<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['first_name','middle_name','last_name','suffix','sex','email','emp_status','position_name','assignment_name','email','div_sec_unit'];
}
