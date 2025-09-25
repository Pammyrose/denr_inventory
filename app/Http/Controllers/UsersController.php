<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        return Inertia::render('users/Index', [
            'users' => User::all()->map(function ($user) {
                return [
                    'id' => (int) $user->id,
                    'full_name' => $user->name ?? 'Unknown User',
                    'email' => $user->email,
                ];
            })->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::warning('UsersController@store: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        User::create([
            'name' => $request->full_name ?: 'Unknown User',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        session()->flash('message', 'User created successfully');
        return Inertia::location(route('users.index'));
    }

    public function show(User $user)
    {
        // Log request start
        Log::info('UsersController@show: Starting request processing', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ]);

        // Log user data
        Log::info('UsersController@show: User data', [
            'user_id' => $user->id ?? 'null',
            'user_name' => $user->name ?? 'null',
            'user_email' => $user->email ?? 'null',
        ]);

        // Fetch employee with relationships
        $employee = null;
        try {
            $employee = Employee::where('email', $user->email)
                ->with(['assets', 'position', 'assignment', 'orgUnit'])
                ->first();
            Log::info('UsersController@show: Employee query result', [
                'email' => $user->email,
                'employee_found' => $employee ? true : false,
                'employee_id' => $employee ? $employee->id : 'null',
            ]);
        } catch (\Exception $e) {
            Log::error('UsersController@show: Employee query failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Build profileUser array
        $profileUser = [
            'id' => $user->id ?? 0,
            'full_name' => $user->name ?? 'Unknown User',
            'email' => $user->email ?? 'N/A',
            'role' => isset($user->is_admin) ? ($user->is_admin ? 'Admin' : 'User') : 'User',
            'assets' => [],
            'employee' => null, // New: Employee details
        ];

        // Add employee details if found
        if ($employee) {
            $profileUser['employee'] = [
                'first_name' => $employee->first_name ?? 'N/A',
                'middle_name' => $employee->middle_name ?? 'N/A',
                'last_name' => $employee->last_name ?? 'N/A',
                'suffix' => $employee->suffix ?? 'N/A',
                'sex' => $employee->sex ?? 'N/A',
                'emp_status' => $employee->emp_status ?? 'N/A',
                'position_name' => $employee->position ? $employee->position->name : 'N/A',
                'assignment_name' => $employee->assignment ? $employee->assignment->name : 'N/A',
                'div_sec_unit' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A',
            ];

            // Map assets
            try {
                $profileUser['assets'] = $employee->assets->map(function ($asset) {
                    return [
                        'id' => $asset->asset_id ?? 'N/A',
                        'name' => $asset->name ?? 'N/A',
                        'category' => $asset->category ?? 'N/A',
                        'location' => $asset->location ?? 'N/A',
                        'purchase_date' => $asset->purchase_date ? $asset->purchase_date : 'N/A',
                        'value' => $asset->value ?? 0,
                        'condition' => $asset->condition ?? 'N/A',
                        'status' => $asset->status ?? 'N/A',
                    ];
                })->toArray();
                Log::info('UsersController@show: Assets mapped', [
                    'asset_count' => count($profileUser['assets']),
                ]);
            } catch (\Exception $e) {
                Log::error('UsersController@show: Asset mapping failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $profileUser['assets'] = [];
            }
        }

        // Log final data
        Log::info('UsersController@show: Final profile data', [
            'profile_user' => $profileUser,
        ]);

        return Inertia::render('users/View', [
            'user' => $profileUser,
        ]);
    }

    public function update(Request $request, User $user)
    {
        Log::info('UsersController@update: Incoming request', [
            'request' => $request->all(),
            'user_id' => $user->id,
            'current_email' => $user->email,
        ]);

        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::warning('UsersController@update: Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            $userData = [
                'name' => $request->full_name ?: 'Unknown User',
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);
            Log::info('UsersController@update: User updated successfully', [
                'user_id' => $user->id,
                'updated_data' => $userData,
            ]);

            session()->flash('message', 'User updated successfully');
            return Inertia::location(route('users.index'));
        } catch (\Exception $e) {
            Log::error('UsersController@update: Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['general' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        try {
            Log::info('UsersController: Deleting user', ['user_id' => $user->id]);
            $user->delete();
            Log::info('UsersController: User deleted', ['user_id' => $user->id]);
            return Inertia::location(route('users.index'));
        } catch (\Exception $e) {
            Log::error('UsersController: Failed to delete user', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }
}