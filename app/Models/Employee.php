<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'emp_status',
        'position_name',
        'assignment_name',
        'div_sec_unit',
        
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_name', 'id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssignmentPlace::class, 'assignment_name', 'id');
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'div_sec_unit', 'id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Inventory::class, 'assigned');
    }

    public function otherInfo(): HasOne
    {
        return $this->hasOne(OtherInfo::class);
    }
}