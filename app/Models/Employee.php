<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'position_id',
        'assignment_id',
        'org_unit_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssignmentPlace::class, 'assignment_id');
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'org_unit_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Inventory::class, 'assigned');
    }
}