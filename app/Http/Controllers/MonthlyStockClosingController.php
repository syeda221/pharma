<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MonthlyStockClosing;
use App\Models\MonthlyStockClosingItem;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyStockClosingController extends Controller
{
    /**
     * Check permission
     */
    private function checkPermission()
    {
        $user = auth()->user();
        if ($user->email === 'admin@admin.com' || $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->hasRole('admin')) {
            return true;
        }

        if ($user->can('stock.closing.view') || $user->can('stock.closing.create')) {
            return true;
        }

        abort(403, 'Unauthorized action. You do not have permission to access Monthly Stock Closing.');
    }

    /**
     * Get warehouses permitted for current user
     */
    private function getPermittedWarehouses()
    {
        $user = auth()->user();

        if ($user->email === 'admin@admin.com' || $user->hasRole('Super Admin') || $user->hasRole('Admin') || $user->can('warehouse.view')) {
            return Warehouse::orderBy('warehouse_name')->get();
        }

        if (isset($user->warehouse_id) && $user->warehouse_id) {
            return Warehouse::where('id', $user->warehouse_id)->get();
        }

        $userWarehouses = Warehouse::where('creater_id', $user->id)->get();
        if ($userWarehouses->count() > 0) {
            return $userWarehouses;
        }

        return Warehouse::limit(1)->get();
    }

    /**
     * Get Main Warehouse (Default store)
     */
    private function getMainWarehouse()
    {
        return Warehouse::where('warehouse_name', 'like', '%main%')->first() 
            ?: (Warehouse::where('id', 1)->first() ?: Warehouse::first());
    }

    /**
     * List historical Monthly Closings
     */
    public function index(Request $request)
    {
        $this->checkPermission();

        $mainWarehouse = $this->getMainWarehouse();
        $warehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        $query = MonthlyStockClosing::with(['warehouse', 'creator'])
            ->where('warehouse_id', $warehouseId);

        if ($request->filled('closing_month')) {
            $query->where('closing_month', $request->closing_month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('closing_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhere('reason_type', 'like', "%{$search}%");
            });
        }

        $closings = $query->latest('id')->paginate(15)->appends($request->query());

        // Quick KPI Stats
        $totalClosings = MonthlyStockClosing::where('warehouse_id', $warehouseId)->count();
        $totalAdjusted = MonthlyStockClosing::where('warehouse_id', $warehouseId)->where('adjustment_mode', '!=', 'none')->count();
        $thisMonthClosings = MonthlyStockClosing::where('warehouse_id', $warehouseId)
            ->where('closing_month', now()->format('Y-m'))
            ->count();

        return view('admin_panel.stock_closing.index', compact(
            'closings',
            'mainWarehouse',
            'totalClosings',
            'totalAdjusted',
            'thisMonthClosings'
        ));
    }

    /**
     * Show Form to conduct a new Monthly Stock Closing
     */
    public function create(Request $request)
    {
        $this->checkPermission();

        $mainWarehouse = $this->getMainWarehouse();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        $currentMonth = now()->format('Y-m');

        return view('admin_panel.stock_closing.create', compact(
            'mainWarehouse',
            'categories',
            'brands',
            'currentMonth'
        ));
    }

    /**
     * AJAX Endpoint: Fetch products and their current system stock for selected warehouse
     */
    public function getWarehouseProducts(Request $request)
    {
        $this->checkPermission();

        $mainWarehouse = $this->getMainWarehouse();
        $warehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        $reportingRequest = new Request([
            'warehouse_id' => $warehouseId,
            'category_id'  => $request->query('category_id'),
            'brand_id'     => $request->query('brand_id'),
            'search'       => $request->query('search'),
        ]);

        $reportingController = app()->make(ReportingController::class);
        $response = $reportingController->fetchItemStock($reportingRequest);
        $responseData = json_decode($response->getContent(), true);
        $stockRows = $responseData['data'] ?? [];

        $items = [];
        foreach ($stockRows as $row) {
            $ppb = (float) ($row['ppb'] ?? ($row['pieces_per_box'] ?? 1));
            if ($ppb <= 0) $ppb = 1;

            $totalPieces = (float) ($row['balance'] ?? 0);
            $cartons = (is_numeric($row['cartons'])) ? (float) $row['cartons'] : floor($totalPieces / $ppb);
            $loose   = (is_numeric($row['loose'])) ? (float) $row['loose'] : ($totalPieces - ($cartons * $ppb));

            $items[] = [
                'product_id'          => $row['id'],
                'variant_key'         => $row['variant_key'] ?? null,
                'variant_name'        => $row['variant_name'] ?? null,
                'item_code'           => $row['item_code'],
                'item_name'           => $row['item_name'],
                'category_id'         => $row['category_id'] ?? null,
                'category_name'       => $row['category_name'] ?? 'General',
                'brand_id'            => $row['brand_id'] ?? null,
                'unit_name'           => $row['unit_name'] ?? 'Pcs',
                'pieces_per_box'      => $ppb,
                'system_total_pieces' => $totalPieces,
                'system_cartons'      => $cartons,
                'system_pieces'       => $loose,
                'system_formatted'    => $row['formatted_stock'] ?? "{$totalPieces} Pcs",
            ];
        }

        return response()->json([
            'success'        => true,
            'warehouse_id'   => $warehouseId,
            'warehouse_name' => $mainWarehouse->warehouse_name ?? 'Main Store',
            'total_items'    => count($items),
            'items'          => $items
        ]);
    }

    /**
     * Check if a closing already exists for the given month
     */
    public function checkMonthClosing(Request $request)
    {
        $this->checkPermission();
        $month = $request->query('month');
        $mainWarehouse = $this->getMainWarehouse();
        $warehouseId = $mainWarehouse ? $mainWarehouse->id : 1;

        $existing = MonthlyStockClosing::where('warehouse_id', $warehouseId)
            ->where('closing_month', $month)
            ->first();

        if ($existing) {
            return response()->json([
                'exists'       => true,
                'closing_id'   => $existing->id,
                'closing_code' => $existing->closing_code,
                'closing_date' => $existing->closing_date,
                'edit_url'     => route('stock-closing.edit', $existing->id),
                'show_url'     => route('stock-closing.show', $existing->id),
                'message'      => "A monthly stock closing for {$month} already exists ({$existing->closing_code} on {$existing->closing_date}). You can edit the existing audit instead of creating a duplicate."
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }

    /**
     * Store and Process Monthly Stock Closing
     */
    public function store(Request $request)
    {
        $this->checkPermission();

        $mainWarehouse = $this->getMainWarehouse();
        $defaultWhId   = $mainWarehouse ? $mainWarehouse->id : 1;

        if (!$request->filled('warehouse_id')) {
            $request->merge(['warehouse_id' => $defaultWhId]);
        }

        $request->validate([
            'warehouse_id'                => 'required|exists:warehouses,id',
            'closing_month'               => 'required|string|size:7',
            'closing_date'                => 'required|date',
            'adjustment_mode'             => 'required|in:equal,selective,none',
            'reason_type'                 => 'nullable|string|max:100',
            'remarks'                     => 'nullable|string|max:2000',
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.variant_key'         => 'nullable|string',
            'items.*.variant_name'        => 'nullable|string',
            'items.*.pieces_per_box'      => 'nullable|numeric|min:1',
            'items.*.system_total_pieces' => 'nullable|numeric',
            'items.*.physical_cartons'    => 'nullable|numeric|min:0',
            'items.*.physical_pieces'     => 'nullable|numeric|min:0',
            'items.*.is_adjusted'         => 'nullable',
        ]);

        // Duplicate month prevention
        $month = $request->closing_month;
        $existing = MonthlyStockClosing::where('warehouse_id', $defaultWhId)
            ->where('closing_month', $month)
            ->first();

        if ($existing) {
            return response()->json([
                'success'    => false,
                'message'    => "A monthly stock closing for {$month} already exists ({$existing->closing_code}). Please edit the existing closing or select another month.",
                'closing_id' => $existing->id,
                'edit_url'   => route('stock-closing.edit', $existing->id),
            ], 422);
        }

        try {
            $closing = DB::transaction(function () use ($request, $defaultWhId) {
                $user = auth()->user();
                $warehouseId = $request->warehouse_id ?: $defaultWhId;
                $month = $request->closing_month;
                $adjMode = $request->adjustment_mode;
                $reasonType = $request->reason_type ?: 'Physical Count Correction';
                $remarks = $request->remarks ?: '';

                // Generate closing code: MSC-YYYYMM-XXXX
                $count = MonthlyStockClosing::where('closing_month', $month)->count() + 1;
                $closingCode = 'MSC-' . str_replace('-', '', $month) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

                $closing = MonthlyStockClosing::create([
                    'closing_code'          => $closingCode,
                    'warehouse_id'          => $warehouseId,
                    'closing_month'         => $month,
                    'closing_date'          => $request->closing_date,
                    'adjustment_mode'       => $adjMode,
                    'reason_type'           => $reasonType,
                    'remarks'               => $remarks,
                    'status'                => 'posted',
                    'created_by'            => $user->id,
                ]);

                $totalItems = 0;
                $matchedCount = 0;
                $discrepantCount = 0;
                $totalSystemPieces = 0;
                $totalPhysicalPieces = 0;

                // Load products
                $productIds = collect($request->items)->pluck('product_id')->unique()->toArray();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                // Load existing warehouse stocks map with lock
                $existingStocks = WarehouseStock::where('warehouse_id', $warehouseId)
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($request->items as $itemData) {
                    $productId = $itemData['product_id'];
                    $product   = $products->get($productId);
                    if (!$product) continue;

                    $totalItems++;
                    $variantKey  = !empty($itemData['variant_key']) ? trim($itemData['variant_key']) : null;
                    $variantName = !empty($itemData['variant_name']) ? trim($itemData['variant_name']) : null;

                    $ppb = (float) ($itemData['pieces_per_box'] ?? ($product->pieces_per_box ?? 1));
                    if ($ppb <= 0) $ppb = 1;

                    // Current System Stock from row payload
                    $systemTotalPieces = (float) ($itemData['system_total_pieces'] ?? 0);
                    $systemCartons = floor($systemTotalPieces / $ppb);
                    $systemPieces = $systemTotalPieces - ($systemCartons * $ppb);

                    // Physical Stock Input
                    $physicalCartons = (float) ($itemData['physical_cartons'] ?? 0);
                    $physicalPieces  = (float) ($itemData['physical_pieces'] ?? 0);
                    $physicalTotalPieces = ($physicalCartons * $ppb) + $physicalPieces;

                    // Variance
                    $variancePieces = $physicalTotalPieces - $systemTotalPieces;

                    $status = 'matched';
                    if ($variancePieces < 0) {
                        $status = 'shortage';
                        $discrepantCount++;
                    } elseif ($variancePieces > 0) {
                        $status = 'surplus';
                        $discrepantCount++;
                    } else {
                        $matchedCount++;
                    }

                    // Format variance display string
                    $vAbs = abs($variancePieces);
                    $vCtns = floor($vAbs / $ppb);
                    $vPcs  = $vAbs - ($vCtns * $ppb);
                    $vSign = $variancePieces < 0 ? '-' : ($variancePieces > 0 ? '+' : '');
                    $varianceDisplay = ($variancePieces == 0) ? '0 Pcs' : (
                        ($ppb > 1) 
                            ? "{$vSign}{$vCtns} Ctn" . ($vPcs > 0 ? " {$vPcs} Pcs" : "") . " ({$vSign}{$vAbs} pcs)"
                            : "{$vSign}{$vAbs} Pcs"
                    );

                    $totalSystemPieces += $systemTotalPieces;
                    $totalPhysicalPieces += $physicalTotalPieces;

                    // Should this item be adjusted in system stock?
                    $shouldAdjust = false;
                    if ($variancePieces != 0) {
                        if ($adjMode === 'equal') {
                            $shouldAdjust = true;
                        } elseif ($adjMode === 'selective' && !empty($itemData['is_adjusted'])) {
                            $shouldAdjust = true;
                        }
                    }

                    $oldStock = $systemTotalPieces;
                    $newStock = $shouldAdjust ? $physicalTotalPieces : $systemTotalPieces;

                    // Execute Stock Adjustment if applicable
                    if ($shouldAdjust) {
                        // 1. Log stock movement
                        $movementNote = "Warehouse #{$warehouseId} | " 
                            . ($variantKey ? "[{$variantKey}] " : '') 
                            . ($variantName ? "Variant: {$variantName} | " : '') 
                            . "Closing #{$closingCode} | Reason: {$reasonType}" 
                            . ($remarks ? " - {$remarks}" : '');

                        DB::table('stock_movements')->insert([
                            'product_id'   => $productId,
                            'type'         => 'adjustment',
                            'qty'          => $variancePieces,
                            'ref_type'     => 'STOCK_CLOSING',
                            'ref_id'       => $closing->id,
                            'note'         => $movementNote,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);

                        // 2. Update WarehouseStock
                        $targetStock = $existingStocks->get($productId);
                        if (!$targetStock) {
                            $targetStock = WarehouseStock::create([
                                'warehouse_id' => $warehouseId,
                                'product_id'   => $productId,
                                'quantity'     => 0,
                                'total_pieces' => 0
                            ]);
                            $existingStocks->put($productId, $targetStock);
                        }

                        $currentWhTotal = (float) $targetStock->total_pieces;
                        $updatedWhTotal = max(0, $currentWhTotal + $variancePieces);
                        $targetStock->total_pieces = $updatedWhTotal;

                        if ($ppb > 1 && in_array($product->size_mode, ['by_cartons', 'by_size'])) {
                            $targetStock->quantity = floor($updatedWhTotal / $ppb);
                        } else {
                            $targetStock->quantity = $updatedWhTotal;
                        }
                        $targetStock->save();

                        // 3. Create formal StockAdjustment audit log entry
                        StockAdjustment::create([
                            'warehouse_id' => $warehouseId,
                            'product_id'   => $productId,
                            'variant_key'  => $variantKey,
                            'variant_name' => $variantName,
                            'type'         => $variancePieces >= 0 ? 'add' : 'subtract',
                            'old_stock'    => $oldStock,
                            'new_stock'    => $newStock,
                            'qty'          => abs($variancePieces),
                            'reason'       => "Monthly Closing [{$closingCode}] ({$reasonType}): " . ($remarks ?: 'Reconciled to physical count'),
                            'user_id'      => $user->id,
                        ]);
                    }

                    MonthlyStockClosingItem::create([
                        'closing_id'            => $closing->id,
                        'product_id'            => $productId,
                        'variant_key'           => $variantKey,
                        'variant_name'          => $variantName,
                        'pieces_per_box'        => $ppb,
                        'system_cartons'        => $systemCartons,
                        'system_pieces'         => $systemPieces,
                        'system_total_pieces'   => $systemTotalPieces,
                        'physical_cartons'      => $physicalCartons,
                        'physical_pieces'       => $physicalPieces,
                        'physical_total_pieces' => $physicalTotalPieces,
                        'variance_pieces'       => $variancePieces,
                        'variance_display'      => $varianceDisplay,
                        'status'                => $status,
                        'is_adjusted'           => $shouldAdjust,
                        'old_stock'             => $oldStock,
                        'new_stock'             => $newStock,
                        'item_reason'           => $itemData['item_reason'] ?? null,
                    ]);
                }

                // Update Closing Summary Stats
                $closing->update([
                    'total_items_count'     => $totalItems,
                    'matched_count'         => $matchedCount,
                    'discrepant_count'      => $discrepantCount,
                    'total_system_pieces'   => $totalSystemPieces,
                    'total_physical_pieces' => $totalPhysicalPieces,
                    'net_variance_pieces'   => $totalPhysicalPieces - $totalSystemPieces,
                ]);

                return $closing;
            });

            return response()->json([
                'success' => true,
                'message' => "Monthly Stock Closing ({$closing->closing_code}) saved successfully!",
                'closing_id' => $closing->id,
                'redirect_url' => route('stock-closing.show', $closing->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save monthly closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Details of a Monthly Closing
     */
    public function show($id)
    {
        $this->checkPermission();

        $closing = MonthlyStockClosing::with(['warehouse', 'creator', 'items.product.category_relation', 'items.product.unit'])
            ->findOrFail($id);

        return view('admin_panel.stock_closing.show', compact('closing'));
    }

    /**
     * Show Form to Edit an existing Monthly Closing
     */
    public function edit($id)
    {
        $this->checkPermission();

        $closing = MonthlyStockClosing::with([
            'warehouse', 
            'creator', 
            'items.product.category_relation', 
            'items.product.unit'
        ])->findOrFail($id);

        $mainWarehouse = $this->getMainWarehouse();
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        $itemsData = $closing->items->map(function ($it) {
            $p = $it->product;
            $ppb = $it->pieces_per_box > 0 ? (float) $it->pieces_per_box : 1;
            $sysTotal = (float) $it->system_total_pieces;
            $sysCtns = floor($sysTotal / $ppb);
            $sysPcs = $sysTotal - ($sysCtns * $ppb);

            return [
                'uid'                 => $it->product_id . '_' . ($it->variant_key ? urlencode($it->variant_key) : 'base_' . $it->id),
                'id'                  => $it->id,
                'product_id'          => $it->product_id,
                'variant_key'         => $it->variant_key,
                'variant_name'        => $it->variant_name,
                'item_code'           => $p->item_code ?? '-',
                'item_name'           => $it->variant_name ?: ($p->item_name ?? 'Product #' . $it->product_id),
                'category_id'         => $p->category_id ?? null,
                'category_name'       => $p->category_relation->name ?? 'General',
                'brand_id'            => $p->brand_id ?? null,
                'unit_name'           => $p->unit->name ?? 'Pcs',
                'pieces_per_box'      => $ppb,
                'system_total_pieces' => $sysTotal,
                'system_cartons'      => $sysCtns,
                'system_pieces'       => $sysPcs,
                'system_formatted'    => $sysCtns . ' Ctn' . ($sysPcs > 0 ? ' + ' . $sysPcs . ' Pcs' : ''),
                'physical_cartons'      => (float) $it->physical_cartons,
                'physical_pieces'       => (float) $it->physical_pieces,
                'physical_total_pieces' => (float) $it->physical_total_pieces,
                'variance_pieces'       => (float) $it->variance_pieces,
                'status'                => $it->status,
                'is_adjusted'           => (bool) $it->is_adjusted,
            ];
        });

        return view('admin_panel.stock_closing.edit', compact(
            'closing',
            'mainWarehouse',
            'categories',
            'brands',
            'itemsData'
        ));
    }

    /**
     * Update an existing Monthly Closing and re-adjust stock
     */
    public function update(Request $request, $id)
    {
        $this->checkPermission();

        $closing = MonthlyStockClosing::with('items')->findOrFail($id);

        $request->validate([
            'closing_date'                => 'required|date',
            'adjustment_mode'             => 'required|in:equal,selective,none',
            'reason_type'                 => 'nullable|string|max:100',
            'remarks'                     => 'nullable|string|max:2000',
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|exists:products,id',
            'items.*.physical_cartons'    => 'nullable|numeric|min:0',
            'items.*.physical_pieces'     => 'nullable|numeric|min:0',
            'items.*.is_adjusted'         => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request, $closing) {
                $user = auth()->user();
                $warehouseId = $closing->warehouse_id;
                $adjMode = $request->adjustment_mode;
                $reasonType = $request->reason_type ?: 'Physical Count Correction (Edited)';
                $remarks = $request->remarks ?: '';

                // 1. REVERSE PREVIOUS ADJUSTMENTS IF ANY WERE MADE
                $oldMovements = DB::table('stock_movements')
                    ->where('ref_type', 'STOCK_CLOSING')
                    ->where('ref_id', $closing->id)
                    ->get();

                foreach ($oldMovements as $om) {
                    $reverseDelta = -1 * ((float) $om->qty);
                    $whStock = WarehouseStock::where('warehouse_id', $warehouseId)
                        ->where('product_id', $om->product_id)
                        ->first();

                    if ($whStock) {
                        $newPieces = max(0, ((float)$whStock->total_pieces) + $reverseDelta);
                        $whStock->total_pieces = $newPieces;
                        $prod = Product::find($om->product_id);
                        $ppb = ($prod && $prod->pieces_per_box > 0) ? $prod->pieces_per_box : 1;
                        if ($ppb > 1 && $prod && in_array($prod->size_mode, ['by_cartons', 'by_size'])) {
                            $whStock->quantity = floor($newPieces / $ppb);
                        } else {
                            $whStock->quantity = $newPieces;
                        }
                        $whStock->save();
                    }
                }

                // Delete old stock movements for this closing
                DB::table('stock_movements')
                    ->where('ref_type', 'STOCK_CLOSING')
                    ->where('ref_id', $closing->id)
                    ->delete();

                // Delete old StockAdjustment audit records for this closing
                StockAdjustment::where('reason', 'like', "%[{$closing->closing_code}]%")->delete();

                // 2. APPLY NEW ADJUSTMENTS & RE-CALCULATE
                $inputItemsMap = collect($request->items)->keyBy(function($item) {
                    return $item['product_id'] . '_' . ($item['variant_key'] ?? '');
                });

                $totalItems = 0;
                $matchedCount = 0;
                $discrepantCount = 0;
                $totalSystemPieces = 0;
                $totalPhysicalPieces = 0;

                // Load existing warehouse stocks with lock
                $productIds = $closing->items->pluck('product_id')->unique()->toArray();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
                $existingStocks = WarehouseStock::where('warehouse_id', $warehouseId)
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                foreach ($closing->items as $closingItem) {
                    $totalItems++;
                    $product = $products->get($closingItem->product_id);
                    if (!$product) continue;

                    $key = $closingItem->product_id . '_' . ($closingItem->variant_key ?? '');
                    $inputData = $inputItemsMap->get($key);

                    $ppb = (float) $closingItem->pieces_per_box;
                    if ($ppb <= 0) $ppb = 1;

                    // Physical Count Input
                    $physicalCartons = (float) ($inputData['physical_cartons'] ?? $closingItem->physical_cartons);
                    $physicalPieces  = (float) ($inputData['physical_pieces'] ?? $closingItem->physical_pieces);
                    $physicalTotalPieces = ($physicalCartons * $ppb) + $physicalPieces;

                    // System Stock from record
                    $systemTotalPieces = (float) $closingItem->system_total_pieces;
                    $systemCartons = floor($systemTotalPieces / $ppb);
                    $systemPieces = $systemTotalPieces - ($systemCartons * $ppb);

                    // Variance
                    $variancePieces = $physicalTotalPieces - $systemTotalPieces;

                    $status = 'matched';
                    if ($variancePieces < 0) {
                        $status = 'shortage';
                        $discrepantCount++;
                    } elseif ($variancePieces > 0) {
                        $status = 'surplus';
                        $discrepantCount++;
                    } else {
                        $matchedCount++;
                    }

                    $vAbs  = abs($variancePieces);
                    $vCtns = floor($vAbs / $ppb);
                    $vPcs  = $vAbs - ($vCtns * $ppb);
                    $vSign = $variancePieces < 0 ? '-' : ($variancePieces > 0 ? '+' : '');
                    $varianceDisplay = ($variancePieces == 0) ? '0 Pcs' : (
                        ($ppb > 1) 
                            ? "{$vSign}{$vCtns} Ctn" . ($vPcs > 0 ? " {$vPcs} Pcs" : "") . " ({$vSign}{$vAbs} pcs)"
                            : "{$vSign}{$vAbs} Pcs"
                    );

                    $totalSystemPieces   += $systemTotalPieces;
                    $totalPhysicalPieces += $physicalTotalPieces;

                    $shouldAdjust = false;
                    if ($variancePieces != 0) {
                        if ($adjMode === 'equal') {
                            $shouldAdjust = true;
                        } elseif ($adjMode === 'selective' && !empty($inputData['is_adjusted'])) {
                            $shouldAdjust = true;
                        }
                    }

                    $oldStock = $systemTotalPieces;
                    $newStock = $shouldAdjust ? $physicalTotalPieces : $systemTotalPieces;

                    if ($shouldAdjust) {
                        // 1. Log new stock movement
                        $movementNote = "Warehouse #{$warehouseId} | " 
                            . ($closingItem->variant_key ? "[{$closingItem->variant_key}] " : '') 
                            . ($closingItem->variant_name ? "Variant: {$closingItem->variant_name} | " : '') 
                            . "Closing #{$closing->closing_code} | Reason: {$reasonType}" 
                            . ($remarks ? " - {$remarks}" : '');

                        DB::table('stock_movements')->insert([
                            'product_id'   => $product->id,
                            'type'         => 'adjustment',
                            'qty'          => $variancePieces,
                            'ref_type'     => 'STOCK_CLOSING',
                            'ref_id'       => $closing->id,
                            'note'         => $movementNote,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);

                        // 2. Update WarehouseStock
                        $targetStock = $existingStocks->get($product->id);
                        if ($targetStock) {
                            $currentWhTotal = (float) $targetStock->total_pieces;
                            $updatedWhTotal = max(0, $currentWhTotal + $variancePieces);
                            $targetStock->total_pieces = $updatedWhTotal;

                            if ($ppb > 1 && in_array($product->size_mode, ['by_cartons', 'by_size'])) {
                                $targetStock->quantity = floor($updatedWhTotal / $ppb);
                            } else {
                                $targetStock->quantity = $updatedWhTotal;
                            }
                            $targetStock->save();
                        }

                        // 3. Create StockAdjustment audit record
                        StockAdjustment::create([
                            'warehouse_id' => $warehouseId,
                            'product_id'   => $product->id,
                            'variant_key'  => $closingItem->variant_key,
                            'variant_name' => $closingItem->variant_name,
                            'type'         => $variancePieces >= 0 ? 'add' : 'subtract',
                            'old_stock'    => $oldStock,
                            'new_stock'    => $newStock,
                            'qty'          => abs($variancePieces),
                            'reason'       => "Monthly Closing [{$closing->closing_code}] ({$reasonType}): " . ($remarks ?: 'Re-adjusted physical count'),
                            'user_id'      => $user->id,
                        ]);
                    }

                    // Update closing item row
                    $closingItem->update([
                        'physical_cartons'      => $physicalCartons,
                        'physical_pieces'       => $physicalPieces,
                        'physical_total_pieces' => $physicalTotalPieces,
                        'variance_pieces'       => $variancePieces,
                        'variance_display'      => $varianceDisplay,
                        'status'                => $status,
                        'is_adjusted'           => $shouldAdjust,
                        'old_stock'             => $oldStock,
                        'new_stock'             => $newStock,
                    ]);
                }

                // Update Closing Header
                $closing->update([
                    'closing_date'          => $request->closing_date,
                    'adjustment_mode'       => $adjMode,
                    'reason_type'           => $reasonType,
                    'remarks'               => $remarks,
                    'total_items_count'     => $totalItems,
                    'matched_count'         => $matchedCount,
                    'discrepant_count'      => $discrepantCount,
                    'total_system_pieces'   => $totalSystemPieces,
                    'total_physical_pieces' => $totalPhysicalPieces,
                    'net_variance_pieces'   => $totalPhysicalPieces - $totalSystemPieces,
                ]);
            });

            return response()->json([
                'success'      => true,
                'message'      => "Monthly Stock Closing ({$closing->closing_code}) has been updated successfully!",
                'redirect_url' => route('stock-closing.show', $closing->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update monthly stock closing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Printable A4 / PDF Audit Sheet
     */
    public function printSheet($id)
    {
        $this->checkPermission();

        $closing = MonthlyStockClosing::with(['warehouse', 'creator', 'items.product.category_relation', 'items.product.unit'])
            ->findOrFail($id);

        return view('admin_panel.stock_closing.print', compact('closing'));
    }
}
