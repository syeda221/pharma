{{-- AUDIT SHEET TABLE (EXACT PHARMA DISTRIBUTION / TOWN SHEET STYLE) --}}
<style>
    .audit-sheet-wrap {
        background-color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
        color: #0f172a;
    }
    .audit-table-custom {
        width: 100%;
        border-collapse: collapse;
        border: 1.5px solid #2563eb;
        font-size: 11.5px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        border-radius: 4px;
        overflow: hidden;
    }
    .audit-table-custom th, 
    .audit-table-custom td {
        border: 1px solid #cbd5e1;
        padding: 6px 7px;
        vertical-align: middle;
    }
    /* Group Header Row 1: Software Executive Blue */
    .audit-table-custom thead tr:first-child th {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: #ffffff !important;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        border: 1px solid #1d4ed8;
        font-size: 11.5px;
        padding: 7px 6px;
    }
    /* Sub-Header Row 2: Software Ice/Sky Blue */
    .audit-table-custom thead tr:nth-child(2) th {
        background-color: #eff6ff;
        color: #1e40af;
        font-weight: 700;
        text-align: center;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        padding: 5px 6px;
    }
    .audit-table-custom tbody tr {
        transition: background-color 0.15s ease;
    }
    .audit-table-custom tbody tr:hover {
        background-color: #f8fafc;
    }
    .audit-table-custom .prod-id {
        width: 75px;
        text-align: center;
        font-family: monospace;
        font-weight: 700;
        color: #2563eb;
        background-color: #f8fafc;
    }
    .audit-table-custom .prod-name {
        font-weight: 600;
        color: #0f172a;
        text-transform: uppercase;
        min-width: 180px;
    }
    .audit-table-custom .prod-pack {
        width: 60px;
        text-align: center;
        color: #64748b;
        font-weight: 500;
    }
    .audit-table-custom .col-num {
        width: 55px;
        text-align: right;
        font-family: monospace;
        color: #1e293b;
    }
    .audit-table-custom .col-val {
        width: 95px;
        text-align: right;
        font-family: monospace;
        font-weight: 700;
    }
    .audit-table-custom .col-disc {
        width: 65px;
        text-align: right;
        font-family: monospace;
        color: #2563eb;
        font-weight: 600;
    }
    /* Summary Strip 1: Clean Software Theme */
    .audit-summary-strip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 16px;
        font-weight: 700;
        font-size: 13.5px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 12px;
    }
    /* Town Totals Box 2: Premium Soft Blue Theme */
    .audit-town-box {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1.5px solid #3b82f6;
        padding: 12px 18px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #1e3a8a;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
    }
    .audit-town-title {
        font-size: 14.5px;
        font-weight: 800;
        color: #1e3a8a;
    }
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            padding: 0 !important;
        }
        .audit-sheet-wrap {
            padding: 0 !important;
        }
    }
</style>

<div class="audit-sheet-wrap p-2" id="auditSheetPrintArea">
    <!-- Header Controls (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-file-invoice text-primary"></i> Net Sales Audit Sheet &bull; {{ $sale->invoice_no }}
            </h6>
            <small class="text-muted">
                Party / Customer: <strong>{{ optional($sale->customer_relation)->customer_name ?? 'Walking Customer' }}</strong> 
                ({{ optional($sale->customer_relation)->customer_id ?? 'N/A' }})
                &bull; Date: {{ $sale->created_at ? $sale->created_at->format('d/m/Y') : date('d/m/Y') }}
                @if(optional($sale->customer_relation)->zone)
                    &bull; Zone / Town: <strong>{{ optional($sale->customer_relation)->zone }}</strong>
                @endif
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary px-3" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print Sheet
            </button>
        </div>
    </div>

    <!-- MAIN PRODUCT AUDIT TABLE -->
    <div class="table-responsive">
        <table class="audit-table-custom" id="auditTableElement">
            <thead>
                <tr>
                    <th colspan="3">PRODUCT</th>
                    <th colspan="2">SALE</th>
                    <th colspan="2">UNDELIVERED</th>
                    <th colspan="2">RETURN</th>
                    <th colspan="2">NET</th>
                    <th rowspan="2" style="width: 100px;">NET VALUE</th>
                    <th rowspan="2" style="width: 70px;">DISC %</th>
                </tr>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="min-width: 180px;">NAME</th>
                    <th style="width: 60px;">Pack</th>
                    <th style="width: 55px;">QTY</th>
                    <th style="width: 50px;">BNS</th>
                    <th style="width: 55px;">QTY</th>
                    <th style="width: 50px;">BNS</th>
                    <th style="width: 55px;">QTY</th>
                    <th style="width: 50px;">BNS</th>
                    <th style="width: 55px;">QTY</th>
                    <th style="width: 50px;">BNS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalSaleQty = 0;
                    $totalSaleBns = 0;
                    $totalUndelQty = 0;
                    $totalUndelBns = 0;
                    $totalReturnQty = 0;
                    $totalReturnBns = 0;
                    $totalNetQty = 0;
                    $totalNetBns = 0;
                    $totalNetValue = 0;
                    $totalDiscount = 0;
                @endphp

                @foreach($auditRows as $row)
                    @php
                        $sQty = (float)($row['sale_qty'] ?? 0);
                        $sBns = (float)($row['sale_bonus'] ?? 0);
                        $uQty = (float)($row['undel_qty'] ?? 0);
                        $uBns = (float)($row['undel_bonus'] ?? 0);
                        $rQty = (float)($row['return_qty'] ?? 0);
                        $rBns = (float)($row['return_bonus'] ?? 0);
                        
                        $nQty = $sQty - $uQty - $rQty;
                        $nBns = $sBns - $uBns - $rBns;
                        
                        $price = (float)($row['price'] ?? 0);
                        $lineNetVal = round($nQty * $price, 2);
                        $discPct = (float)($row['disc_pct'] ?? 0);
                        $lineDiscAmt = ($discPct > 0) ? round(($lineNetVal * $discPct) / 100, 2) : 0;

                        $totalSaleQty += $sQty;
                        $totalSaleBns += $sBns;
                        $totalUndelQty += $uQty;
                        $totalUndelBns += $uBns;
                        $totalReturnQty += $rQty;
                        $totalReturnBns += $rBns;
                        $totalNetQty += $nQty;
                        $totalNetBns += $nBns;
                        $totalNetValue += $lineNetVal;
                        $totalDiscount += $lineDiscAmt;
                    @endphp
                    <tr>
                        <td class="prod-id">{{ $row['code'] ?? 'PP000' }}</td>
                        <td class="prod-name">{{ $row['name'] ?? 'Product' }}</td>
                        <td class="prod-pack">{{ $row['pack'] ?? '-' }}</td>
                        <td class="col-num">{{ $sQty > 0 ? (int)$sQty : 0 }}</td>
                        <td class="col-num">{{ $sBns > 0 ? (int)$sBns : 0 }}</td>
                        <td class="col-num">{{ $uQty > 0 ? (int)$uQty : 0 }}</td>
                        <td class="col-num">{{ $uBns > 0 ? (int)$uBns : 0 }}</td>
                        <td class="col-num text-danger">{{ $rQty > 0 ? (int)$rQty : 0 }}</td>
                        <td class="col-num text-danger">{{ $rBns > 0 ? (int)$rBns : 0 }}</td>
                        <td class="col-num fw-bold {{ $nQty < 0 ? 'text-danger' : ($nQty > 0 ? 'text-dark' : 'text-muted') }}">
                            {{ (int)$nQty }}
                        </td>
                        <td class="col-num fw-bold {{ $nBns < 0 ? 'text-danger' : ($nBns > 0 ? 'text-dark' : 'text-muted') }}">
                            {{ (int)$nBns }}
                        </td>
                        <td class="col-val {{ $lineNetVal < 0 ? 'text-danger' : 'text-dark' }}">
                            {{ number_format($lineNetVal, 2) }}
                        </td>
                        <td class="col-disc text-muted fw-semibold">{{ number_format($discPct, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @php
        $finalPartyDiscount = isset($partyDiscountAmt) && $partyDiscountAmt != 0 ? (float)$partyDiscountAmt : $totalDiscount;
        $partyNetSale = $totalNetValue - $finalPartyDiscount;
    @endphp

    <!-- STRIP 1: GROSS SALE & PARTY DISCOUNT -->
    <div class="audit-summary-strip">
        <div>
            GROSS Sale : <span class="ms-1 font-monospace">{{ number_format($totalNetValue, 2) }}</span>
        </div>
        <div>
            Party Discount Amount : <span class="ms-1 font-monospace text-primary">{{ number_format($finalPartyDiscount, 2) }}</span>
        </div>
        <div>
            Party Net Sale : <span class="ms-1 font-monospace text-success">{{ number_format($partyNetSale, 2) }}</span>
        </div>
    </div>

    <!-- STRIP 2: TOWN TOTALS BOX (MATCHING SCREENSHOT 100%) -->
    <div class="audit-town-box">
        <div class="row align-items-center g-2">
            <!-- Left: Town Title -->
            <div class="col-md-3">
                <div class="audit-town-title">
                    Town Totals :
                </div>
                <small class="text-muted d-block" style="font-size: 11px;">
                    {{ optional($sale->customer_relation)->zone ? 'Zone: ' . optional($sale->customer_relation)->zone : 'Town / Customer Summary' }}
                </small>
            </div>

            <!-- Middle: Sale, Undelivered, Return -->
            <div class="col-md-5">
                <div class="d-flex justify-content-between mb-1">
                    <span>Total Sale :</span>
                    <span class="font-monospace"><strong>{{ (int)$totalSaleQty }}</strong> &nbsp;&nbsp;&nbsp; <strong>{{ (int)$totalSaleBns }}</strong></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Total Undel. :</span>
                    <span class="font-monospace"><strong>{{ (int)$totalUndelQty }}</strong> &nbsp;&nbsp;&nbsp; <strong>{{ (int)$totalUndelBns }}</strong></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Return :</span>
                    <span class="font-monospace text-danger"><strong>{{ (int)$totalReturnQty }}</strong> &nbsp;&nbsp;&nbsp; <strong>{{ (int)$totalReturnBns }}</strong></span>
                </div>
            </div>

            <!-- Right: Net Sale, Net Value, TTL Disc -->
            <div class="col-md-4 border-start ps-3">
                <div class="d-flex justify-content-between mb-1">
                    <span>Net Sale :</span>
                    <span class="font-monospace"><strong>{{ (int)$totalNetQty }}</strong> &nbsp;&nbsp;&nbsp; <strong>{{ (int)$totalNetBns }}</strong></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Net Value :</span>
                    <span class="font-monospace fs-6 text-primary"><strong>{{ number_format($totalNetValue, 2) }}</strong></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>TTL DISC :</span>
                    <span class="font-monospace"><strong>{{ number_format($finalPartyDiscount, 2) }}</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>
