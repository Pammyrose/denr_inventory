<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
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
            
            $imageUrl = $item->image ? Storage::url($item->image) : null;
            Log::info('Asset Processing', [
                'asset_id' => $item->asset_id,
                'assigned' => $item->assigned,
                'full_name' => $fullName,
                'image' => $imageUrl,
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
                'property_no' => $item->property_no,
                'serial_no' => $item->serial_no,
                'serviceable' => $item->serviceable,
                'unserviceable' => $item->unserviceable,
                'coa_representative' => $item->coa_representative,
                'coa_date' => $item->coa_date,
                'assigned_date' => $item->assigned_date,
                'unit_qty' => $item->unit_qty,
                'image' => $imageUrl,
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
            'purchase_date' => 'required|date',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|in:New,Good,Fair,Poor',
            'assigned' => 'nullable|integer|exists:employees,id',
            'status' => 'required|string|in:Good,Check,Repair,Upgrade',
            'property_no' => 'required|string|max:255',
            'serial_no' => 'required|string|max:255',
            'serviceable' => 'required|string|max:255',
            'unserviceable' => 'required|string|max:255',
            'coa_representative' => 'required|string|max:255',
            'coa_date' => 'required|date',
            'assigned_date' => 'required|date',
            'unit_qty' => 'required|numeric|min:0|max:9999999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('assets', 'public');
            if (!Storage::disk('public')->exists($path)) {
                Log::error('Image storage failed', ['path' => $path]);
                return Inertia::render('inventory/Create', [
                    'employees' => Employee::all()->map(function ($employee) {
                        $fullName = trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}");
                        return [
                            'id' => $employee->id,
                            'full_name' => $fullName ?: 'Unnamed Employee',
                        ];
                    })->toArray(),
                    'error' => 'Failed to store image',
                ]);
            }
            $data['image'] = $path;
            Log::info('Image stored successfully', ['path' => $path, 'url' => Storage::url($path)]);
        }

        $lastAsset = Inventory::orderBy('asset_id', 'desc')->first();
        $lastId = $lastAsset ? (int) str_replace('A', '', $lastAsset->asset_id) : 0;
        $newId = $lastId + 1;
        $data['asset_id'] = 'A' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        Log::info('Storing Asset', ['data' => $data]);

        $asset = Inventory::create($data);

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
                    'property_no' => $asset->property_no,
                    'serial_no' => $asset->serial_no,
                    'serviceable' => $asset->serviceable,
                    'unserviceable' => $asset->unserviceable,
                    'coa_representative' => $asset->coa_representative,
                    'coa_date' => $asset->coa_date,
                    'assigned_date' => $asset->assigned_date,
                    'unit_qty' => $asset->unit_qty,
                    'image' => $asset->image ? Storage::disk('public')->url($asset->image) : null,
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
                'property_no' => $asset->property_no,
                'serial_no' => $asset->serial_no,
                'serviceable' => $asset->serviceable,
                'unserviceable' => $asset->unserviceable,
                'coa_representative' => $asset->coa_representative,
                'coa_date' => $asset->coa_date,
                'assigned_date' => $asset->assigned_date,
                'unit_qty' => $asset->unit_qty,
                'image' => $asset->image ? Storage::disk('public')->url($asset->image) : null,
            ],
            'message' => 'Asset created successfully',
        ]);
    }

   public function edit($asset_id)
    {
        try {
            $inventory = Inventory::where('asset_id', $asset_id)->firstOrFail();
            $employees = Employee::all()->map(function ($employee) {
                $fullName = trim(
                    implode(' ', array_filter([
                        $employee->first_name,
                        $employee->middle_name,
                        $employee->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee';
                return [
                    'id' => $employee->id,
                    'full_name' => $fullName,
                ];
            })->toArray();

            $assignedTo = $inventory->assigned ? Employee::find($inventory->assigned) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';

            $item = [
                'id' => $inventory->asset_id,
                'name' => $inventory->name ?? 'Unknown Item',
                'category' => $inventory->category ?? 'Uncategorized',
                'location' => $inventory->location ?? 'Unknown Location',
                'purchase_date' => $inventory->purchase_date ?? '',
                'value' => (float) ($inventory->value ?? 0),
                'condition' => $inventory->condition ?? 'New',
                'property_no' => $inventory->property_no ?? '',
                'serial_no' => $inventory->serial_no ?? '',
                'serviceable' => (float) ($inventory->serviceable ?? 0),
                'unserviceable' => (float) ($inventory->unserviceable ?? 0),
                'coa_representative' => $inventory->coa_representative ?? '',
                'coa_date' => $inventory->coa_date ?? '',
                'assigned' => $inventory->assigned !== null ? (int)$inventory->assigned : null,
                'assigned_to' => $inventory->assigned !== null ? (int)$inventory->assigned : null, // Alias for frontend
                'full_name' => $fullName,
                'assigned_date' => $inventory->assigned_date ?? '',
                'unit_qty' => (int) ($inventory->unit_qty ?? 1),
            ];

            Log::info('InventoryController@edit: Inventory item and employees for edit form', [
                'asset_id' => $asset_id,
                'item' => $item,
                'assigned' => $item['assigned'],
                'assigned_to' => $item['assigned_to'],
                'full_name' => $fullName,
                'employees' => $employees,
            ]);

            return Inertia::render('inventory/Edit', [
                'item' => $item,
                'employees' => $employees,
            ]);
        } catch (\Exception $e) {
            Log::error('InventoryController@edit: Failed to fetch inventory item', [
                'asset_id' => $asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => 'Unknown Item',
                    'category' => 'Uncategorized',
                    'location' => 'Unknown Location',
                    'purchase_date' => '',
                    'value' => 0,
                    'condition' => 'New',
                    'property_no' => '',
                    'serial_no' => '',
                    'serviceable' => 0,
                    'unserviceable' => 0,
                    'coa_representative' => '',
                    'coa_date' => '',
                    'assigned' => null,
                    'assigned_to' => null, // Alias for frontend
                    'full_name' => 'Unassigned',
                    'assigned_date' => '',
                    'unit_qty' => 1,
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
            ]);
        }
    }

    public function update(Request $request, $asset_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'value' => 'required|numeric|min:0|max:9999999.99',
            'condition' => 'required|string|in:New,Good,Fair,Poor',
            'assigned' => 'nullable|integer|exists:employees,id',
            'property_no' => 'required|string|max:255',
            'serial_no' => 'required|string|max:255',
            'serviceable' => 'required|numeric|min:0|max:9999999.99',
            'unserviceable' => 'required|numeric|min:0|max:9999999.99',
            'coa_representative' => 'required|string|max:255',
            'coa_date' => 'required|date',
            'assigned_date' => 'required|date',
            'unit_qty' => 'required|integer|min:0|max:9999999',
        ]);

        if ($validator->fails()) {
            Log::warning('InventoryController@update: Validation failed', [
                'asset_id' => $asset_id,
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all(),
            ]);

            $assignedTo = $request->input('assigned') ? Employee::find($request->input('assigned')) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';

            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => $request->input('name', 'Unknown Item'),
                    'category' => $request->input('category', 'Uncategorized'),
                    'location' => $request->input('location', 'Unknown Location'),
                    'purchase_date' => $request->input('purchase_date', ''),
                    'value' => (float) $request->input('value', 0),
                    'condition' => $request->input('condition', 'New'),
                    'property_no' => $request->input('property_no', ''),
                    'serial_no' => $request->input('serial_no', ''),
                    'serviceable' => (float) $request->input('serviceable', 0),
                    'unserviceable' => (float) $request->input('unserviceable', 0),
                    'coa_representative' => $request->input('coa_representative', ''),
                    'coa_date' => $request->input('coa_date', ''),
                    'assigned' => $request->input('assigned', null),
                    'assigned_to' => $request->input('assigned', null), // Alias for frontend
                    'full_name' => $fullName,
                    'assigned_date' => $request->input('assigned_date', ''),
                    'unit_qty' => (int) $request->input('unit_qty', 1),
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        try {
            DB::beginTransaction();

            $inventory = Inventory::where('asset_id', $asset_id)->firstOrFail();
            $data = $request->all();
            // Map assigned_to to assigned if present
            if ($request->has('assigned_to')) {
                $data['assigned'] = $request->input('assigned_to') !== '' ? (int)$request->input('assigned_to') : null;
                unset($data['assigned_to']);
            }

            $inventory->update([
                'name' => $data['name'],
                'category' => $data['category'],
                'location' => $data['location'],
                'purchase_date' => $data['purchase_date'],
                'value' => $data['value'],
                'condition' => $data['condition'],
                'property_no' => $data['property_no'],
                'serial_no' => $data['serial_no'],
                'serviceable' => $data['serviceable'],
                'unserviceable' => $data['unserviceable'],
                'coa_representative' => $data['coa_representative'],
                'coa_date' => $data['coa_date'],
                'assigned' => $data['assigned'],
                'assigned_date' => $data['assigned_date'],
                'unit_qty' => $data['unit_qty'],
            ]);

            DB::commit();

            Log::info('InventoryController@update: Inventory item updated', [
                'asset_id' => $asset_id,
                'data' => $data,
            ]);

            return redirect()->route('inventory.index')->with('message', 'Inventory item updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('InventoryController@update: Failed to update inventory item', [
                'asset_id' => $asset_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            $assignedTo = $request->input('assigned') ? Employee::find($request->input('assigned')) : null;
            $fullName = $assignedTo 
                ? trim(
                    implode(' ', array_filter([
                        $assignedTo->first_name,
                        $assignedTo->middle_name,
                        $assignedTo->last_name,
                    ], fn($value) => !is_null($value) && $value !== ''))
                ) ?: 'Unnamed Employee'
                : 'Unassigned';
            return Inertia::render('inventory/Edit', [
                'item' => [
                    'id' => $asset_id,
                    'name' => $request->input('name', 'Unknown Item'),
                    'category' => $request->input('category', 'Uncategorized'),
                    'location' => $request->input('location', 'Unknown Location'),
                    'purchase_date' => $request->input('purchase_date', ''),
                    'value' => (float) $request->input('value', 0),
                    'condition' => $request->input('condition', 'New'),
                    'property_no' => $request->input('property_no', ''),
                    'serial_no' => $request->input('serial_no', ''),
                    'serviceable' => (float) $request->input('serviceable', 0),
                    'unserviceable' => (float) $request->input('unserviceable', 0),
                    'coa_representative' => $request->input('coa_representative', ''),
                    'coa_date' => $request->input('coa_date', ''),
                    'assigned' => $request->input('assigned', null),
                    'assigned_to' => $request->input('assigned', null), // Alias for frontend
                    'full_name' => $fullName,
                    'assigned_date' => $request->input('assigned_date', ''),
                    'unit_qty' => (int) $request->input('unit_qty', 1),
                ],
                'employees' => Employee::all()->map(function ($employee) {
                    $fullName = trim(
                        implode(' ', array_filter([
                            $employee->first_name,
                            $employee->middle_name,
                            $employee->last_name,
                        ], fn($value) => !is_null($value) && $value !== ''))
                    ) ?: 'Unnamed Employee';
                    return [
                        'id' => $employee->id,
                        'full_name' => $fullName,
                    ];
                })->toArray(),
                'errors' => ['general' => 'Failed to update inventory item: ' . $e->getMessage()],
            ]);
        }
    }

    public function destroy(Inventory $inventory)
    {
        try {
            DB::beginTransaction();

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
                'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
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
            'property_no' => (string) $inventoryModel->property_no,
            'serial_no' => (string) $inventoryModel->serial_no,
            'serviceable' => (string) $inventoryModel->serviceable,
            'unserviceable' => (string) $inventoryModel->unserviceable,
            'coa_representative' => (string) $inventoryModel->coa_representative,
            'coa_date' => (string) $inventoryModel->coa_date,
            'assigned_date' => (string) $inventoryModel->assigned_date,
            'unit_qty' => (float) $inventoryModel->unit_qty,
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
                'full_name' => $fullName,
                'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
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
            'property_no' => (string) $inventoryModel->property_no,
            'serial_no' => (string) $inventoryModel->serial_no,
            'serviceable' => (string) $inventoryModel->serviceable,
            'unserviceable' => (string) $inventoryModel->unserviceable,
            'coa_representative' => (string) $inventoryModel->coa_representative,
            'coa_date' => (string) $inventoryModel->coa_date,
            'assigned_date' => (string) $inventoryModel->assigned_date,
            'unit_qty' => (float) $inventoryModel->unit_qty,
            'qr_code' => $qrCodeBase64,
            'image' => $inventoryModel->image ? Storage::url($inventoryModel->image) : null,
        ];
    
        Log::info('Tag Asset Data', ['asset' => $assetData]);
    
        return Inertia::render('inventory/AssetTag', [
            'asset' => $assetData,
            'error' => null
        ]);
    }
}