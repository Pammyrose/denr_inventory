<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgUnit extends Model
{
    protected $table = 'org_units';
    
    protected $primaryKey = 'org_code';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    protected $fillable = [
        'org_code',
        'name',
        'description',
    ];
}