<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DistributorStock;
use App\Models\DistributorSalesReport;
use App\Models\DistributorSalesReportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributorReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DistributorSalesReport::with('distributor', 'zone', 'items');

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->distributor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('report_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('report_date', '<=', $request->to_date);
        }

        $reports = $query->orderBy('id', 'desc')->get();
        
        $distributors = Customer::all();

        return view('distributor_report.index', compact('reports', 'distributors'));
    }

    /**
     * Show the form for creating a new report.
     */
    public function create()
    {
        $distributors = Customer::all();
        $zones = \App\Models\Zone::all();
        return view('distributor_report.create', compact('distributors', 'zones'));
    }

    /**
     * Get stock for a specific distributor.
     */
    public function getDistributorStock($distributorId)
    {
        $stocks = DistributorStock::with('product')
            ->where('distributor_id', $distributorId)
            ->where('current_quantity', '>', 0)
            ->get();

        $stocks->transform(function($stock) {
            $ppb = $stock->product ? ($stock->product->pieces_per_box ?: 1) : 1;
            $price = $stock->product ? ($stock->product->sale_price_per_piece ?: 0) : 0;
            
            $stock->cartons_given = floor($stock->current_quantity / $ppb);
            $stock->pieces_given = $stock->current_quantity % $ppb;
            $stock->pieces_per_box = $ppb;
            $stock->price_per_piece = $price;
            
            return $stock;
        });

        return response()->json([
            'status' => true,
            'data' => $stocks
        ]);
    }

    /**
     * Store a new distributor sales report.
     */
    public function storeReport(Request $request)
    {
        $request->validate([
            'distributor_id' => 'required|exists:customers,id',
            'zone_id' => 'nullable|exists:zones,id',
            'report_date' => 'required|date',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.given_quantity' => 'required|numeric',
            'items.*.sold_quantity' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::find($request->distributor_id);

            $report = DistributorSalesReport::create([
                'distributor_id' => $request->distributor_id,
                'zone_id' => $request->zone_id ?? $customer->zone,
                'report_date' => $request->report_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                // Find stock
                $stock = DistributorStock::where([
                    'distributor_id' => $request->distributor_id,
                    'product_id' => $itemData['product_id']
                ])->first();

                $soldQuantity = $itemData['sold_quantity'];
                
                // Ensure we don't oversell (Optional validation)
                if ($stock && $soldQuantity > $stock->current_quantity) {
                    throw new \Exception("Sold quantity cannot be greater than current stock for product ID {$itemData['product_id']}");
                }

                $remaining = $itemData['given_quantity'] - $soldQuantity;

                DistributorSalesReportItem::create([
                    'report_id' => $report->id,
                    'product_id' => $itemData['product_id'],
                    'given_quantity' => $itemData['given_quantity'],
                    'sold_quantity' => $soldQuantity,
                    'remaining_quantity' => $remaining,
                ]);

                // Deduct from distributor stock since it is sold
                if ($stock && $soldQuantity > 0) {
                    $stock->current_quantity -= $soldQuantity;
                    $stock->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Distributor sales report saved successfully.',
                'data' => $report
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error saving report: ' . $e->getMessage()
            ], 500);
        }
    }
}
