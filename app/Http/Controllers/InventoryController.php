<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index()
    {
        $assets = Inventory::all()->map(function ($item) {
            $assignedTo = $item->assigned ? Employee::find($item->assigned) : null;
            $fullName = $assignedTo 
                ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
                : 'Unassigned';
            
            Log::info('Asset Processing', [
                'asset_id' => $item->asset_id,
                'assigned' => $item->assigned,
                'full_name' => $fullName
            ]);

            return [
                'id' => $item->asset_id,
                'name' => $item->name,
                'category' => $item->category,
                'location' => $item->location,
                'purchase_date' => $item->purchase_date,
                'value' => $item->value,
                'condition' => $item->condition,
                'assigned_to' => $fullName,
                'employee_id' => $item->assigned,
                'status' => $item->status,
                'image' => $item->image ? Storage::url($item->image) : null, // Added image URL
               
            ];
        })->toArray();

        $employees = Employee::all()->map(function ($employee) {
            $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
            return [
                'id' => $employee->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('Employees Fetched', ['employees' => $employees]);

        return Inertia::render('inventory/Index', [
            'assets' => $assets,
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        $employees = Employee::all()->map(function ($employee) {
            $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
            return [
                'id' => $employee->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('Employees for Create Form', ['employees' => $employees]);

        return Inertia::render('inventory/Create', [
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'purchase_date' => 'required|date|max:255',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|max:255',
            'assigned' => 'nullable|integer|exists:employees,id',
            'status' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Added image validation
           
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('assets', 'public');
            $data['image'] = $path;
        }

        // Generate asset_id
        $lastAsset = Inventory::orderBy('asset_id', 'desc')->first();
        $lastId = $lastAsset ? (int) str_replace('A', '', $lastAsset->asset_id) : 0;
        $newId = $lastId + 1;
        $data['asset_id'] = 'A' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        Log::info('Storing Asset', ['data' => $data]);

        $asset = Inventory::create($data);

        // Generate QR code linking to inventory.tag route
        $tagUrl = url()->route('inventory.tag', ['inventory' => $asset->asset_id], true);
        Log::info('QR Code URL', ['url' => $tagUrl]);
        $qrCodeBase64 = null;
        try {
            $qrCode = new QrCode($tagUrl);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());
            Log::info('QR Code Generated', ['asset_id' => $asset->asset_id, 'tag_url' => $tagUrl]);
        } catch (\Exception $e) {
            Log::error('QR Code Generation Failed in Store', [
                'asset_id' => $asset->asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Inertia::render('inventory/Create', [
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName ?: 'Unnamed Employee',
                    ];
                })->toArray(),
                'error' => 'Failed to generate QR code: ' . $e->getMessage(),
                'asset' => [
                    'id' => $asset->asset_id,
                    'name' => $asset->name,
                    'category' => $asset->category,
                    'location' => $asset->location,
                    'purchase_date' => $asset->purchase_date,
                    'value' => $asset->value,
                    'condition' => $asset->condition,
                    'assigned_to' => $asset->assigned ? (int)$asset->assigned : null,
                    'status' => $asset->status,
                    'image' => $asset->image ? Storage::url($asset->image) : null,
                   
                ],
            ]);
        }

        return Inertia::render('inventory/Create', [
            'employees' => Employee::all()->map(function ($employee) {
                $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
                return [
                    'id' => $employee->id,
                    'full_name' => $fullName ?: 'Unnamed Employee',
                ];
            })->toArray(),
            'qrCode' => $qrCodeBase64,
            'asset' => [
                'id' => $asset->asset_id,
                'name' => $asset->name,
                'category' => $asset->category,
                'location' => $asset->location,
                'purchase_date' => $asset->purchase_date,
                'value' => $asset->value,
                'condition' => $asset->condition,
                'assigned_to' => $asset->assigned ? (int)$asset->assigned : null,
                'status' => $asset->status,
                'image' => $asset->image ? Storage::url($asset->image) : null,
            ],
            'message' => 'Asset created successfully',
        ]);
    }


    public function edit(Inventory $inventory)
    {
        $employees = Employee::all()->map(function ($employee) {
            $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
            return [
                'id' => $employee->id,
                'full_name' => $fullName ?: 'Unnamed Employee',
            ];
        })->toArray();

        Log::info('Employees for Edit Form', [
            'employees' => $employees,
            'asset_id' => $inventory->asset_id,
            'assigned' => $inventory->assigned
        ]);

        return Inertia::render('inventory/Edit', [
            'asset' => [
                'id' => $inventory->asset_id,
                'name' => $inventory->name,
                'category' => $inventory->category,
                'location' => $inventory->location,
                'purchase_date' => $inventory->purchase_date,
                'value' => $inventory->value,
                'condition' => $inventory->condition,
                'assigned_to' => $inventory->assigned ? (int)$inventory->assigned : null,
                'status' => $inventory->status,
            ],
            'employees' => $employees,
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        Log::info('InventoryController@update: Incoming request data: ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'purchase_date' => 'required|date|max:255',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|max:255',
            'assigned_to' => 'nullable|integer|exists:employees,id',
            'status' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed in InventoryController@update: ' . json_encode($validator->errors()));
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $inventoryData = [
                'name' => $request->name,
                'category' => $request->category,
                'location' => $request->location,
                'purchase_date' => $request->purchase_date,
                'value' => $request->value,
                'condition' => $request->condition,
                'assigned' => $request->assigned_to,
                'status' => $request->status,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($inventory->image) {
                    Storage::disk('public')->delete($inventory->image);
                }
                $inventoryData['image'] = $request->file('image')->store('assets', 'public');
            }

            $inventory->update($inventoryData);
            Log::info('InventoryController@update: Asset updated: ' . json_encode($inventory->toArray()));

            DB::commit();

            return redirect()->route('inventory.index')->with('message', 'Asset updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in InventoryController@update: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString() . ' | Data: ' . json_encode($request->all()));
            return back()->withErrors(['general' => 'Failed to update asset: ' . $e->getMessage()])->withInput();
        }
    }

   public function destroy(Inventory $inventory)
    {
        try {
            DB::beginTransaction();

            // Delete associated image
            if ($inventory->image) {
                Storage::disk('public')->delete($inventory->image);
            }

            $inventory->delete();
            Log::info('InventoryController@destroy: Asset deleted: ' . json_encode($inventory->toArray()));

            DB::commit();
            return redirect()->route('inventory.index')->with('message', 'Asset deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in InventoryController@destroy: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return back()->withErrors(['general' => 'Failed to delete asset: ' . $e->getMessage()]);
        }
    }

    public function view(Request $request, $inventory)
{
    Log::info('View Method Called', ['inventory_param' => $inventory]);

    $inventoryModel = Inventory::where('asset_id', $inventory)->first();
    Log::info('Inventory Query', ['asset_id' => $inventory, 'result' => $inventoryModel ? $inventoryModel->toArray() : null]);

    if (!$inventoryModel) {
        Log::error('Inventory not found', ['asset_id' => $inventory]);
        return Inertia::render('inventory/View', [
            'asset' => null,
            'error' => 'Asset not found for ID: ' . $inventory
        ]);
    }

    $assignedTo = $inventoryModel->assigned ? Employee::find($inventoryModel->assigned) : null;
    $fullName = $assignedTo 
        ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
        : 'Unassigned';

    $viewUrl = $request->getSchemeAndHttpHost() . route(
        'inventory.tag',
        ['inventory' => $inventoryModel->asset_id],
        false
    );

    Log::info('QR Code URL', ['url' => $viewUrl]);

    try {
        $qrCode = new QrCode($viewUrl);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());
        Log::info('Viewing Asset', [
            'asset_id' => $inventoryModel->asset_id,
            'assigned' => $inventoryModel->assigned,
            'full_name' => $fullName,
            'image' => $inventoryModel->image
        ]);
    } catch (\Exception $e) {
        Log::error('QR Code Generation Failed in View', [
            'asset_id' => $inventoryModel->asset_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $qrCodeBase64 = null;
    }

    $assetData = [
        'id' => (string) $inventoryModel->asset_id,
        'name' => (string) $inventoryModel->name,
        'category' => (string) $inventoryModel->category,
        'location' => (string) $inventoryModel->location,
        'purchase_date' => (string) $inventoryModel->purchase_date,
        'value' => (float) $inventoryModel->value,
        'condition' => (string) $inventoryModel->condition,
        'assigned_to' => (string) $fullName,
        'employee_id' => $inventoryModel->assigned ? (int)$inventoryModel->assigned : null,
        'status' => (string) $inventoryModel->status,
        'qr_code' => $qrCodeBase64,
        'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
    ];

    Log::info('View Asset Data', ['asset' => $assetData]);

    return Inertia::render('inventory/View', [
        'asset' => $assetData,
        'error' => null
    ]);
}

    public function tag(Request $request, $inventory)
    {
        Log::info('Tag Method Called', ['inventory_param' => $inventory]);
    
        $inventoryModel = Inventory::where('asset_id', $inventory)->first();
        Log::info('Inventory Query', ['asset_id' => $inventory, 'result' => $inventoryModel ? $inventoryModel->toArray() : null]);
    
        if (!$inventoryModel) {
            Log::error('Inventory not found', ['asset_id' => $inventory]);
            return Inertia::render('inventory/AssetTag', [
                'asset' => null,
                'error' => 'Asset not found for ID: ' . $inventory
            ]);
        }
    
        $assignedTo = $inventoryModel->assigned ? Employee::find($inventoryModel->assigned) : null;
        $fullName = $assignedTo 
            ? trim("{$assignedTo->first_name} {$assignedTo->middle_name} {$assignedTo->last_name}")
            : 'Unassigned';
    
        $tagUrl = $request->getSchemeAndHttpHost() . route(
            'inventory.tag',
            ['inventory' => $inventoryModel->asset_id],
            false
        );
    
        Log::info('QR Code URL', ['url' => $tagUrl]);
    
        try {
            $qrCode = new QrCode($tagUrl);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = 'data:image/png;base64,' . base64_encode($result->getString());
            Log::info('Viewing Asset Tag', [
                'asset_id' => $inventoryModel->asset_id,
                'assigned' => $inventoryModel->assigned,
                'full_name' => $fullName
            ]);
        } catch (\Exception $e) {
            Log::error('QR Code Generation Failed in Tag', [
                'asset_id' => $inventoryModel->asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $qrCodeBase64 = null;
        }
    
        $assetData = [
            'id' => (string) $inventoryModel->asset_id,
            'name' => (string) $inventoryModel->name,
            'category' => (string) $inventoryModel->category,
            'location' => (string) $inventoryModel->location,
            'purchase_date' => (string) $inventoryModel->purchase_date,
            'value' => (float) $inventoryModel->value,
            'condition' => (string) $inventoryModel->condition,
            'assigned_to' => (string) $fullName,
            'employee_id' => $inventoryModel->assigned ? (int)$inventoryModel->assigned : null,
            'status' => (string) $inventoryModel->status,
            'qr_code' => $qrCodeBase64,
        ];
    
        Log::info('Tag Asset Data', ['asset' => $assetData]);
    
        return Inertia::render('inventory/AssetTag', [
            'asset' => $assetData,
            'error' => null
        ]);
    }
}