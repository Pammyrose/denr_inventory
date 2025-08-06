<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(){
        return Inertia::render('employee/Index', [
            'employees' => Employee::all()->toArray(),
        ]);
     
    }

    public function create(){
        return Inertia::render('employee/Create', []);
    }

    public function store(Request $request){
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'emp_status' => 'required|string|max:255',
            'position_name' => 'required|string|max:255',
            'assignment_name' => 'required|string|max:255',
            'div_sec_unit' => 'required|string|max:255',
        ]);

        Employee::create($data);

        return redirect()->route('employee.index')->with('message','Employee added successfully');
    }

    
}