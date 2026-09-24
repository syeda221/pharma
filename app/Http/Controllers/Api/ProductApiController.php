<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        // Start querying only products that are visible on the website
        $query = Product::with(['category_relation', 'webImages'])
            ->withSum('warehouseStocks as total_stock', 'total_pieces')
            ->where('is_web_visible', 1);

        // Auto-hide out of stock products if auto_hide_out_of_stock is enabled
        $query->where(function ($q) {
            $q->where('auto_hide_out_of_stock', 0)
              ->orWhereExists(function ($subQuery) {
                  $subQuery->select(DB::raw(1))
                      ->from('warehouse_stocks')
                      ->whereColumn('warehouse_stocks.product_id', 'products.id')
                      ->groupBy('warehouse_stocks.product_id')
                      ->havingRaw('SUM(total_pieces) > 0');
              });
        });

        // Filter by category
        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by promotional tag (e.g. Featured, Flash Sale)
        if ($request->has('promo_tag') && $request->promo_tag != '') {
            $query->where('promo_tag', $request->promo_tag);
        }

        // Filter by color
        if ($request->has('color') && $request->color != '') {
            $color = $request->color;
            $query->where(function($q) use ($color) {
                $q->where('color', 'like', '%"color":"' . $color . '"%')
                  ->orWhere('color', 'like', '%"color": "' . $color . '"%');
            });
        }

        // Filter by size
        if ($request->has('size') && $request->size != '') {
            $size = $request->size;
            $query->where(function($q) use ($size) {
                $q->where('color', 'like', '%"size":"' . $size . '"%')
                  ->orWhere('color', 'like', '%"size": "' . $size . '"%');
            });
        }
        
        // Homepage only filter
        if ($request->has('show_on_homepage') && $request->show_on_homepage == 1) {
            $query->where('show_on_homepage', 1);
        }

        // Search by keyword
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        // Fetch paginated results
        $products = $query->paginate(12);

        // Transform results slightly to ensure standard price if web_sale_price is missing
        $products->getCollection()->transform(function ($product) {
            $product->final_price = $product->web_sale_price ?: $product->sale_price_per_piece;
            $product->description = $product->meta_description ?: "Designed as part of our premium modern luxury apparel collection, this piece stands out with premium stitching and elegant cuts.";
            $product->total_stock = (int) ($product->total_stock ?? 0);
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function show($id)
    {
        $cacheKey = 'api_product_detail_' . $id;

        $productData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, function () use ($id) {
            $product = Product::with(['category_relation', 'webImages'])
                ->withSum('warehouseStocks as total_stock', 'total_pieces')
                ->where('is_web_visible', 1)
                ->findOrFail($id);

            $product->final_price = $product->web_sale_price ?: $product->sale_price_per_piece;
            $product->description = $product->meta_description ?: "Designed as part of our premium modern luxury apparel collection, this piece stands out with premium stitching and elegant cuts.";
            $product->total_stock = (int) ($product->total_stock ?? 0);

            // Fetch actual variant stocks based on ERP ledger formula
            if ($product->color) {
                try {
                    $calculated_variants = $this->calculateVariantStocks($product);
                    if (!empty($calculated_variants)) {
                        $product->color = json_encode($calculated_variants);
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }
            }

            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $productData
        ]);
    }

    /**
     * Compute current stock balances dynamically using the ERP stock report ledger formula
     */
    private function calculateVariantStocks($product)
    {
        $variants = json_decode($product->color, true);
        if (!is_array($variants) || count($variants) === 0) {
            return [];
        }

        // Fetch sales
        $salesList = DB::table('sale_items')->where('product_id', $product->id)->select('total_pieces', 'color')->get();

        // Fetch web sales
        $webSalesList = DB::table('ecommerce_order_items as eoi')
            ->join('ecommerce_orders as eo', 'eo.id', '=', 'eoi.ecommerce_order_id')
            ->where('eoi.product_id', $product->id)
            ->where('eo.is_stock_deducted', 1)
            ->select('eoi.quantity as total_pieces', 'eoi.color', 'eoi.size')
            ->get();

        $salesListArray = $salesList->toArray();
        foreach ($webSalesList as $wItem) {
            $salesListArray[] = (object) [
                'total_pieces' => $wItem->total_pieces,
                'color' => json_encode([
                    'color' => $wItem->color ?: '-',
                    'size' => $wItem->size ?: '-'
                ])
            ];
        }
        $salesList = collect($salesListArray);

        // Fetch returns
        $returnsList = DB::table('sale_return_items as sri')
            ->join('sale_returns as sr', 'sr.id', '=', 'sri.sale_return_id')
            ->where('sri.product_id', $product->id)
            ->select('sri.qty', 'sri.color', 'sr.sale_id')
            ->get();

        $saleIds = $returnsList->pluck('sale_id')->unique()->toArray();
        $saleItemsMap = [];
        if (!empty($saleIds)) {
            $siList = DB::table('sale_items')
                ->whereIn('sale_id', $saleIds)
                ->where('product_id', $product->id)
                ->select('sale_id', 'color')
                ->get();
            foreach ($siList as $si) {
                $saleItemsMap[$si->sale_id][] = $si->color;
            }
        }

        // Fetch purchases
        $purchasesList = DB::table('purchase_items as pi')
            ->join('purchases as pur', 'pur.id', '=', 'pi.purchase_id')
            ->where('pi.product_id', $product->id)
            ->whereIn('pur.status_purchase', ['approved', 'Returned', 'Partial'])
            ->select('pi.qty as total_pieces', 'pi.color')
            ->get();

        // Fetch purchase returns
        $purchaseReturnsList = DB::table('purchase_return_items as pri')
            ->where('pri.product_id', $product->id)
            ->select('pri.qty', 'pri.color')
            ->get();

        // Fetch stock adjustments
        $adjList = DB::table('stock_movements')
            ->where('product_id', $product->id)
            ->where('type', 'adjustment')
            ->select('qty', 'note')
            ->get();

        foreach ($variants as $idx => $v) {
            $initial = (float) ($v['stock'] ?? 0);

            // Purchased
            $purchased = 0;
            foreach ($purchasesList as $pItem) {
                if ($this->matchSaleItemToVariant($pItem, $v)) {
                    $purchased += (float) $pItem->total_pieces;
                }
            }

            // Purchase Returned
            $pReturned = 0;
            foreach ($purchaseReturnsList as $prItem) {
                if ($this->matchSaleItemToVariant($prItem, $v)) {
                    $pReturned += (float) $prItem->qty;
                }
            }

            // Sold
            $sold = 0;
            foreach ($salesList as $sItem) {
                if ($this->matchSaleItemToVariant($sItem, $v)) {
                    $sold += (float) $sItem->total_pieces;
                }
            }

            // Returned
            $returnedQty = 0;
            foreach ($returnsList as $rItem) {
                $rColor = $rItem->color;
                if (empty($rColor)) {
                    $saleColors = $saleItemsMap[$rItem->sale_id] ?? [];
                    $rColor = !empty($saleColors) ? $saleColors[0] : '';
                }
                $rItemCopy = (object)['qty' => $rItem->qty, 'color' => $rColor];
                if ($this->matchSaleItemToVariant($rItemCopy, $v)) {
                    $returnedQty += (float) $rItem->qty;
                }
            }

            // Adjustments
            $adjustments = 0;
            foreach ($adjList as $adjItem) {
                if ($this->matchAdjustmentToVariant($adjItem, $v)) {
                    $adjustments += (float) $adjItem->qty;
                }
            }

            $variants[$idx]['stock'] = (int) max(0, $initial + $purchased - $sold + $returnedQty - $pReturned + $adjustments);
        }

        return $variants;
    }

    private function matchSaleItemToVariant($saleItem, $variant, $totalVariantsCount = 1)
    {
        $itemColor = $saleItem->color ?? null;
        if (empty($itemColor) || $itemColor === '-' || $itemColor === 'null') {
            $vColor = strtolower(trim($variant['color'] ?? '-'));
            $vSize = strtolower(trim($variant['size'] ?? '-'));
            if ($totalVariantsCount <= 1 || (($vColor === '' || $vColor === '-') && ($vSize === '' || $vSize === '-'))) {
                return true;
            }
            return false;
        }

        $itemVariant = [];
        $b64Decoded = base64_decode($itemColor, true);
        if ($b64Decoded !== false) {
            $json = json_decode($b64Decoded, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
        }
        if (empty($itemVariant)) {
            $json = json_decode($itemColor, true);
            if (is_array($json)) {
                $itemVariant = $json;
            }
        }

        if (empty($itemVariant)) {
            $vColor = strtolower(trim($variant['color'] ?? '-'));
            $vSize = strtolower(trim($variant['size'] ?? '-'));
            if ($totalVariantsCount <= 1 && ($vColor === '-' || $vColor === '') && ($vSize === '-' || $vSize === '')) {
                return true;
            }
            return strtolower(trim($itemColor)) === $vColor;
        }

        $vColor = strtolower(trim($variant['color'] ?? '-'));
        $vSize = strtolower(trim($variant['size'] ?? '-'));

        $itemVColor = strtolower(trim($itemVariant['color'] ?? ($itemVariant['color_val'] ?? '-')));
        $itemVSize = strtolower(trim($itemVariant['size'] ?? ($itemVariant['size_val'] ?? '-')));

        if ($vColor === '') $vColor = '-';
        if ($vSize === '') $vSize = '-';
        if ($itemVColor === '') $itemVColor = '-';
        if ($itemVSize === '') $itemVSize = '-';

        return $vColor === $itemVColor && $vSize === $itemVSize;
    }

    private function matchAdjustmentToVariant($adjItem, $variant)
    {
        $note = strtolower($adjItem->note ?? '');
        if (empty($note)) {
            return false;
        }

        $vSize  = strtolower(trim($variant['size'] ?? '-'));
        $vColor = strtolower(trim($variant['color'] ?? '-'));

        $sizeMatch = true;
        if ($vSize !== '-' && !empty($vSize)) {
            $pattern = '/\b' . preg_quote($vSize, '/') . '\b/i';
            $sizeMatch = preg_match($pattern, $note) === 1;
        }

        $colorMatch = true;
        if ($vColor !== '-' && !empty($vColor)) {
            $pattern = '/\b' . preg_quote($vColor, '/') . '\b/i';
            $colorMatch = preg_match($pattern, $note) === 1;
        }

        return $sizeMatch && $colorMatch;
    }
}
