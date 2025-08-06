<?php

namespace App\Http\Controllers;


use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $assets = Inventory::all()->map(function ($item) {
            return [
                'id' => $item->asset_id,
                'name' => $item->name,
                'category' => $item->category,
                'location' => $item->location,
                'purchase_date' => $item->purchase_date,
                'value' => $item->value,
                'condition' => $item->condition,
                'assigned_to' => $item->assigned,
                'status' => $item->status,
            ];
        })->toArray();
    
        return Inertia::render('inventory/Index', [
            'assets' => $assets,
        ]);
    }

    public function create(){
        return Inertia::render('inventory/Create', []);
    }

    public function store(Request $request){
        $data = $request->validate([
            'asset_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'purchase_date' => 'required|date|max:255',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|max:255',
            'assigned' => 'nullable|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        Inventory::create($data);

        return redirect()->route('inventory.index')->with('message','Asset added successfully');
    }

    }
