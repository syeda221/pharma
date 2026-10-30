<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\MedicalSample;
use App\Models\MedicalSampleItem;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Customer;
use App\Models\StockMovement;

class MedicalSampleController extends Controller
{
    /**
     * Store newly issued medical / physician sample.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sample_date' => 'required|date',
            'doctor_name' => 'nullable|string|max:150',
            'mr_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0.01',
            'variant_color' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $sampleNo = MedicalSample::generateSampleNo();

            // Resolve Customer ID & Doctor Name
            $customerId = $validated['customer_id'] ?? null;
            $doctorName = trim($validated['doctor_name'] ?? '');

            if ($customerId && empty($doctorName)) {
                $cObj = Customer::find($customerId);
                if ($cObj) {
                    $doctorName = $cObj->customer_name;
                }
            }

            if (!$customerId && !empty($doctorName)) {
                $matchedCust = Customer::where('customer_name', $doctorName)->first();
                $customerId = $matchedCust ? $matchedCust->id : (Customer::first()?->id);
            } elseif (!$customerId) {
                $defaultCust = Customer::first();
                $customerId = $defaultCust ? $defaultCust->id : null;
            }

            $sample = MedicalSample::create([
                'sample_no' => $sampleNo,
                'customer_id' => $customerId,
                'warehouse_id' => $validated['warehouse_id'],
                'sample_date' => $validated['sample_date'],
                'doctor_name' => $doctorName ?: ($validated['doctor_name'] ?? null),
                'mr_name' => $validated['mr_name'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => 'posted',
                'created_by' => auth()->id(),
            ]);

            $totalPieces = 0;
            $totalWos = 0;

            foreach ($request->product_id as $idx => $productId) {
                $qty = (float)($request->qty[$idx] ?? 0);
                if ($qty <= 0) continue;

                $product = Product::find($productId);
                if (!$product) continue;

                $ppb = (float)($product->pieces_per_box ?: 1);
                if ($ppb <= 0) $ppb = 1;

                $purchPrice = (float)($product->purchase_price_per_piece ?: 0);
                $retailPrice = (float)($product->retail_price ?: ($product->sale_price_per_piece ?: 0));

                $rColor = $request->variant_color[$idx] ?? null;
                if (!empty($rColor)) {
                    try {
                        $vData = is_string($rColor) ? json_decode(base64_decode($rColor, true) ?: $rColor, true) : $rColor;
                        if (is_array($vData)) {
                            if (!empty($vData['purch_price']) && (float)$vData['purch_price'] > 0) {
                                $purchPrice = (float)$vData['purch_price'];
                            }
                            if (!empty($vData['sale_price']) && (float)$vData['sale_price'] > 0) {
                                $retailPrice = (float)$vData['sale_price'];
                            }
                            if (!empty($vData['conv_factor']) && (float)$vData['conv_factor'] > 0) {
                                $ppb = (float)$vData['conv_factor'];
                            }
                        }
                    } catch (\Exception $e) {}
                }

                $wosLine = round($qty * $purchPrice, 2);
                $totalPieces += $qty;
                $totalWos += $wosLine;

                // Create item
                MedicalSampleItem::create([
                    'medical_sample_id' => $sample->id,
                    'product_id' => $productId,
                    'variant_color' => $rColor,
                    'qty' => $qty,
                    'purchase_price' => $purchPrice,
                    'retail_price' => $retailPrice,
                    'wos_line_total' => $wosLine,
                ]);

                // Reduce Stock from Warehouse (Total Pieces)
                $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $stock->total_pieces = (float)$stock->total_pieces - $qty;
                    $stock->quantity = $stock->total_pieces / $ppb;
                    $stock->save();
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_id' => $productId,
                        'total_pieces' => -$qty,
                        'quantity' => -$qty / $ppb,
                        'price' => $purchPrice,
                    ]);
                }

                // Record Stock Movement (type='out', qty=-$qty for physical deduction tracking)
                try {
                    DB::table('stock_movements')->insert([
                        'product_id' => $productId,
                        'type' => 'out',
                        'qty' => -$qty,
                        'ref_type' => 'sample',
                        'ref_id' => $sample->id,
                        'note' => 'Physician Sample Issued #' . $sampleNo . ($sample->doctor_name ? ' (' . $sample->doctor_name . ')' : '') . " (Warehouse #{$validated['warehouse_id']})",
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('StockMovement record failed for sample: ' . $e->getMessage());
                }
            }

            $sample->update([
                'total_pieces' => $totalPieces,
                'wos_amount' => $totalWos,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sample {$sampleNo} issued successfully! Stock deducted.",
                'sample_id' => $sample->id,
                'sample_no' => $sampleNo,
                'total_pieces' => $totalPieces,
                'wos_amount' => $totalWos,
                'print_url' => route('samples.view', $sample->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Issue Sample Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to issue sample: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Printable Medical Sample Slip / Delivery Challan.
     */
    public function view($id)
    {
        $sample = MedicalSample::with(['items.product', 'customer', 'warehouse', 'createdBy'])->findOrFail($id);
        return view('admin_panel.sale.samples.view', compact('sample'));
    }

    /**
     * Delete sample and revert stock.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $sample = MedicalSample::with('items')->findOrFail($id);

            // Revert stock
            foreach ($sample->items as $it) {
                $stock = WarehouseStock::where('warehouse_id', $sample->warehouse_id)
                    ->where('product_id', $it->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    $prod = Product::find($it->product_id);
                    $ppb = (float)($prod->pieces_per_box ?: 1);
                    if ($ppb <= 0) $ppb = 1;
                    $stock->total_pieces = (float)$stock->total_pieces + (float)$it->qty;
                    $stock->quantity = $stock->total_pieces / $ppb;
                    $stock->save();
                }
            }

            // Remove stock movements recorded for this sample
            DB::table('stock_movements')
                ->where('ref_type', 'sample')
                ->where('ref_id', $sample->id)
                ->delete();

            $sample->delete();
            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sample deleted and stock restored.']);
            }
            return redirect()->back()->with('success', 'Sample deleted and stock restored.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error deleting sample: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error deleting sample: ' . $e->getMessage());
        }
    }
}
