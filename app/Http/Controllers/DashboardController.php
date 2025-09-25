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
            ->selectRaw('COUNT(*) as quantity, DATE(purchase_date) as date')
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

        $departments = ['PMD', 'Finance', 'Admin', 'Legal', 'CDD', 'SMD', 'LPDD', 'Enforcement', 'MSD', 'Technical'];
try {
    $assignedPerDepartment = DB::table('assets')
        ->join('employees', 'assets.assigned', '=', 'employees.id')
        ->join('org_units', 'employees.org_unit_id', '=', 'org_units.org_code')
        ->select('org_units.name as department', DB::raw('COUNT(*) as value'))
        ->whereIn('org_units.name', $departments)
        ->groupBy('org_units.name')
        ->get()
        ->mapWithKeys(function ($item) use ($departments) {
            return [$item->department => ['name' => $item->department, 'value' => (int)$item->value]];
        })
        ->toArray();

    // Ensure all departments are included, even with zero assets
    $assignedPerDepartment = array_merge(
        array_fill_keys(
            array_map(fn($dept) => $dept, $departments),
            ['name' => '', 'value' => 0]
        ),
        $assignedPerDepartment
    );
    foreach ($assignedPerDepartment as $key => &$dept) {
        if (is_array($dept) && empty($dept['name'])) {
            $dept['name'] = $key;
        }
    }
    $assignedPerDepartment = array_values($assignedPerDepartment);

    Log::info('Assigned Assets per Department', ['data' => $assignedPerDepartment]);
} catch (\Exception $e) {
    Log::error('Error fetching assigned assets per department: ' . $e->getMessage());
    $assignedPerDepartment = array_map(function ($dept) {
        return ['name' => $dept, 'value' => 0];
    }, $departments);
}

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'employees' => $employeeCount,
                'users' => $userCount,
                'assignedAssets' => Inventory::whereNotNull('assigned')->count(),
                'assets' => $inventoryCount,
                'assignedPerDepartment' => $assignedPerDepartment,
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
            ],
        ]);
    }
}