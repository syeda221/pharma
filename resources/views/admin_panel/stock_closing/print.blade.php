<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Audit Sheet - {{ $closing->closing_code }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #fff;
            color: #000;
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }
        .header-title {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .meta-box {
            border: 1px solid #000;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .table-print th, .table-print td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        .table-print th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            text-align: center;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .sig-box {
            width: 28%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 6px;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-3 fw-bold">
            <i class="fas fa-print me-1"></i> Print Audit Sheet
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">
            Close
        </button>
    </div>

    {{-- HEADER --}}
    <div class="header-title">
        <h4 class="mb-0 fw-bold text-uppercase">Monthly Physical Stock Audit & Closing Sheet</h4>
        <div class="small text-muted">Internal Inventory Reconciliation & Variance Audit Report</div>
    </div>

    {{-- META INFO BOX --}}
    <div class="meta-box">
        <div class="row">
            <div class="col-4">
                <div><strong>Audit Code:</strong> {{ $closing->closing_code }}</div>
                <div><strong>Closing Month:</strong> {{ \Carbon\Carbon::parse($closing->closing_month . '-01')->format('F Y') }}</div>
            </div>
            <div class="col-4 text-center">
                <div><strong>Warehouse:</strong> {{ $closing->warehouse->warehouse_name ?? 'Main Warehouse' }}</div>
                <div><strong>Audit Date:</strong> {{ \Carbon\Carbon::parse($closing->closing_date)->format('d-M-Y') }}</div>
            </div>
            <div class="col-4 text-end">
                <div><strong>Audited By:</strong> {{ $closing->creator->name ?? 'Admin' }}</div>
                <div><strong>Action Mode:</strong> {{ strtoupper($closing->adjustment_mode) }}</div>
            </div>
        </div>
    </div>

    {{-- SUMMARY STATS --}}
    <table class="table-print text-center mb-2">
        <thead>
            <tr>
                <th>TOTAL PRODUCTS</th>
                <th>MATCHED ITEMS</th>
                <th>DISCREPANCIES</th>
                <th>SYSTEM STOCK (PCS)</th>
                <th>PHYSICAL COUNT (PCS)</th>
                <th>NET VARIANCE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold">{{ $closing->total_items_count }}</td>
                <td class="fw-bold text-success">{{ $closing->matched_count }}</td>
                <td class="fw-bold {{ $closing->discrepant_count > 0 ? 'text-danger' : '' }}">{{ $closing->discrepant_count }}</td>
                <td>{{ number_format($closing->total_system_pieces, 0) }}</td>
                <td class="fw-bold">{{ number_format($closing->total_physical_pieces, 0) }}</td>
                <td class="fw-bold">
                    {{ $closing->net_variance_pieces > 0 ? '+' : '' }}{{ number_format($closing->net_variance_pieces, 0) }} Pcs
                </td>
            </tr>
        </tbody>
    </table>

    {{-- REASON & REMARKS --}}
    @if($closing->reason_type || $closing->remarks)
        <div class="p-2 border mb-3" style="background:#f9f9f9; border: 1px solid #000 !important;">
            <strong>Variance Reason:</strong> {{ $closing->reason_type ?? 'Audit Correction' }} &nbsp;|&nbsp; 
            <strong>Remarks / Notes:</strong> {{ $closing->remarks ?: 'None' }}
        </div>
    @endif

    {{-- ITEMS TABLE --}}
    <table class="table-print">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 75px;">CODE</th>
                <th>PRODUCT NAME</th>
                <th style="width: 45px;">PPB</th>
                <th style="width: 120px;">SYSTEM STOCK</th>
                <th style="width: 120px;">PHYSICAL COUNT</th>
                <th style="width: 110px;">VARIANCE (DIFF)</th>
                <th style="width: 75px;">STATUS</th>
                <th style="width: 65px;">ADJUSTED</th>
            </tr>
        </thead>
        <tbody>
            @foreach($closing->items as $idx => $it)
                @php
                    $isFarq = ($it->status !== 'matched');
                @endphp
                <tr style="{{ $isFarq ? 'background-color: #fff2f2;' : '' }}">
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center font-monospace">{{ $it->product->item_code ?? '-' }}</td>
                    <td>
                        <strong>{{ $it->variant_name ?: ($it->product->item_name ?? 'Product #' . $it->product_id) }}</strong>
                        <span class="text-muted small">({{ $it->product->category_relation->name ?? 'General' }})</span>
                    </td>
                    <td class="text-center">{{ $it->pieces_per_box }}</td>
                    <td class="text-center">
                        {{ (int)$it->system_cartons }} Ctn {{ (int)$it->system_pieces > 0 ? '+ ' . (int)$it->system_pieces . ' Pcs' : '' }}
                        <div class="small text-muted">({{ (int)$it->system_total_pieces }} pcs)</div>
                    </td>
                    <td class="text-center fw-bold">
                        {{ (int)$it->physical_cartons }} Ctn {{ (int)$it->physical_pieces > 0 ? '+ ' . (int)$it->physical_pieces . ' Pcs' : '' }}
                        <div class="small text-muted">({{ (int)$it->physical_total_pieces }} pcs)</div>
                    </td>
                    <td class="text-center fw-bold {{ $it->variance_pieces < 0 ? 'text-danger' : ($it->variance_pieces > 0 ? 'text-primary' : 'text-success') }}">
                        {{ $it->variance_display }}
                    </td>
                    <td class="text-center text-uppercase" style="font-size: 10px;">
                        <strong>{{ $it->status }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $it->is_adjusted ? 'YES' : 'NO' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- SIGNATURE SECTION --}}
    <div class="signature-section">
        <div class="sig-box">
            <div>Prepared By (Storekeeper)</div>
            <div class="small text-muted mt-4">Signature & Date</div>
        </div>
        <div class="sig-box">
            <div>Verified By (Stock Auditor)</div>
            <div class="small text-muted mt-4">Signature & Date</div>
        </div>
        <div class="sig-box">
            <div>Approved By (Managing Director)</div>
            <div class="small text-muted mt-4">Signature & Date</div>
        </div>
    </div>

</body>
</html>
