<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\OrgUnit;
use App\Models\Position;
use App\Models\AssignmentPlace;
use App\Models\ArchivedEmployee;
use App\Models\ArchivedPosition;
use App\Models\OtherInfo; // Add import for OtherInfo
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
   private function getDropdownData()
    {
        $dropdowns = [
            'orgUnits' => OrgUnit::all()->map(fn ($o) => [
                'value' => (string)$o->id,
                'label' => $o->name,
            ]),
            'positions' => Position::all()->map(fn ($p) => [
                'value' => (string)$p->id,
                'label' => $p->name,
                'salary_grade' => $p->salary_grade,
                'item_code' => $p->item_code,
                'org_code' => $p->org_code ?? '',
            ]),
            'assignmentPlaces' => AssignmentPlace::all()->map(fn ($a) => [
                'value' => (string)$a->id,
                'label' => $a->name,
            ]),
            'empStatuses' => [
                ['value' => 'Active', 'label' => 'Active'],
                ['value' => 'Inactive', 'label' => 'Inactive'],
                ['value' => 'On Leave', 'label' => 'On Leave'],
            ],
        ];

        Log::info('EmployeeController: getDropdownData', [
            'orgUnits_count' => count($dropdowns['orgUnits']),
            'positions_count' => count($dropdowns['positions']),
            'assignmentPlaces_count' => count($dropdowns['assignmentPlaces']),
            'empStatuses_count' => count($dropdowns['empStatuses']),
        ]);

        return $dropdowns;
    }

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'sex' => 'required|string|in:M,F',
            'email' => 'required|email|unique:employees,email|max:255|unique:users,email',
            'emp_status' => 'required|string|in:Active,Inactive,On Leave',
            'position_name' => 'required|exists:positions,id',
            'assignment_name' => 'required|exists:assignment_places,id',
            'div_sec_unit' => 'required|exists:org_units,id',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required|date',
            'tin_no' => 'nullable|string|max:255',
            'date_appointment' => 'nullable|date',
            'date_last_promotion' => 'nullable|date',
            'civil_service' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:1000',
            
        ]);

        DB::beginTransaction();

        // Create User
        $fullName = trim("{$validated['first_name']} {$validated['middle_name']} {$validated['last_name']} {$validated['suffix']}");
        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        // Prepare employee data
        $employeeData = [
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'suffix' => $validated['suffix'],
            'sex' => $validated['sex'],
            'email' => $validated['email'],
            'emp_status' => $validated['emp_status'],
            'position_name' => $validated['position_name'],
            'assignment_name' => $validated['assignment_name'],
            'div_sec_unit' => $validated['div_sec_unit'],
        ];

        // Prepare other info data
        $otherInfoData = [
            'date_of_birth' => $validated['date_of_birth'],
            'tin_no' => $validated['tin_no'],
            'date_appointment' => $validated['date_appointment'],
            'date_last_promotion' => $validated['date_last_promotion'],
            'civil_service' => $validated['civil_service'],
            'education' => $validated['education'],
        ];

        // Create Employee and OtherInfo
        $employee = Employee::create($employeeData);
        $employee->otherInfo()->create($otherInfoData);

        DB::commit();

        Log::info('EmployeeController: Employee and OtherInfo created', [
            'email' => $validated['email'],
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('employee.index')->with('message', 'Employee created successfully');
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        Log::error('EmployeeController: Validation failed', [
            'error' => $e->getMessage(),
            'errors' => $e->errors(),
        ]);
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('EmployeeController: Failed to create employee', [
            'error' => $e->getMessage(),
        ]);
        return back()->withErrors(['general' => 'Failed to create employee: ' . $e->getMessage()])->withInput();
    }
}

    public function index()
    {
        try {
            $employees = Employee::with(['position', 'assignment', 'orgUnit', 'otherInfo'])->get()->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name ?? 'N/A',
                    'middle_name' => $employee->middle_name ?? '',
                    'last_name' => $employee->last_name ?? 'N/A',
                    'suffix' => $employee->suffix ?? '',
                    'sex' => $employee->sex ?? 'N/A',
                    'email' => $employee->email ?? 'N/A',
                    'emp_status' => $employee->emp_status ?? 'N/A',
                    'position_name' => $employee->position_name ? (string)$employee->position_name : null,
                    'position_label' => $employee->position ? $employee->position->name : 'N/A',
                    'assignment_name' => $employee->assignment_name ? (string)$employee->assignment_name : null,
                    'assignment_label' => $employee->assignment ? $employee->assignment->name : 'N/A',
                    'div_sec_unit' => $employee->div_sec_unit ? (string)$employee->div_sec_unit : null,
                    'div_sec_unit_label' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A',
                    'date_of_birth' => $employee->otherInfo ? $employee->otherInfo->date_of_birth : null,
                    'tin_no' => $employee->otherInfo ? $employee->otherInfo->tin_no : null,
                    'date_appointment' => $employee->otherInfo ? $employee->otherInfo->date_appointment : null,
                    'date_last_promotion' => $employee->otherInfo ? $employee->otherInfo->date_last_promotion : null,
                    'civil_service' => $employee->otherInfo ? $employee->otherInfo->civil_service : null,
                    'education' => $employee->otherInfo ? $employee->otherInfo->education : null,
                ];
            })->toArray();

            Log::info('EmployeeController: Fetched employees', [
                'employee_count' => count($employees),
            ]);

            return Inertia::render('employee/Index', [
                'employees' => $employees,
                'dropdowns' => $this->getDropdownData(),
                'error' => null,
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to fetch employees', [
                'error' => $e->getMessage(),
            ]);
            return Inertia::render('employee/Index', [
                'employees' => [],
                'dropdowns' => $this->getDropdownData(),
                'error' => 'Failed to load employees. Please try again later.',
            ]);
        }
    }


    public function view($id)
{
    try {
        $employee = Employee::with(['otherInfo', 'assets', 'position', 'assignment', 'orgUnit'])->findOrFail($id);
        $dropdowns = $this->getDropdownData();

        return Inertia::render('employee/View', [
            'employee' => [
                'id' => $employee->id,
                'first_name' => $employee->first_name ?? 'N/A',
                'middle_name' => $employee->middle_name ?? '',
                'last_name' => $employee->last_name ?? 'N/A',
                'suffix' => $employee->suffix ?? '',
                'sex' => $employee->sex ?? 'N/A',
                'email' => $employee->email ?? 'N/A',
                'emp_status' => $employee->emp_status ?? 'N/A',
                'position_name' => $employee->position ? $employee->position->name : 'N/A', // Use name from positions table
                'assignment_name' => $employee->assignment ? $employee->assignment->name : 'N/A', // Use name from assignment_places table
                'div_sec_unit' => $employee->orgUnit ? $employee->orgUnit->name : 'N/A', // Use name from org_units table
                'date_of_birth' => $employee->otherInfo ? $employee->otherInfo->date_of_birth : null,
                'tin_no' => $employee->otherInfo ? $employee->otherInfo->tin_no : null,
                'date_appointment' => $employee->otherInfo ? $employee->otherInfo->date_appointment : null,
                'date_last_promotion' => $employee->otherInfo ? $employee->otherInfo->date_last_promotion : null,
                'civil_service' => $employee->otherInfo ? $employee->otherInfo->civil_service : null,
                'education' => $employee->otherInfo ? $employee->otherInfo->education : null,
                'assets' => $employee->assets ? $employee->assets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->name ?? 'N/A',
                        'category' => $asset->category ?? 'N/A',
                        'location' => $asset->location ?? 'N/A',
                        'purchase_date' => $asset->purchase_date ?? 'N/A',
                    ];
                })->toArray() : [],
            ],
            'dropdowns' => $dropdowns,
            'error' => null,
        ]);
    } catch (\Exception $e) {
        Log::error('EmployeeController: Failed to fetch employee', [
            'id' => $id,
            'error' => $e->getMessage(),
        ]);
        return Inertia::render('employee/View', [
            'employee' => null,
            'dropdowns' => $this->getDropdownData(),
            'error' => 'Failed to load employee. Please try again later.',
        ]);
    }
}

    public function edit(Employee $employee)
    {
        $employees = Employee::all()->map(function ($e) {
            $fullName = trim("{$e->first_name} {$e->middle_name} {$e->last_name}");
            return [
                'id' => $e->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('EmployeeController@edit: Employees for Edit Form', [
            'employees' => $employees,
            'employee_id' => $employee->id,
        ]);

        return Inertia::render('employee/Index', [
            'employee' => [
                'id' => $employee->id,
                'first_name' => $employee->first_name ?? 'N/A',
                'middle_name' => $employee->middle_name ?? '',
                'last_name' => $employee->last_name ?? 'N/A',
                'suffix' => $employee->suffix ?? '',
                'sex' => $employee->sex ?? 'N/A',
                'email' => $employee->email ?? 'N/A',
                'emp_status' => $employee->emp_status ?? 'N/A',
                'position_id' => $employee->position_id ? (string)$employee->position_id : null,
                'assignment_id' => $employee->assignment_id ? (string)$employee->assignment_id : null,
                'org_unit_id' => $employee->org_unit_id ? (string)$employee->org_unit_id : null,
                'date_of_birth' => $employee->otherInfo ? $employee->otherInfo->date_of_birth : null,
                'tin_no' => $employee->otherInfo ? $employee->otherInfo->tin_no : null,
                'date_appointment' => $employee->otherInfo ? $employee->otherInfo->date_appointment : null,
                'date_last_promotion' => $employee->otherInfo ? $employee->otherInfo->date_last_promotion : null,
                'civil_service' => $employee->otherInfo ? $employee->otherInfo->civil_service : null,
                'education' => $employee->otherInfo ? $employee->otherInfo->education : null,
            ],
            'employees' => $employees,
            'dropdowns' => $this->getDropdownData(),
        ]);
    }

    public function update(Request $request, Employee $employee)
{
    Log::info('EmployeeController@update: Incoming request data: ' . json_encode($request->all()));

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'last_name' => 'required|string|max:255',
        'suffix' => 'nullable|string|max:50',
        'sex' => 'required|string|in:M,F',
        'email' => 'nullable|email|max:255',
        'emp_status' => 'required|string|in:Active,Inactive,On Leave',
        'position_id' => 'required|exists:positions,id',
        'assignment_id' => 'required|exists:assignment_places,id',
        'org_unit_id' => 'required|exists:org_units,id',
        'password' => 'nullable|string|min:8|confirmed',
        'date_of_birth' => 'required|date',
        'tin_no' => 'nullable|string|max:255',
        'date_appointment' => 'nullable|date',
        'date_last_promotion' => 'nullable|date',
        'civil_service' => 'nullable|string|max:255',
        'education' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
        Log::warning('Validation failed in EmployeeController@update: ' . json_encode($validator->errors()));
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        // Compute new full name
        $fullName = trim("{$request->first_name} {$request->middle_name} {$request->last_name} {$request->suffix}");

        // Update employee
        $employeeData = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'sex' => $request->sex,
            'email' => $request->has('email') ? $request->email : $employee->email,
            'emp_status' => $request->emp_status,
            'position_name' => $request->position_id, // Map position_id to position_name
            'assignment_name' => $request->assignment_id, // Map assignment_id to assignment_name
            'div_sec_unit' => $request->org_unit_id, // Map org_unit_id to div_sec_unit
        ];
        $employee->update($employeeData);

        // Update or create OtherInfo
        $otherInfoData = [
            'date_of_birth' => $request->date_of_birth,
            'tin_no' => $request->tin_no,
            'date_appointment' => $request->date_appointment,
            'date_last_promotion' => $request->date_last_promotion,
            'civil_service' => $request->civil_service,
            'education' => $request->education,
        ];
        $employee->otherInfo()->updateOrCreate(['employee_id' => $employee->id], $otherInfoData);

        // Update user
        if ($employee->user) {
            $userData = ['name' => $fullName];
            if ($request->password) {
                $userData['password'] = Hash::make($request->password);
            }
            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }
            $employee->user->update($userData);
        }

        DB::commit();

        Log::info('EmployeeController@update: Employee and OtherInfo updated', [
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('employee.index')->with('message', 'Employee updated successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error in EmployeeController@update: ' . $e->getMessage());
        return back()->withErrors(['general' => 'Failed to update employee: ' . $e->getMessage()])->withInput();
    }
}

    public function destroy(Employee $employee)
    {
        try {
            Log::info('EmployeeController: Deleting employee', ['employee_id' => $employee->id]);
            $employee->delete();
            Log::info('EmployeeController: Employee deleted', ['employee_id' => $employee->id]);
            return redirect()->route('employee.index')->with('message', 'Employee deleted successfully');
        } catch (\Exception $e) {
            Log::error('EmployeeController: Failed to delete employee', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    public function archive($id)
{
    try {
        $employee = Employee::findOrFail($id);
        ArchivedEmployee::create([
            'original_employee_id' => $employee->id,
            'first_name' => $employee->first_name,
            'middle_name' => $employee->middle_name,
            'last_name' => $employee->last_name,
            'suffix' => $employee->suffix,
            'sex' => $employee->sex,
            'email' => $employee->email,
            'emp_status' => $employee->emp_status,
            'position_id' => $employee->position_name, // Map position_name to position_id
            'assignment_id' => $employee->assignment_name, // Map assignment_name to assignment_id
            'org_unit_id' => $employee->div_sec_unit, // Map div_sec_unit to org_unit_id
            'archived_at' => now(),
        ]);

        $employee->delete();
        return redirect()->back()->with('message', 'Employee archived successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['general' => 'Failed to archive employee: ' . $e->getMessage()]);
    }
}

    public function unarchive(Request $request, $archivedEmployeeId)
{
    try {
        DB::beginTransaction();

        $archivedEmployee = ArchivedEmployee::findOrFail($archivedEmployeeId);
        $positionId = $archivedEmployee->position_id;

        // Check if the position is in archived_positions
        $archivedPosition = ArchivedPosition::find($archivedEmployee->position_id);
        if ($archivedPosition) {
            $position = Position::create([
                'id' => $archivedPosition->original_position_id ?? $archivedPosition->id,
                'item_code' => $archivedPosition->item_code,
                'name' => $archivedPosition->name,
                'desc' => $archivedPosition->desc,
                'salary_grade' => $archivedPosition->salary_grade,
                'org_code' => $archivedPosition->org_code,
            ]);
            $positionId = $position->id;
            Log::info('EmployeeController: Position transferred back to positions', [
                'archived_position_id' => $archivedPosition->id,
                'position_id' => $position->id,
            ]);
            $archivedPosition->delete();
        }

        // Validate that the email is not already in use
        if (Employee::where('email', $archivedEmployee->email)->exists()) {
            throw new \Exception('Email already exists in the employees table.');
        }

        // Ensure User record exists
        $user = User::where('email', $archivedEmployee->email)->first();
        if (!$user) {
            $fullName = trim("{$archivedEmployee->first_name} {$archivedEmployee->middle_name} {$archivedEmployee->last_name} {$archivedEmployee->suffix}");
            $user = User::create([
                'name' => $fullName,
                'email' => $archivedEmployee->email,
                'password' => Hash::make('default_password'),
                'is_admin' => false,
            ]);
            Log::info('EmployeeController: Created new User record during unarchive', [
                'email' => $archivedEmployee->email,
                'user_id' => $user->id,
            ]);
        }

        // Restore employee
        $employee = Employee::create([
            'id' => $archivedEmployee->original_employee_id,
            'first_name' => $archivedEmployee->first_name,
            'middle_name' => $archivedEmployee->middle_name,
            'last_name' => $archivedEmployee->last_name,
            'suffix' => $archivedEmployee->suffix,
            'sex' => $archivedEmployee->sex,
            'email' => $archivedEmployee->email,
            'emp_status' => $archivedEmployee->emp_status,
            'position_name' => $positionId,
            'assignment_name' => $archivedEmployee->assignment_id,
            'div_sec_unit' => $archivedEmployee->org_unit_id,
        ]);

        $archivedEmployee->delete();

        DB::commit();

        Log::info('EmployeeController: Employee unarchived', [
            'archived_employee_id' => $archivedEmployeeId,
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('archived')->with('message', "Employee ID {$employee->id} unarchived successfully.");
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('EmployeeController: Failed to unarchive employee', [
            'error' => $e->getMessage(),
        ]);
        return back()->withErrors(['general' => 'Failed to unarchive employee: ' . $e->getMessage()]);
    }
}

    

    public function storeOrgUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'org_code' => 'required|string|max:255|unique:org_units,org_code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: OrgUnit validation failed', [
                'errors' => $validator->errors(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            OrgUnit::create([
                'org_code' => $request->org_code,
                'name' => $request->name,
                'description' => $request->description,
            ]);

            DB::commit();

            Log::info('EmployeeController: OrgUnit created', [
                'org_code' => $request->org_code,
                'name' => $request->name,
            ]);

            return redirect()->route('employee.index')->with('message', 'Organizational unit created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create org unit', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to create organizational unit: ' . $e->getMessage()])->withInput();
        }
    }

    public function position(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|max:255|unique:positions,item_code',
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string|max:1000',
            'salary_grade' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: Position validation failed', [
                'errors' => $validator->errors(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $position = Position::create([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'desc' => $request->desc,
                'salary_grade' => $request->salary_grade,
            ]);

            DB::commit();

            Log::info('EmployeeController: Position created', [
                'item_code' => $request->item_code,
                'name' => $request->name,
            ]);

            return redirect()->route('employee.index')->with([
                'message' => 'Position created successfully',
                'position' => [
                    'value' => (string)$position->id,
                    'label' => $position->name,
                    'item_code' => $position->item_code,
                    'salary_grade' => $position->salary_grade,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create position', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to create position: ' . $e->getMessage()])->withInput();
        }
    }

    public function storeSalaryGrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_code' => 'required|string|exists:positions,item_code',
            'salary_grade' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: SalaryGrade validation failed', [
                'errors' => $validator->errors(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $position = Position::where('item_code', $request->item_code)->firstOrFail();
            $position->update([
                'salary_grade' => $request->salary_grade,
            ]);

            DB::commit();

            Log::info('EmployeeController: SalaryGrade updated', [
                'item_code' => $request->item_code,
                'salary_grade' => $request->salary_grade,
            ]);

            return redirect()->route('employee.index')->with([
                'message' => 'Salary grade updated successfully',
                'position' => [
                    'value' => (string)$position->id,
                    'label' => $position->name,
                    'salary_grade' => $position->salary_grade,
                    'item_code' => $position->item_code,
                    'org_code' => $position->org_code ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to update salary grade', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to update salary grade: ' . $e->getMessage()])->withInput();
        }
    }

    public function storeAssignmentPlace(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::warning('EmployeeController: AssignmentPlace validation failed', [
                'errors' => $validator->errors(),
            ]);
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $assignmentPlace = AssignmentPlace::create([
                'name' => $request->name,
                'desc' => $request->desc,
            ]);

            DB::commit();

            Log::info('EmployeeController: AssignmentPlace created', [
                'name' => $request->name,
            ]);

            return redirect()->route('employee.index')->with([
                'message' => 'Assignment place created successfully',
                'assignmentPlace' => [
                    'value' => (string)$assignmentPlace->id,
                    'label' => $assignmentPlace->name,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('EmployeeController: Failed to create assignment place', [
                'error' => $e->getMessage(),
            ]);
            return back()->withErrors(['general' => 'Failed to create assignment place: ' . $e->getMessage()])->withInput();
        }
    }
}