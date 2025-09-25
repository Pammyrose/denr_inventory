<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentPlace extends Model
{
    protected $table = 'assignment_places';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name', 'desc'];
}