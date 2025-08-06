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
    ];
}