<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $sale->invoice_no }}</title>

    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --border-color: #bdc3c7;
            --text-color: #2c3e50;
        }

        body {
            background-color: #f8f9fa;
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Layout */
        .invoice-container {
            max-width: 210mm;
            margin: 10px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            min-height: 297mm;
            position: relative;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 2px;
        }

        .invoice-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: var(--accent-color);
            margin: 15px 0 10px 0;
            letter-spacing: 2px;
        }

        .info-box {
            border: 1px solid var(--border-color);
            padding: 8px;
            height: 100%;
            border-radius: 4px;
            background-color: #fff;
        }

        .info-box-header {
            font-weight: bold;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 4px;
            padding-bottom: 2px;
            color: var(--primary-color);
            font-size: 11px;
            text-transform: uppercase;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 70px;
            display: inline-block;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .invoice-table th {
            background-color: var(--primary-color);
            color: #fff;
            text-transform: uppercase;
            font-size: 11px;
            padding: 6px 4px;
            border: 1px solid var(--primary-color);
        }

        .invoice-table td {
            border: 1px solid var(--border-color);
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 12px;
        }

        .invoice-table tbody tr:nth-of-type(even) {
            background-color: #f8f9fa;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-section {
            margin-top: 20px;
            border-top: 2px solid var(--primary-color);
            padding-top: 10px;
        }

        .terms-box {
            font-size: 11px;
            color: #666;
        }

        .terms-box ul {
            padding-left: 20px;
            margin-bottom: 0;
        }

        .terms-box li {
            margin-bottom: 2px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 4px 8px;
            border-bottom: 1px solid #eee;
        }

        .totals-table .total-row td {
            border-top: 2px solid var(--primary-color);
            font-weight: bold;
            font-size: 14px;
            color: var(--primary-color);
        }

        .signature-area {
            margin-top: 40px;
            border-top: 1px solid #000;
            width: 180px;
            text-align: center;
            padding-top: 5px;
        }

        .print-btn-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        /* Thermal Receipt */
        .receipt-container {
            display: none;
        }

        @media print {

            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
            }

            .print-btn-container,
            .no-print,
            .screen-only {
                display: none !important;
            }

            .receipt-container {
                display: block !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                padding: 3mm 2mm 5mm 2mm !important;
                width: 76mm !important;
                max-width: 76mm !important;
                background: #fff !important;
                color: #000 !important;
                font-size: 11px !important;
                line-height: 1.3 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
            }

            .receipt-container h1,
            .receipt-container h2,
            .receipt-container h3,
            .receipt-container p {
                margin: 0;
                padding: 0;
            }

            .receipt-container .company-name {
                font-size: 18px !important;
                font-weight: 700 !important;
                text-align: center !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                margin-bottom: 2px !important;
                color: #000 !important;
            }

            .receipt-container .company-info {
                font-size: 11px !important;
                text-align: center !important;
                color: #000 !important;
                font-weight: 400 !important;
                line-height: 1.35 !important;
                margin-bottom: 4px !important;
            }

            .receipt-container .policy-banner {
                background-color: #000 !important;
                color: #fff !important;
                text-align: center !important;
                font-size: 10px !important;
                font-weight: bold !important;
                padding: 3px 5px !important;
                margin: 4px 0 !important;
                border-radius: 3px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-container .divider {
                border-top: 1px dashed #000 !important;
                margin: 4px 0 !important;
            }

            .receipt-container .meta-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 2px !important;
                margin-bottom: 2px !important;
                color: #000 !important;
            }

            .receipt-container .meta-row {
                display: flex !important;
                justify-content: space-between !important;
                font-size: 10.5px !important;
                font-weight: 400 !important;
            }

            .receipt-container .items-table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 10.5px !important;
                color: #000 !important;
                margin: 3px 0 !important;
            }

            .receipt-container .items-table th {
                border-top: 1px dashed #000 !important;
                border-bottom: 1px dashed #000 !important;
                padding: 3px 1px !important;
                text-align: left !important;
                font-weight: 700 !important;
                font-size: 10px !important;
                text-transform: uppercase !important;
            }

            .receipt-container .items-table td {
                padding: 3px 1px !important;
                vertical-align: top !important;
                border-bottom: 1px dashed #ccc !important;
                font-size: 10.5px !important;
            }

            .receipt-container .item-name {
                font-weight: 600 !important;
                font-size: 10.5px !important;
                color: #000 !important;
                display: block !important;
            }

            .receipt-container .item-variant {
                font-size: 9.5px !important;
                color: #222 !important;
                font-weight: 400 !important;
                margin-top: 1px !important;
                display: block !important;
            }

            .receipt-container .totals-section {
                font-size: 10.5px !important;
                margin-top: 4px !important;
                color: #000 !important;
            }

            .receipt-container .tot-row {
                display: flex !important;
                justify-content: space-between !important;
                padding: 1.5px 0 !important;
            }

            .receipt-container .tot-row.grand-total {
                font-size: 12px !important;
                font-weight: 800 !important;
                margin: 3px 0 !important;
                padding: 4px 0 !important;
                border-top: 1.5px solid #000 !important;
                border-bottom: 1.5px solid #000 !important;
                color: #000 !important;
            }

            .receipt-container .balance-section {
                font-size: 10.5px !important;
                margin-top: 3px !important;
                border-top: 1px dashed #000 !important;
                padding-top: 3px !important;
            }

            .receipt-container .balance-section .tot-row.closing-bal {
                font-weight: 700 !important;
                background-color: #f8f9fa !important;
                padding: 3px 2px !important;
                border: 1px solid #000 !important;
                margin-top: 2px !important;
            }

            .receipt-container .footer {
                text-align: center !important;
                margin-top: 10px !important;
                color: #000 !important;
            }

            .receipt-container .footer p {
                font-size: 10px !important;
                font-weight: 600 !important;
                margin-bottom: 2px !important;
            }

            .receipt-container .footer .soft-credit {
                font-size: 8.5px !important;
                color: #444 !important;
                margin-top: 2px !important;
                font-weight: 400 !important;
            }
        }
    </style>
</head>

<body>

    <!-- Print / Action Buttons -->
    <div class="print-btn-container no-print">

        <button onclick="window.print()" class="btn btn-primary btn-sm shadow fw-bold">

            <svg xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                class="bi bi-printer-fill me-2"
                viewBox="0 0 16 16">

                <path d="M0 9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9zm4-6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2H4V3z" />

                <path d="M2.5 14.5A1.5 1.5 0 0 1 1 13V9a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v4a1.5 1.5 0 0 1-1.5 1.5h-13z" />

            </svg>

            Print

        </button>

        <a href="javascript:void(0)"
            onclick="handleGoBack()"
            class="btn btn-secondary btn-sm shadow ms-2 fw-bold">
            Back
        </a>

    </div>


    <!-- ========================================== -->
    <!-- SCREEN VIEW -->
    <!-- ========================================== -->

    <div class="invoice-container screen-only">

        <!-- Company Header -->

        @if(!($isEstimate ?? false))

            <div class="company-info">

                <div class="company-name">
                    {{ \App\Models\Setting::get('company_name', 'prowave technogies') }}
                </div>

                <div style="font-size: 12px;">
                    {{ \App\Models\Setting::get('company_address', 'Hyderabad') }}
                </div>

                <p>
                    {{ \App\Models\Setting::get('company_phone', '0327-9226901') }}
                </p>

            </div>

        @endif


        <div class="invoice-title">
            {{ ($isEstimate ?? false) ? 'Estimate' : 'Sales Invoice' }}
        </div>


        <!-- Info Grid -->

        @if(!($isEstimate ?? false))

            <div class="row g-2 mb-2">

                <!-- Customer -->

                <div class="col-6">

                    <div class="info-box">

                        <div class="info-box-header">
                            Customer
                        </div>

                        @if($sale->customer_relation?->customer_id)

                            <div style="font-size: 11px; color: #555;">
                                Code:
                                <strong>
                                    {{ $sale->customer_relation->customer_id }}
                                </strong>
                            </div>

                        @endif

                        <div>
                            <span class="info-label">
                                Name:
                            </span>

                            <strong>
                                {{ $sale->walkin_name ?? ($sale->customer_relation->customer_name ?? 'Walking Customer') }}
                            </strong>
                        </div>

                        <div>
                            <span class="info-label">
                                Address:
                            </span>

                            <span style="font-size:11px;">
                                {{ $sale->customer_relation->address ?? '—' }}
                            </span>
                        </div>

                        <div>
                            <span class="info-label">
                                Mob:
                            </span>

                            <span style="font-size:11px;">
                                {{ $sale->customer_relation->mobile ?? '—' }}
                            </span>
                        </div>

                    </div>

                </div>


                <!-- Reference -->

                <div class="col-6">

                    <div class="info-box">

                        <div class="info-box-header">
                            Reference
                        </div>

                        <div>
                            <span class="info-label">
                                Inv #:
                            </span>

                            <strong>
                                {{ $sale->invoice_no }}
                            </strong>
                        </div>

                        <div>
                            <span class="info-label">
                                Date:
                            </span>

                            {{ $sale->created_at
                                ? $sale->created_at->format('d/m/Y')
                                : date('d/m/Y') }}
                        </div>

                        @if($sale->reference)

                            <div style="margin-top:4px; padding-top:4px; border-top:1px dashed #ddd;">

                                <span class="info-label">
                                    Remarks:
                                </span>

                                <span style="font-size:11px; color:#333;">
                                    {{ $sale->reference }}
                                </span>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        @else

            <div class="row g-2 mb-2">

                <div class="col-12 text-end">

                    <div class="info-box">

                        <div>

                            <span class="info-label">
                                Date:
                            </span>

                            {{ $sale->created_at
                                ? $sale->created_at->format('d/m/Y')
                                : date('d/m/Y') }}

                        </div>

                    </div>

                </div>

            </div>

        @endif


        <!-- Return Note -->

        @if ($sale->return_note)

            <div class="row mb-2">

                <div class="col-12">

                    <div class="info-box"
                        style="min-height: auto; padding: 4px 8px; background-color: #f1f5f9; font-style: italic;">

                        <strong>
                            Note:
                        </strong>

                        {{ $sale->return_note }}

                    </div>

                </div>

            </div>

        @endif


        <!-- ========================================== -->
        <!-- MAIN INVOICE TABLE -->
        <!-- ========================================== -->

        <table class="invoice-table">

            <thead>

                <tr>

                    <th class="text-start" style="width: 38%">
                        Description
                    </th>

                    <th class="text-center" style="width: 14%">
                        Shipped
                    </th>

                    <th class="text-center" style="width: 10%">
                        UOM
                    </th>

                    <th class="text-end" style="width: 10%">
                        Price
                    </th>

                    <th class="text-end" style="width: 10%">
                        Disc
                    </th>

                    <th class="text-end" style="width: 13%">
                        Net Amount
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($saleItems as $item)

                    @php

                        $height = $item['height'] ?? 0;
                        $width = $item['width'] ?? 0;

                        $m2PerPiece =
                            $height > 0 && $width > 0
                                ? ($height * $width) / 10000
                                : 0;

                        $piecesPerBox =
                            (int)($item['pieces_per_box'] ?? 1);

                        if ($piecesPerBox <= 0) {
                            $piecesPerBox = 1;
                        }

                        $m2PerBox =
                            $m2PerPiece * $piecesPerBox;

                        $totalPieces =
                            (int)$item['total_pieces'];

                        $boxes =
                            floor($totalPieces / $piecesPerBox);

                        $loosePieces =
                            $totalPieces % $piecesPerBox;

                        $totalM2Line =
                            $m2PerPiece * $totalPieces;

                        $sizeMode =
                            $item['size_mode'] ?? 'by_size';

                    @endphp


                    <tr>

                        <!-- DESCRIPTION -->

                        <td class="text-start">

                            @php
                                $vName = $item['variant_name'] ?? '';
                                $vSize = (!empty($item['size_val']) && $item['size_val'] !== '-') ? $item['size_val'] : '';
                                $vColor = (!empty($item['color_val']) && $item['color_val'] !== '-') ? $item['color_val'] : '';
                                
                                $vExtra = [];
                                if ($vSize) $vExtra[] = $vSize;
                                if ($vColor) $vExtra[] = $vColor;
                                $vExtraStr = count($vExtra) > 0 ? ' (' . implode(', ', $vExtra) . ')' : '';

                                $productTitle = $item['item_name'];
                                if ($vName && strtolower(trim($vName)) !== strtolower(trim($productTitle))) {
                                    $productTitle .= ' — ' . $vName;
                                }
                                $productTitle .= $vExtraStr;
                            @endphp

                            <div style="font-weight: bold; font-size: 12px; margin-bottom: 2px;">

                                {{ $productTitle }}

                            </div>


                            <!--
                                IMPORTANT:
                                Raw $item['color'] data has intentionally
                                been removed from this invoice.

                                This prevents values such as:

                                1000 180 180 1000 90 0 24 1 Carton 41.16

                                from appearing here.
                            -->


                            <div style="font-size: 11px; color: #555; line-height: 1.2;">

                                @if ($sizeMode == 'by_size')

                                    <span class="d-inline-block ms-1">

                                        @if ($height > 0 && $width > 0)

                                            Dims:
                                            {{ number_format($width, 0) }}x{{ number_format($height, 0) }}

                                        @endif

                                    </span>

                                @endif


                                @if ($piecesPerBox > 1)

                                    <span class="d-inline-block ms-1">

                                        Pack:
                                        {{ $piecesPerBox }} pcs

                                    </span>

                                @endif

                            </div>

                        </td>


                        <!-- SHIPPED -->

                        <td class="text-center" style="vertical-align: middle;">

                            @php

                                $variantUnit =
                                    strtolower(
                                        $item['variant_unit']
                                        ??
                                        ''
                                    );

                                $weightGrams =
                                    (float)(
                                        $item['weight_per_piece']
                                        ?? 0
                                    );

                            @endphp


                            @if (
                                $variantUnit === 'pcs'
                                ||
                                $variantUnit === 'piece'
                                ||
                                $variantUnit === 'pieces'
                            )

                                <div style="font-weight: bold; color: #2c3e50;">

                                    {{ $totalPieces }} Pcs

                                    @if ($weightGrams > 0)

                                        <small class="d-block text-muted"
                                            style="font-size: 10px;">

                                            (
                                            {{
                                                $weightGrams == (int)$weightGrams
                                                    ? (int)$weightGrams
                                                    : $weightGrams
                                            }}g
                                            )

                                        </small>

                                    @endif

                                </div>


                            @elseif (
                                in_array(
                                    $sizeMode,
                                    [
                                        'by_kg',
                                        'by_gm',
                                        'by_feet',
                                        'by_meter'
                                    ]
                                )
                            )

                                @php

                                    $uomLabel = match($sizeMode) {

                                        'by_kg' =>
                                            'Kg',

                                        'by_gm' =>
                                            'Gm',

                                        'by_feet' =>
                                            'Ft',

                                        'by_meter' =>
                                            'Meter',

                                        default =>
                                            '',

                                    };

                                    $qtyVal =
                                        (float)(
                                            $item['qty_box']
                                            ??
                                            $item['qty']
                                            ??
                                            $totalPieces
                                        );

                                    $displayQty =
                                        ($qtyVal == (int)$qtyVal)
                                            ? (int)$qtyVal
                                            : number_format($qtyVal, 3);

                                @endphp


                                <div style="font-weight: bold; color: #2c3e50;">

                                    {{ $displayQty }}
                                    {{ $uomLabel }}

                                </div>


                            @else

                                <div style="font-weight: bold; color: #2c3e50;">

                                    @if ($sizeMode == 'by_pieces')

                                        {{ $totalPieces }} Pcs

                                    @else

                                        @if ($boxes > 0 && $loosePieces > 0)

                                            {{ $boxes }}

                                            {{ $sizeMode == 'by_cartons'
                                                ? 'Carton'
                                                : 'Box' }}

                                            +

                                            {{ $loosePieces }}
                                            Pc

                                        @elseif ($boxes > 0)

                                            {{ $boxes }}

                                            {{ $sizeMode == 'by_cartons'
                                                ? 'Carton'
                                                : 'Box' }}

                                        @else

                                            {{ $loosePieces }} Pcs

                                        @endif

                                    @endif

                                </div>


                                <small class="text-muted"
                                    style="font-size: 10px;">

                                    ({{ $totalPieces }} pcs)

                                </small>

                            @endif

                        </td>


                        <!-- UOM -->

                        <td class="text-center"
                            style="vertical-align: middle;">

                            @if (!empty($item['variant_unit']))

                                <span class="fw-bold">
                                    {{ ucfirst($item['variant_unit']) }}
                                </span>

                            @elseif ($sizeMode == 'by_pieces')

                                <span class="fw-bold">
                                    Pieces
                                </span>

                            @elseif ($sizeMode == 'by_cartons')

                                <span class="fw-bold">
                                    Cartons
                                </span>

                            @elseif ($sizeMode == 'by_size')

                                <span class="fw-bold">
                                    {{ number_format($totalM2Line, 4) }}
                                </span>

                                m²

                            @else

                                <span class="fw-bold">
                                    Pieces
                                </span>

                            @endif

                        </td>


                        <!-- PRICE -->

                        <td class="text-end"
                            style="vertical-align: middle;">

                            {{ number_format($item['price'], 2) }}

                        </td>


                        <!-- DISCOUNT -->

                        <td class="text-end"
                            style="vertical-align: middle;">

                            @php

                                $discAmt =
                                    (float)(
                                        $item['discount_amount']
                                        ?? 0
                                    );

                                $discPct =
                                    (float)(
                                        $item['discount_percent']
                                        ?? 0
                                    );

                            @endphp


                            @if ($discAmt > 0)

                                <span class="text-danger">

                                    {{ number_format($discAmt, 2) }}

                                </span>

                                @if ($discPct > 0)

                                    <br>

                                    <small class="text-muted">

                                        (
                                        {{ number_format($discPct, 1) }}%
                                        )

                                    </small>

                                @else

                                    <br>

                                    <small class="text-muted">
                                        PKR
                                    </small>

                                @endif

                            @else

                                <span class="text-muted">
                                    —
                                </span>

                            @endif

                        </td>


                        <!-- NET AMOUNT -->

                        <td class="text-end fw-bold"
                            style="vertical-align: middle;">

                            {{ number_format($item['total'], 2) }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

        @php
            $totalCartonsCount = 0;
            $totalLooseCount = 0;
            $totalPiecesCount = 0;

            foreach ($saleItems as $it) {
                $ppb = (float)($it['pieces_per_box'] ?? 1);
                if ($ppb <= 0) $ppb = 1;
                $tp = (float)($it['total_pieces'] ?? 0);
                $totalPiecesCount += $tp;

                $vU = strtolower($it['variant_unit'] ?? '');
                $sM = $it['size_mode'] ?? 'std';

                if ($sM === 'by_cartons' || $vU === 'carton' || $vU === 'ctn') {
                    $b = floor($tp / $ppb);
                    $l = $tp % $ppb;
                    $totalCartonsCount += $b;
                    $totalLooseCount += $l;
                }
            }
        @endphp


        <!-- ========================================== -->
        <!-- EXCHANGE RETURN -->
        <!-- ========================================== -->

        @php

            $exchangeReturn =
                \App\Models\SaleReturn::with('items.product')
                    ->where(
                        'remarks',
                        'LIKE',
                        '%Invoice #'.$sale->invoice_no.'%'
                    )
                    ->first();

            $exchangeReturnedAmount = 0;

            if ($exchangeReturn) {

                $exchangeReturnedAmount =
                    $exchangeReturn->items->sum('line_total');

            }

        @endphp


        @if($exchangeReturn && $exchangeReturn->items->count() > 0)

            <div class="mt-3">

                <h6 class="fw-bold mb-2">
                    Returned Items (Exchange)
                </h6>


                <table class="invoice-table">

                    <thead class="bg-light">

                        <tr>

                            <th class="text-start"
                                style="width: 5%">
                                S.
                            </th>

                            <th class="text-start"
                                style="width: 38%">
                                Description
                            </th>

                            <th class="text-center"
                                style="width: 14%">
                                Qty
                            </th>

                            <th class="text-center"
                                style="width: 10%">
                                UOM
                            </th>

                            <th class="text-end"
                                style="width: 10%">
                                Price
                            </th>

                            <th class="text-end"
                                style="width: 10%">
                                Disc
                            </th>

                            <th class="text-end"
                                style="width: 13%">
                                Net Amount
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach ($exchangeReturn->items as $retItem)

                            <tr>

                                <td class="text-start">
                                    {{ $loop->iteration }}
                                </td>


                                <td class="text-start">

                                    <div style="font-weight: bold; font-size: 12px; margin-bottom: 2px;">

                                        {{ $retItem->product->item_name ?? 'Unknown' }}

                                    </div>

                                    @php

                                        $retColorStr = '';

                                        if (!empty($retItem->color)) {

                                            $decoded =
                                                base64_decode(
                                                    $retItem->color,
                                                    true
                                                );

                                            if (
                                                $decoded !== false
                                                &&
                                                is_string($decoded)
                                                &&
                                                str_starts_with(
                                                    trim($decoded),
                                                    '{'
                                                )
                                            ) {

                                                $parsed =
                                                    json_decode(
                                                        $decoded,
                                                        true
                                                    );

                                                if (
                                                    json_last_error()
                                                    ===
                                                    JSON_ERROR_NONE
                                                    &&
                                                    is_array($parsed)
                                                ) {

                                                    $parts = [];

                                                    if (
                                                        !empty($parsed['size'])
                                                        &&
                                                        $parsed['size'] !== '-'
                                                    ) {

                                                        $parts[] =
                                                            $parsed['size'];

                                                    }

                                                    if (
                                                        !empty($parsed['color'])
                                                        &&
                                                        $parsed['color'] !== '-'
                                                    ) {

                                                        $parts[] =
                                                            $parsed['color'];

                                                    }

                                                    $retColorStr =
                                                        implode(
                                                            ' | ',
                                                            $parts
                                                        );

                                                }

                                            }

                                        }

                                    @endphp


                                    @if($retColorStr)

                                        <div style="font-size: 11px; color: #555;">

                                            <span class="badge bg-light text-dark border p-1"
                                                style="font-size: 9px;">

                                                {{ $retColorStr }}

                                            </span>

                                        </div>

                                    @endif

                                </td>


                                <td class="text-center fw-bold">

                                    {{ (float)$retItem->qty }}
                                    Pcs

                                </td>


                                <td class="text-center fw-bold">
                                    Pieces
                                </td>


                                <td class="text-end">

                                    {{ number_format($retItem->price, 2) }}

                                </td>


                                <td class="text-end text-muted">
                                    —
                                </td>


                                <td class="text-end fw-bold">

                                    -
                                    {{ number_format($retItem->line_total, 2) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif


        <!-- ========================================== -->
        <!-- FOOTER / TOTALS -->
        <!-- ========================================== -->

        <div class="row mt-2">

            <div class="col-7">

                <div class="terms-box pt-2">

                    <p class="fw-bold mb-1">
                        Terms & Conditions:
                    </p>

                    <ul style="font-size: 10px;">

                        @php

                            $invoiceTerms =
                                \App\Models\Setting::get(
                                    'invoice_terms',
                                    "10% will be deducted on return of purchase goods within 7 days.\nLoose & Water Soak products will not be RETURNED.\nPlease bring this invoice for any returns or exchanges."
                                );

                            $termLines =
                                explode("\n", $invoiceTerms);

                        @endphp


                        @foreach($termLines as $line)

                            @if(trim($line))

                                <li>
                                    {{ trim($line) }}
                                </li>

                            @endif

                        @endforeach

                    </ul>

                </div>


                <div class="mt-4 pt-2">

                    <div class="signature-area">
                        Authorized Signature
                    </div>

                    <div class="small text-muted mt-1"
                        style="font-size: 10px;">

                        Printed on:
                        {{ date('d/m/Y h:i A') }}

                    </div>

                </div>

            </div>


            <div class="col-5">

                <div class="info-box"
                    style="border: none; padding: 0;">

                    <table class="totals-table">

                        @php
                            $lineDisc = collect($saleItems)->sum('discount_amount');
                            $orderDisc = (float)($sale->total_extradiscount ?? 0);
                            $totalDisc = $lineDisc + $orderDisc;
                            $grossTotal = collect($saleItems)->sum('total') + $lineDisc;
                            $netBill = $sale->total_net;
                            $paidAmount = (float)($sale->cash ?? 0);
                            $finalBal = $previousBalance + $netBill - $paidAmount;
                        @endphp

                        @if ($totalCartonsCount > 0)
                            <tr>
                                <td class="text-muted fw-bold">
                                    Total Cartons
                                </td>
                                <td class="text-end fw-bold" style="color: var(--primary-color);">
                                    @if ($totalLooseCount > 0)
                                        {{ $totalCartonsCount }} Cartons + {{ $totalLooseCount }} Pcs
                                    @else
                                        {{ $totalCartonsCount }} Cartons
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if ($totalDisc > 0)
                            <tr>
                                <td class="text-muted">
                                    Sub Total (Gross)
                                </td>
                                <td class="text-end text-muted">
                                    {{ number_format($grossTotal, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    Total Discount
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    - {{ number_format($totalDisc, 2) }}
                                </td>
                            </tr>
                        @endif


                        @if ($exchangeReturnedAmount > 0)

                            <tr>

                                <td class="text-muted">
                                    Return Value
                                </td>

                                <td class="text-end text-danger">

                                    -
                                    {{
                                        number_format(
                                            $exchangeReturnedAmount,
                                            2
                                        )
                                    }}

                                </td>

                            </tr>

                        @endif


                        @php

                            $finalPayable =
                                $netBill
                                -
                                $exchangeReturnedAmount;

                        @endphp


                        <tr>

                            <td class="fw-bold text-dark fs-6"
                                style="border-top: 2px solid #34495e; padding-top: 8px;">

                                {{
                                    $finalPayable < 0
                                        ? 'Refund To Customer'
                                        : 'Net Payable'
                                }}

                            </td>


                            <td class="text-end fw-bold text-dark fs-6"
                                style="border-top: 2px solid #34495e; padding-top: 8px;">

                                {{
                                    number_format(
                                        abs($finalPayable),
                                        2
                                    )
                                }}

                            </td>

                        </tr>


                        @if (round(abs($previousBalance), 2) > 0)

                            <tr style="border-bottom: 2px solid #eee;">

                                <td class="text-muted">
                                    Prev Bal
                                </td>

                                <td class="text-end text-muted">

                                    {{
                                        number_format(
                                            abs($previousBalance),
                                            2
                                        )
                                    }}

                                    <small>
                                        {{
                                            $previousBalance >= 0
                                                ? 'Dr'
                                                : 'Cr'
                                        }}
                                    </small>

                                </td>

                            </tr>

                        @endif


                        <tr>

                            <td>
                                Paid
                            </td>

                            <td class="text-end text-success">

                                {{
                                    number_format(
                                        $paidAmount,
                                        2
                                    )
                                }}

                            </td>

                        </tr>


                        @if ($sale->change > 0)

                            <tr>

                                <td>

                                    Change

                                    {{
                                        $sale->change_account
                                            ? ' ('.$sale->change_account->title.')'
                                            : ''
                                    }}

                                </td>

                                <td class="text-end text-danger">

                                    {{
                                        number_format(
                                            $sale->change,
                                            2
                                        )
                                    }}

                                </td>

                            </tr>

                        @endif


                        @php

                            $finalBal =
                                $previousBalance
                                +
                                $finalPayable
                                -
                                $paidAmount;

                        @endphp


                        <tr class="closing-bal">

                            <td class="fw-bold text-dark py-2">
                                Closing Balance
                            </td>

                            <td class="text-end fw-bold text-dark py-2">

                                {{
                                    number_format(
                                        abs($finalBal),
                                        2
                                    )
                                }}

                                <span class="text-muted"
                                    style="font-size: 11px;">

                                    {{
                                        $finalBal >= 0
                                            ? 'Dr'
                                            : 'Cr'
                                    }}

                                </span>

                            </td>

                        </tr>

                    </table>

                </div>


                <div class="text-end mt-1">

                    <small class="text-muted fst-italic"
                        style="font-size: 10px;">

                        {{
                            Str::limit(
                                $sale->total_amount_Words,
                                60
                            )
                        }}

                    </small>

                </div>

            </div>

        </div>

    </div>


    <!-- ========================================== -->
    <!-- THERMAL RECEIPT -->
    <!-- ========================================== -->

    <div class="receipt-container">

        <!-- Header -->

        <div class="company-name">

            {{ \App\Models\Setting::get(
                'company_name',
                'Three Stars Medical'
            ) }}

        </div>


        <div class="company-info">

            <div>
                {{ \App\Models\Setting::get(
                    'company_address',
                    'Hyderabad'
                ) }}
            </div>

            <div>
                Ph:
                {{ \App\Models\Setting::get(
                    'company_phone',
                    '0327-9226901'
                ) }}
            </div>

        </div>


        @if(
            \App\Models\Setting::get('facebook_link')
            ||
            \App\Models\Setting::get('tiktok_link')
            ||
            \App\Models\Setting::get('instagram_link')
            ||
            \App\Models\Setting::get('website_link')
        )

            <div style="text-align: left; font-size: 10px; line-height: 1.4; word-wrap: break-word; overflow-wrap: break-word; margin-top: 4px;">

                @if(\App\Models\Setting::get('facebook_link'))

                    <div style="margin-bottom: 2px;">
                        <strong>Facebook:</strong>
                        {{ \App\Models\Setting::get('facebook_link') }}
                    </div>

                @endif


                @if(\App\Models\Setting::get('tiktok_link'))

                    <div style="margin-bottom: 2px;">
                        <strong>TikTok:</strong>
                        {{ \App\Models\Setting::get('tiktok_link') }}
                    </div>

                @endif


                @if(\App\Models\Setting::get('instagram_link'))

                    <div style="margin-bottom: 2px;">
                        <strong>Instagram:</strong>
                        {{ \App\Models\Setting::get('instagram_link') }}
                    </div>

                @endif


                @if(\App\Models\Setting::get('website_link'))

                    <div style="margin-bottom: 2px;">
                        <strong>Website:</strong>
                        {{ \App\Models\Setting::get('website_link') }}
                    </div>

                @endif

            </div>

        @endif


        <div class="policy-banner">
            No Return, Only Exchange in 3 days
        </div>


        <div class="divider"></div>


        <!-- Meta -->

        @php
            $isWalkin = empty($sale->customer_id);
        @endphp


        <div class="meta-grid">

            <div class="meta-row">

                <span class="meta-label">
                    Invoice #:
                </span>

                <span class="meta-value">
                    {{ $sale->invoice_no }}
                </span>

            </div>


            <div class="meta-row">

                <span class="meta-label">
                    Date:
                </span>

                <span class="meta-value">

                    {{
                        $sale->created_at
                            ? $sale->created_at->format('d/m/Y h:i A')
                            : date('d/m/Y h:i A')
                    }}

                </span>

            </div>


            <div class="meta-row">

                <span class="meta-label">
                    Customer:
                </span>

                <span class="meta-value">

                    {{
                        Str::limit(
                            $sale->walkin_name
                            ??
                            ($sale->customer_relation->customer_name
                                ?? 'Walking Customer'),
                            22
                        )
                    }}

                </span>

            </div>


            @if (auth()->check())

                <div class="meta-row">

                    <span class="meta-label">
                        Salesperson:
                    </span>

                    <span class="meta-value">
                        {{ auth()->user()->name }}
                    </span>

                </div>

            @endif


            @if($sale->reference)

                <div class="meta-row">

                    <span class="meta-label">
                        Remarks:
                    </span>

                    <span class="meta-value">
                        {{ $sale->reference }}
                    </span>

                </div>

            @endif

        </div>


        <div class="divider"></div>


        <!-- ========================================== -->
        <!-- THERMAL ITEMS -->
        <!-- ========================================== -->

        <table class="items-table">

            <thead>

                <tr>

                    <th style="width: 6%;">
                        S.
                    </th>

                    <th style="width: 53%;">
                        Description
                    </th>

                    <th style="width: 10%;"
                        class="text-center">
                        Qty
                    </th>

                    <th style="width: 13%;"
                        class="text-end">
                        Rate
                    </th>

                    <th style="width: 18%;"
                        class="text-end">
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($saleItems as $item)

                    @php

                        $sizeMode =
                            $item['size_mode'] ?? 'std';

                        $totalPieces =
                            (int)$item['total_pieces'];

                        $variantUnit =
                            strtolower(
                                $item['variant_unit']
                                ?? ''
                            );

                        $weightGrams =
                            (float)(
                                $item['weight_per_piece']
                                ?? 0
                            );

                        $qtyDisplay =
                            $totalPieces . ' Pcs';


                        if (
                            $variantUnit === 'pcs'
                            ||
                            $variantUnit === 'piece'
                            ||
                            $variantUnit === 'pieces'
                        ) {

                            $qtyDisplay =
                                $totalPieces . ' Pcs';

                            if ($weightGrams > 0) {

                                $qtyDisplay .=
                                    ' (' .
                                    (
                                        $weightGrams == (int)$weightGrams
                                            ? (int)$weightGrams
                                            : $weightGrams
                                    )
                                    .
                                    'g)';

                            }

                        }

                        elseif (
                            in_array(
                                $sizeMode,
                                [
                                    'by_kg',
                                    'by_gm',
                                    'by_feet',
                                    'by_meter'
                                ]
                            )
                        ) {

                            $uomLabel = match($sizeMode) {

                                'by_kg' =>
                                    'Kg',

                                'by_gm' =>
                                    'Gm',

                                'by_feet' =>
                                    'Ft',

                                'by_meter' =>
                                    'Mtr',

                                default =>
                                    '',

                            };


                            $qtyVal =
                                (float)(
                                    $item['qty_box']
                                    ??
                                    $item['qty']
                                    ??
                                    $totalPieces
                                );


                            $qtyDisplay =
                                (
                                    $qtyVal == (int)$qtyVal
                                        ? (int)$qtyVal
                                        : number_format($qtyVal, 3)
                                )
                                .
                                ' '
                                .
                                $uomLabel;

                        }

                        elseif (
                            $sizeMode == 'by_cartons'
                            ||
                            $sizeMode == 'by_size'
                        ) {

                            $piecesPerBox =
                                (int)(
                                    $item['pieces_per_box']
                                    ?? 1
                                );


                            if ($piecesPerBox <= 0) {
                                $piecesPerBox = 1;
                            }


                            $boxes =
                                floor(
                                    $totalPieces
                                    /
                                    $piecesPerBox
                                );


                            $loose =
                                $totalPieces
                                %
                                $piecesPerBox;


                            if (
                                $boxes > 0
                                &&
                                $loose > 0
                            ) {

                                $qtyDisplay =
                                    "$boxes.$loose";

                            }

                            elseif ($boxes > 0) {

                                $qtyDisplay =
                                    $boxes;

                            }

                            else {

                                $qtyDisplay =
                                    $loose;

                            }

                        }

                    @endphp


                    <tr>

                        <td style="width: 6%;">
                            {{ $loop->iteration }}
                        </td>


                        <td style="width: 53%;">

                            @php
                                $tVName = $item['variant_name'] ?? '';
                                $tVSize = (!empty($item['size_val']) && $item['size_val'] !== '-') ? $item['size_val'] : '';
                                $tVColor = (!empty($item['color_val']) && $item['color_val'] !== '-') ? $item['color_val'] : '';
                                
                                $tVExtra = [];
                                if ($tVSize) $tVExtra[] = $tVSize;
                                if ($tVColor) $tVExtra[] = $tVColor;
                                $tVExtraStr = count($tVExtra) > 0 ? ' (' . implode(', ', $tVExtra) . ')' : '';

                                $tProductTitle = $item['item_name'];
                                if ($tVName && strtolower(trim($tVName)) !== strtolower(trim($tProductTitle))) {
                                    $tProductTitle .= ' — ' . $tVName;
                                }
                                $tProductTitle .= $tVExtraStr;
                            @endphp

                            <span class="item-name">
                                {{ $tProductTitle }}
                            </span>

                            {{-- 
                                IMPORTANT:
                                Raw color / variant data intentionally
                                removed from thermal receipt as well.
                            --}}

                        </td>


                        <td style="width: 10%;"
                            class="text-center">

                            {{ $qtyDisplay }}

                        </td>


                        <td style="width: 13%;"
                            class="text-end">

                            {{ number_format($item['price'], 0) }}

                        </td>


                        <td style="width: 18%;"
                            class="text-end">

                            {{ number_format($item['total'], 0) }}

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>


        <!-- Returned Items -->

        @if(
            $exchangeReturn
            &&
            $exchangeReturn->items->count() > 0
        )

            <div class="divider"
                style="border-top: 1px dashed #000; margin: 4px 0;">
            </div>


            <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px; color: #000;">
                Returned Items:
            </div>


            <table class="items-table"
                style="margin-bottom: 4px;">

                <tbody>

                    @foreach ($exchangeReturn->items as $retItem)

                        <tr>

                            <td style="width: 6%;">
                                {{ $loop->iteration }}
                            </td>


                            <td style="width: 53%;">

                                @if(
                                    $retItem->is_manual
                                    ||
                                    empty($retItem->product_id)
                                )

                                    <span class="item-name">

                                        {{ $retItem->product_name }}
                                        (Manual)

                                    </span>

                                @else

                                    <span class="item-name">

                                        {{
                                            $retItem->product->item_name
                                            ?? 'Unknown'
                                        }}

                                    </span>

                                @endif

                            </td>


                            <td style="width: 10%;"
                                class="text-center">

                                {{ (float)$retItem->qty }}

                            </td>


                            <td style="width: 13%;"
                                class="text-end">

                                {{ number_format($retItem->price, 0) }}

                            </td>


                            <td style="width: 18%;"
                                class="text-end">

                                -
                                {{
                                    number_format(
                                        $retItem->line_total,
                                        0
                                    )
                                }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @endif


        <!-- Totals -->
        @php
            $thLineDisc = collect($saleItems)->sum('discount_amount');
            $thOrderDisc = (float)($sale->total_extradiscount ?? 0);
            $thTotalDisc = $thLineDisc + $thOrderDisc;
            $thGrossTotal = collect($saleItems)->sum('total') + $thLineDisc;
        @endphp

        <div class="totals-section">
            <div class="tot-row">
                <span>Sub Total:</span>
                <span>{{ number_format($thGrossTotal, 0) }}</span>
            </div>

            @if ($thTotalDisc > 0)
                <div class="tot-row">
                    <span>Discount:</span>
                    <span>- {{ number_format($thTotalDisc, 0) }}</span>
                </div>
            @endif


            @if ($exchangeReturnedAmount > 0)

                <div class="tot-row">

                    <span>
                        Return Value:
                    </span>

                    <span>

                        -
                        {{
                            number_format(
                                $exchangeReturnedAmount,
                                0
                            )
                        }}

                    </span>

                </div>

            @endif


            @php

                $finalPayable =
                    $sale->total_net
                    -
                    $exchangeReturnedAmount;

            @endphp


            <div class="tot-row grand-total">

                @if($finalPayable < 0)

                    <span>
                        REFUND TO CUSTOMER:
                    </span>

                    <span>

                        {{
                            number_format(
                                abs($finalPayable),
                                0
                            )
                        }}

                    </span>

                @else

                    <span>
                        TOTAL PAYABLE:
                    </span>

                    <span>

                        {{
                            number_format(
                                $finalPayable,
                                0
                            )
                        }}

                    </span>

                @endif

            </div>

        </div>


        <!-- Ledger -->

        <div class="balance-section">

            @if(!$isWalkin)

                <div class="tot-row">

                    <span>
                        Prev Balance:
                    </span>

                    <span>

                        {{
                            number_format(
                                abs($previousBalance),
                                0
                            )
                        }}

                        {{
                            $previousBalance >= 0
                                ? 'Dr'
                                : 'Cr'
                        }}

                    </span>

                </div>

            @endif


            <div class="tot-row">

                <span>
                    Paid Amount:
                </span>

                <span>

                    {{
                        number_format(
                            $sale->cash,
                            0
                        )
                    }}

                </span>

            </div>


            @if($sale->change > 0)

                <div class="tot-row">

                    <span>

                        Change
                        {{
                            $sale->change_account
                                ? ' ('.$sale->change_account->title.')'
                                : ''
                        }}:

                    </span>

                    <span>

                        {{
                            number_format(
                                $sale->change,
                                0
                            )
                        }}

                    </span>

                </div>

            @endif


            @if(!$isWalkin)

                @php

                    $finalBalance =
                        $previousBalance
                        +
                        $sale->total_net
                        -
                        $sale->cash;

                @endphp


                <div class="tot-row closing-bal">

                    <span>
                        CLOSING BALANCE:
                    </span>

                    <span>

                        {{
                            number_format(
                                abs($finalBalance),
                                0
                            )
                        }}

                        {{
                            $finalBalance >= 0
                                ? 'Dr'
                                : 'Cr'
                        }}

                    </span>

                </div>

            @endif

        </div>


        <!-- Footer -->

        <div class="footer">

            <p>
                Thank you for shopping with us!
            </p>

            <div class="soft-credit">

                Powered by Prowave Technologies
                <br>
                📞 +92 317 3836 223

            </div>

        </div>

    </div>


    <!-- ========================================== -->
    <!-- JAVASCRIPT -->
    <!-- ========================================== -->

    <script>

        function handleGoBack() {

            const urlParams =
                new URLSearchParams(
                    window.location.search
                );


            if (
                urlParams.get('from') === 'pos'
                ||
                (
                    document.referrer
                    &&
                    document.referrer.indexOf('/pos') !== -1
                )
            ) {

                window.location.href =
                    "{{ route('pos.index') }}";

                return;

            }


            if (
                window.opener
                &&
                !window.opener.closed
            ) {

                window.close();

                setTimeout(function() {

                    window.location.href =
                        "{{ route('sale.index') }}";

                }, 150);

                return;

            }


            if (
                window.history.length > 1
                &&
                document.referrer
                &&
                document.referrer.indexOf(
                    window.location.host
                ) !== -1
                &&
                !document.referrer.includes('/sales/store')
            ) {

                window.history.back();

                return;

            }


            window.location.href =
                "{{ route('sale.index') }}";

        }

    </script>

</body>

</html>