<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'assets';

    protected $fillable = [
        'asset_id',
        'name',
        'category',
        'location',
        'purchase_date',
        'value',
        'condition',
        'assigned',
        'status',
        'property_no',
        'serial_no',
        'serviceable',
        'unserviceable',
        'coa_representative',
        'coa_date',
        'assigned_date',
        'image',
        'return_date',
        'unit_qty',
    ];

    protected $primaryKey = 'id';

    /**
     * Get the route key for the model (use asset_id for binding).
     */
    public function getRouteKeyName(): string
    {
        return 'asset_id';
    }

    /**
     * Get the employee assigned to this asset.
     */
    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned');
    }
}