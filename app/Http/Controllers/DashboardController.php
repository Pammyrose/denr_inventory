<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Inventory;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $employeeCount = Employee::count();
        $userCount = User::count();
        $inventoryCount = Inventory::count();

        // Fetch assetsByDay for the current month
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $monthStart = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $assetsByDay = DB::table('assets')
            ->selectRaw('SUM(unit_qty) as quantity, DATE(purchase_date) as date')
            ->whereBetween('purchase_date', [$monthStart, $monthEnd])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        $allDays = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($currentYear, $currentMonth, $day)->format('Y-m-d');
            $found = $assetsByDay->get($date);
            $allDays[] = [
                'date' => $date,
                'quantity' => $found ? (int)$found->quantity : 0,
            ];
        }

        // Fetch assets by location
        try {
            $assetsByLocation = DB::table('assets')
                ->select('location', DB::raw('SUM(unit_qty) as value'))
                ->groupBy('location')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->location => ['name' => $item->location, 'value' => (int)$item->value]];
                })
                ->toArray();

            // Ensure all locations have valid data
            $assetsByLocation = array_values($assetsByLocation);

            Log::info('Assets by Location', ['data' => $assetsByLocation]);
        } catch (\Exception $e) {
            Log::error('Error fetching assets by location: ' . $e->getMessage());
            $assetsByLocation = [];
        }

// Fetch assigned assets older than 5 years and status 'check' with full name
        $fiveYearsAgo = Carbon::now()->subYears(5)->endOfYear()->toDateString();
        $assignedAssets = DB::table('assets')
            ->join('employees', 'assets.assigned', '=', 'employees.id') // Assuming assigned is an employee ID
            ->select(
                DB::raw('CONCAT(employees.first_name, " ", COALESCE(employees.middle_name, ""), " ", employees.last_name) as name'),
                'assets.assigned_date',
                'assets.status'
            )
            ->whereNotNull('assets.assigned')
            ->whereNotNull('assets.assigned_date')
            ->where('assets.assigned_date', '<=', $fiveYearsAgo)
            ->whereRaw('LOWER(assets.status) = ?', ['check'])
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'assigned_date' => Carbon::parse($item->assigned_date)->format('m-d-Y'),
                    'status' => $item->status,
                ];
            })
            ->toArray();

        // Log the assigned assets for debugging
        Log::info('Assigned Assets', ['data' => $assignedAssets]);

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'employees' => $employeeCount,
                'users' => $userCount,
                'assignedAssets' => Inventory::whereNotNull('assigned')->count(),
                'assets' => $inventoryCount,
                'assetsByLocation' => $assetsByLocation,
                'purchasedAssets' => [
                    ['month' => 'Jan', 'count' => 10],
                    ['month' => 'Feb', 'count' => 15],
                    ['month' => 'Mar', 'count' => 8],
                    ['month' => 'Apr', 'count' => 20],
                    ['month' => 'May', 'count' => 12],
                    ['month' => 'Jun', 'count' => 18],
                ],
                'assetsByDay' => $allDays,
                'daysInMonth' => $daysInMonth,
                'selectedYear' => (string)$currentYear,
                'selectedMonth' => sprintf('%02d', $currentMonth),
                'assignedAssetsTable' => $assignedAssets,
            ],
        ]);
    }
}