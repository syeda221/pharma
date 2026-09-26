@extends('admin_panel.layout.app')

@section('content')
  <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        /* ================= RESPONSIVE PURCHASE UI (Modernized) ================= */
        body {
            background-color: #f4f6f9;
            /* Light gray background for contrast */
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .sales-table {
            border-collapse: collapse !important;
            margin-bottom: 0 !important;
            min-width: 1000px;
        }

        .sales-table thead th {
            background-color: #f8fafc !important; /* Light clean header */
            color: #0f172a !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 11px !important;
            letter-spacing: 0.5px;
            padding: 10px 8px !important;
            border: 1px solid #cbd5e1 !important;
            border-bottom: 2px solid #94a3b8 !important; /* Thick header separator border */
            vertical-align: middle !important;
            text-align: center;
        }

        .sales-table thead th.col-product {
            text-align: left !important;
            padding-left: 12px !important;
        }

        .sales-table tbody td {
            border: 1px solid #cbd5e1 !important; /* Flat interior cell borders */
            padding: 0 !important; /* Zero padding to let input fill cell completely */
            background-color: #ffffff;
            vertical-align: middle !important;
        }

        /* ⚡ FLAT BORDERLESS GRID INPUTS ⚡ */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 38px !important; /* Uniform height */
            margin: 0 !important;
            padding: 6px 8px !important;
            width: 100% !important;
            background-color: transparent !important;
            text-align: center; /* Center-align text in grid inputs */
            color: #1e293b !important;
            font-weight: 500 !important;
            font-size: 0.82rem !important;
        }

        .sales-table tbody td.col-product .form-select {
            text-align: left !important;
            padding-left: 12px !important;
        }

        /* Calculations and Read-Only cells get a neat slate tone background */
        .sales-table tbody .input-readonly,
        .sales-table tbody input[readonly],
        .sales-table tbody select[disabled] {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            color: #475569 !important;
            font-weight: 600 !important;
        }

        /* Subtle focus highlight inside cell */
        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        /* Select2 Specific flat borderless styling */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 38px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background-color: transparent !important;
            display: flex;
            align-items: center;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            font-size: 0.82rem !important;
            color: #1e293b !important;
            font-weight: 500 !important;
            text-align: left !important;
        }

        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        /* Select2 Focus state */
        .sales-table tbody .select2-container--default.select2-container--focus .select2-selection--single {
            background-color: #f8fafc !important;
            box-shadow: inset 0 0 0 2px #2563eb !important;
        }

        /* Elegant flat block layout for discount input + toggle */
        .sales-table tbody .discount-wrapper {
            display: flex !important;
            align-items: stretch !important;
            width: 100% !important;
            height: 38px !important;
            gap: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sales-table tbody .discount-wrapper .discount-value {
            flex-grow: 1 !important;
            border: none !important;
            border-radius: 0 !important;
            height: 100% !important;
            text-align: center;
            background-color: transparent !important;
            padding: 6px 8px !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle {
            border: none !important;
            border-radius: 0 !important;
            background-color: #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            width: 32px !important;
            min-width: 32px !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            cursor: pointer !important;
            transition: background-color 0.2s !important;
        }

        .sales-table tbody .discount-wrapper .discount-toggle:hover {
            background-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        .sales-table tfoot td {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            border-top: 2px solid #94a3b8 !important; /* Thick tfoot separator */
            padding: 8px 10px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
        }

        /* Row hover */
        .sales-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Column widths */
        .col-product {
            width: 300px;
            min-width: 250px;
        }

        .col-warehouse {
            width: 140px;
        }

        .col-stock {
            width: 90px;
        }

        .col-qty {
            width: 100px;
        }

        .col-pieces {
            width: 100px;
        }

        .col-price {
            width: 120px;
        }

        .col-disc {
            width: 80px;
        }

        .col-disc-amt {
            width: 95px;
        }

        .col-price-p {
            width: 100px;
        }

        .col-amount {
            width: 120px;
            text-align: right;
        }

        .col-action {
            width: 50px;
            text-align: center;
        }

        .main-container {
            font-size: .85rem;
            max-width: 99%;
            border-radius: 12px !important;
            border: none !important;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08) !important;
        }

        .btn {
            font-size: .82rem;
            padding: .35rem .8rem;
            border-radius: 5px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-success {
            background-color: #198754;
            border-color: #198754;
        }

        /* Mobile Breakpoints (< 768px) */
        @media (max-width: 768px) {
            .header-text {
                font-size: 1.1rem !important;
            }
            .main-container {
                padding: 12px !important;
                border-radius: 8px !important;
            }
            .sales-table {
                min-width: 780px !important;
            }
            .discount-wrapper {
                min-width: 70px !important;
            }
            .btn-submit-update {
                width: 100% !important;
                height: 46px !important;
                font-size: 1rem !important;
            }
            .payment-row select, .payment-row input {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        .section-title {
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            border-left: 3px solid #0d6efd;
            padding-left: 8px;
        }

        /* Product Search Dropdown */
        .search-results {
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            z-index: 1000;
            max-height: 250px;
            overflow-y: auto;
            width: 100%;
            list-style: none;
            padding: 0;
            margin: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
        }

        .search-result-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: background 0.1s;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover,
        .search-result-item.active {
            background-color: #e7f1ff;
            color: #0b5ed7;
        }

        /* Layout Helpers */
        .card-panel {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            height: 100%;
        }

        .summary-card {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .select2-container .select2-selection--single {
            height: 36px !important;
            padding: 3px 12px;
            border-color: #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 5px !important;
        }
    </style>

    <div class="container-fluid py-2">
        <div class="main-container bg-white border shadow-sm mx-auto p-2 rounded-3">

            <form id="purchaseForm" action="{{ route('purchase.update', $purchase->id) }}" method="POST" autocomplete="off">
                @csrf
                @method('PUT')

                {{-- HEADER --}}
                <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                    <div>
                        <a href="{{ route('Purchase.home') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <h2 class="header-text text-secondary fw-bold mb-0">Edit Purchase #{{ $purchase->invoice_no }}</h2>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-secondary" id="entryDate">Date: {{ date('d/m/Y') }}</small>
                    </div>
                </div>

                <div class="row g-3 border-bottom pb-4 mb-3 mt-2">
                    {{-- LEFT: Invoice & Vendor --}}
                    <div class="col-lg-3 col-md-4">
                        <div class="card-panel shadow-sm">
                            <div class="section-title mb-3">Invoice & Vendor</div>

                            <div class="mb-2 d-flex align-items-center gap-2">
                                <label class="form-label fw-bold mb-0 text-muted small" style="min-width: 80px;">Invoice
                                    No</label>
                                <input type="text" class="form-control input-readonly" name="invoice_no"
                                    value="{{ $purchase->invoice_no }}" readonly>
                            </div>

                            <!-- VENDOR SELECT -->
                            <div class="mb-2">
                                <label class="form-label fw-bold mb-1 text-muted small">Select Vendor</label>
                                <select class="form-select select2" id="vendorSelect" name="vendor_id">
                                    <option value="" disabled>Select Vendor</option>
                                    @foreach ($Vendor as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $v->id == $purchase->vendor_id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold mb-1 text-muted small">Date</label>
                                <input type="date" name="purchase_date" class="form-control"
                                    value="{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') : date('Y-m-d') }}">
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted small">M.Bill</label>
                                <textarea class="form-control" name="note" rows="2">{{ $purchase->note }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted small">Warehouse</label>
                                <select name="warehouse_id" class="form-control select2">
                                    @foreach ($Warehouse as $w)
                                        <option value="{{ $w->id }}"
                                            {{ $w->id == $purchase->warehouse_id ? 'selected' : '' }}>
                                            {{ $w->warehouse_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Items --}}
                    <div class="col-lg-9 col-md-8">
                        <div class="card-panel shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="section-title mb-0">Purchase Items</div>
                                <button type="button" class="btn btn-sm btn-primary px-3 shadow-sm"
                                    onclick="addBlankRow()">
                                    <i class="bi bi-plus-lg"></i> Add Row
                                </button>
                            </div>

                            <div class="table-responsive border rounded-3 bg-white">
                                <table class="table table-bordered sales-table mb-0" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th class="col-product">Product & Variant</th>
                                            <th class="col-unit" style="width: 100px;">Unit</th>
                                            <th class="col-qty" style="width: 110px;">Qty</th>
                                            <th class="col-price" style="width: 130px;">Base Price</th>
                                            <th class="col-disc" style="width: 90px;">Disc %</th>
                                            <th class="col-disc-amt" style="width: 110px;">Disc Amt</th>
                                            <th class="col-amount" style="width: 130px;">Amount</th>
                                            <th class="col-action" style="width: 50px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="purchaseTableBody">
                                        @foreach ($purchase->items as $item)
                                            @php
                                                $sizeMode = $item->size_mode ?? 'by_pieces';
                                                $ppb = (float) ($item->pieces_per_box > 0 ? $item->pieces_per_box : 1);
                                                $uVal = strtolower($unitName ?? 'pcs');
                                                $isCtn = in_array($uVal, ['carton', 'ctn', 'box']) || ($sizeMode === 'by_cartons');
                                                $isKg = in_array($uVal, ['kg', 'gm', 'g']);

                                                $displayQty = (float) $item->qty;
                                                if ($isCtn && ($item->loose_qty > 0 || $item->boxes_qty > 0)) {
                                                    $b = (int) $item->boxes_qty;
                                                    $l = (int) $item->loose_qty;
                                                    if ($l > 0) {
                                                        $displayQty = $b . '.' . $l;
                                                    } else {
                                                        $displayQty = $b;
                                                    }
                                                }

                                                $baseProductName = $item->product->item_name ?? 'Product';
                                                $variantNameDisplay = $baseProductName;
                                                $variantInfo = '';
                                                $rawVariantData = $item->color ?? '';
                                                $unitName = !empty($item->unit) ? $item->unit : ($item->product->unit->name ?? 'Pcs');

                                                if (!empty($item->color)) {
                                                    $decodedColor = base64_decode($item->color, true);
                                                    $vData = ($decodedColor !== false) ? json_decode($decodedColor, true) : null;
                                                    if (!$vData) {
                                                        $vData = json_decode($item->color, true);
                                                    }
                                                    if (is_array($vData)) {
                                                        $vName = trim($vData['name'] ?? ($vData['variant_name'] ?? ''));
                                                        $vColorName = trim($vData['color'] ?? '');
                                                        $vSize = trim($vData['size'] ?? '');
                                                        if (empty($item->unit) && !empty($vData['unit'])) {
                                                            $unitName = $vData['unit'];
                                                        }
                                                        $vParts = [];
                                                        $sStr = ($vSize !== '' && $vSize !== '-') ? " {$vSize}" : '';
                                                        $cStr = ($vColorName !== '' && $vColorName !== '-') ? " ({$vColorName})" : '';

                                                        if ($vName !== '') {
                                                            if (stripos($vName, $baseProductName) !== false) {
                                                                $variantNameDisplay = $vName;
                                                            } else {
                                                                $variantNameDisplay = $baseProductName . ' — ' . $vName;
                                                            }
                                                        } else {
                                                            $variantNameDisplay = $baseProductName;
                                                        }

                                                        if ($sStr !== '' && stripos($variantNameDisplay, trim($vSize)) === false) {
                                                            $variantNameDisplay .= $sStr;
                                                        }
                                                        if ($cStr !== '' && stripos($variantNameDisplay, trim($vColorName)) === false) {
                                                            $variantNameDisplay .= $cStr;
                                                        }

                                                        if ($vColorName && $vColorName !== '-') {
                                                            $vParts[] = 'Color: ' . $vColorName;
                                                        }
                                                        if ($vSize && $vSize !== '-') {
                                                            $vParts[] = 'Size: ' . $vSize;
                                                        }
                                                        if (!empty($vParts)) {
                                                            $variantInfo = implode(' | ', $vParts);
                                                        }
                                                    } elseif (is_string($item->color) && trim($item->color) !== '' && trim($item->color) !== '-') {
                                                        $variantNameDisplay = $baseProductName . ' (' . trim($item->color) . ')';
                                                        $variantInfo = trim($item->color);
                                                    }
                                                }

                                                $optionVal = $item->product_id;
                                                if (!empty($rawVariantData)) {
                                                    $encodedVar = (base64_decode($rawVariantData, true) !== false) ? $rawVariantData : base64_encode($rawVariantData);
                                                    $optionVal = $item->product_id . '|variant|' . $encodedVar;
                                                }

                                                $gross = $item->line_total + $item->item_discount;
                                                $dPct = $gross > 0 ? ($item->item_discount / $gross) * 100 : 0;
                                            @endphp
                                            <tr data-sizemode="{{ $sizeMode }}"
                                                data-pieces_per_m2="{{ $item->pieces_per_m2 }}">
                                                <td>
                                                    <select class="form-select product-select2" name="product_id[]">
                                                        <option value="{{ $optionVal }}" selected>
                                                            {{ $variantNameDisplay }} ({{ $item->product->item_code ?? 'SKU' }})
                                                        </option>
                                                    </select>
                                                    <div class="variant-badge-wrapper px-2 py-1 small text-muted d-flex gap-2 align-items-center {{ empty($variantInfo) ? 'd-none' : '' }}">
                                                        <span class="badge bg-light text-dark border variant-badge">{{ $variantInfo }}</span>
                                                    </div>
                                                    {{-- Snapshots --}}
                                                    <input type="hidden" name="size_mode[]" class="hidden-size-mode"
                                                        value="{{ $sizeMode }}">
                                                    <input type="hidden" name="pieces_per_box[]"
                                                        class="hidden-pieces-per-box" value="{{ $ppb }}">
                                                    <input type="hidden" name="pieces_per_m2[]"
                                                        class="hidden-pieces-per-m2" value="{{ $item->pieces_per_m2 }}">
                                                    <input type="hidden" name="boxes_qty[]" class="hidden-boxes-qty" value="{{ $item->boxes_qty ?? 0 }}">
                                                    <input type="hidden" name="loose_qty[]" class="hidden-loose-qty" value="{{ $item->loose_qty ?? 0 }}">
                                                    <input type="hidden" name="length[]" class="hidden-length"
                                                        value="{{ $item->length }}">
                                                    <input type="hidden" name="width[]" class="hidden-width"
                                                        value="{{ $item->width }}">
                                                    <input type="hidden" name="color[]" class="hidden-variant-data"
                                                        value="{{ $rawVariantData }}">
                                                </td>
                                                <td class="text-center align-middle">
                                                    @php
                                                        $uVal = strtolower($unitName ?? 'pcs');
                                                        $isCtn = in_array($uVal, ['carton', 'ctn', 'box']);
                                                        $isKg = in_array($uVal, ['kg', 'gm', 'g']);
                                                        $btnClass = $isCtn ? 'btn-outline-success' : ($isKg ? 'btn-outline-primary' : 'btn-outline-info');
                                                    @endphp
                                                    <button type="button" class="btn btn-sm {{ $btnClass }} fw-bold unit-toggle-btn py-0 px-2" data-unit="{{ $unitName }}" title="Click to toggle unit (Carton ↔ Pcs / Kg ↔ Gm)" style="font-size:0.75rem; min-width: 55px; cursor: pointer;">{{ $unitName }}</button>
                                                    <input type="hidden" name="unit[]" class="unit-input-val" value="{{ $unitName }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" min="0.0001" name="qty[]"
                                                        class="form-control text-center main-qty-input"
                                                        value="{{ $displayQty }}" placeholder="Qty">
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="price[]" class="form-control text-end price"
                                                            step="0.01" value="{{ (float) $item->price }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="number" name="item_discount[]" class="form-control text-end item-disc-percent"
                                                        step="0.01" value="{{ round($dPct, 2) }}">
                                                </td>
                                                <td>
                                                    <input type="number"
                                                        class="form-control text-end input-readonly item-disc-amt"
                                                        value="{{ (float) $item->item_discount }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control text-end input-readonly row-total"
                                                        value="{{ (float) $item->line_total }}" readonly>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger remove-row border-0"><i
                                                             class="bi bi-x-lg"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end fw-bold text-muted">Total Amount:</td>
                                            <td class="text-end fw-bold fs-6 text-dark" colspan="2"><span id="totalAmount">0.00</span>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SUMMARY --}}
                <div class="row g-3 mt-1">
                    {{-- LEFT: Payment / Receipt Voucher --}}
                    <div class="col-lg-7">
                        <div class="card-panel shadow-sm">
                            <div class="section-title mb-3">Payment / Receipt Voucher</div>
                            <div id="paymentWrapper" class="border rounded p-3 bg-light mb-3">
                                @if (isset($existingPayments) && $existingPayments->isNotEmpty())
                                    @foreach ($existingPayments as $pIndex => $pDetail)
                                        <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                                            <select class="form-select rv-account" name="payment_account_id[]"
                                                style="max-width: 300px; flex-grow: 1;">
                                                <option value="" disabled>Select Account</option>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}" {{ $acc->id == $pDetail->account_id ? 'selected' : '' }}>
                                                        {{ $acc->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" class="form-control text-end payment-amount"
                                                name="payment_amount[]" value="{{ (float) $pDetail->credit }}" placeholder="Amount" style="width:140px" step="0.01">
                                            @if ($loop->first)
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPayment">
                                                    <i class="bi bi-plus"></i> Add
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-payment">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                                        <select class="form-select rv-account" name="payment_account_id[]"
                                            style="max-width: 300px; flex-grow: 1;">
                                            <option value="" selected disabled>Select Account</option>
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" class="form-control text-end payment-amount"
                                            name="payment_amount[]" placeholder="Amount" style="width:140px" step="0.01">
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddPayment">
                                            <i class="bi bi-plus"></i> Add
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="me-2 fw-bold text-muted">Total Paid:</span>
                                <span class="fw-bold fs-6 text-success" id="totalPaid">0.00</span>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: Summary --}}
                    <div class="col-lg-5">
                        <div class="card-panel shadow-sm">
                            <div class="section-title mb-3">Summary</div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Total Qty (Pieces)</div>
                                <div class="col-5 text-end"><span id="tQty" class="fw-bold">0</span></div>
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Sub-Total</div>
                                <div class="col-5 text-end fw-bold"><span id="tSub">0.00</span></div>
                                <input type="hidden" name="subtotal" id="subtotalInput">
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Bill Discount</div>
                                <div class="col-5 text-end d-flex gap-1">
                                    @php
                                        $inlineVal = $purchase->items->sum('item_discount');
                                        $bSub = (float) $purchase->subtotal + $inlineVal;
                                        $bDisc = (float) $purchase->discount + $inlineVal;
                                        $bPct = $bSub > 0 ? ($bDisc / $bSub) * 100 : 0;
                                    @endphp
                                    <input type="number" class="form-control text-end form-control-sm"
                                        id="billDiscountPct" value="{{ round($bPct, 2) }}" placeholder="%" style="width: 70px;" step="0.01">
                                    <input type="number" class="form-control text-end form-control-sm"
                                        id="billDiscount" value="{{ (float) $bDisc }}" step="0.01">
                                    <input type="hidden" name="discount" id="discountInput" value="{{ (float) $purchase->discount }}">
                                </div>
                            </div>
                            <div class="row py-1 align-items-center">
                                <div class="col-7 text-muted fw-medium">Extra Cost</div>
                                <div class="col-5 text-end">
                                    <input type="number" class="form-control text-end form-control-sm" name="extra_cost"
                                        id="extraCost" value="{{ (float) $purchase->extra_cost }}">
                                </div>
                            </div>
                            <hr class="my-2 border-secondary">
                            <div class="row py-2">
                                <div class="col-6 fw-bold fs-5 text-primary">Net Payable</div>
                                <div class="col-6 text-end fw-bold fs-5 text-primary"><span id="tPayable">0.00</span>
                                </div>
                                <input type="hidden" name="net_amount" id="netAmountInput">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success btn-submit-update px-5 fw-bold shadow-sm">
                        <i class="bi bi-save me-2"></i> Update Purchase
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Init Global Select2
            $('.select2').select2({
                width: '100%'
            });

            // Initialize existing product selects
            $('.product-select2').each(function() {
                initProductSelect2($(this));
            });

            // Recalc rows & payments on initial load
            $('#purchaseTableBody tr').each(function() {
                recalcRow($(this));
            });
            recalcPayments();
            recalcAll();

            // Unit Toggle Handler (Carton ↔ Pcs / Kg ↔ Gm)
            $(document).on('click', '.unit-toggle-btn', function() {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                const sizeMode = $row.data('sizemode') || $row.find('.hidden-size-mode').val();
                const packQty = parseFloat($row.find('.hidden-pieces-per-box').val()) || parseFloat($row.data('pieces_per_box')) || 1;
                let currentUnit = ($btn.attr('data-unit') || $btn.text() || '').trim();
                const $priceInp = $row.find('.price');
                let curPrice = parseFloat($priceInp.val()) || 0;

                const isCartonOrPcs = (sizeMode === 'by_cartons' || packQty > 1 || ['carton', 'ctn', 'pcs', 'pc', 'piece'].includes(currentUnit.toLowerCase()));

                if (isCartonOrPcs) {
                    if (currentUnit.toLowerCase() === 'carton' || currentUnit.toLowerCase() === 'ctn') {
                        // Switch from Carton to Pcs
                        currentUnit = 'Pcs';
                        $btn.text('Pcs')
                            .removeClass('btn-outline-success btn-outline-primary')
                            .addClass('btn-outline-info')
                            .attr('data-unit', 'Pcs');
                        $row.find('.unit-input-val').val('Pcs');

                        if (packQty > 1 && curPrice > 0) {
                            let piecePrice = curPrice / packQty;
                            $priceInp.val(piecePrice % 1 === 0 ? piecePrice : piecePrice.toFixed(2));
                        }
                    } else {
                        // Switch from Pcs to Carton
                        currentUnit = 'Carton';
                        $btn.text('Carton')
                            .removeClass('btn-outline-info btn-outline-primary')
                            .addClass('btn-outline-success')
                            .attr('data-unit', 'Carton');
                        $row.find('.unit-input-val').val('Carton');

                        if (packQty > 1 && curPrice > 0) {
                            let cartonPrice = curPrice * packQty;
                            $priceInp.val(cartonPrice % 1 === 0 ? cartonPrice : cartonPrice.toFixed(2));
                        }
                    }
                    recalcRow($row);
                    recalcAll();
                } else if (sizeMode === 'by_kg' || sizeMode === 'by_gm') {
                    if (currentUnit.toLowerCase() === 'kg') {
                        currentUnit = 'Gm';
                        $btn.text('Gm').removeClass('btn-outline-primary').addClass('btn-outline-info').attr('data-unit', 'Gm');
                    } else {
                        currentUnit = 'Kg';
                        $btn.text('Kg').removeClass('btn-outline-info').addClass('btn-outline-primary').attr('data-unit', 'Kg');
                    }
                    $row.find('.unit-input-val').val(currentUnit);
                    recalcRow($row);
                    recalcAll();
                }
            });

            // Add Row
            window.addBlankRow = function() {
                const html = `
                <tr>
                    <td>
                        <select class="form-select product-select2" name="product_id[]"></select>
                        <div class="variant-badge-wrapper px-2 py-1 small text-muted d-flex gap-2 align-items-center d-none">
                            <span class="badge bg-light text-dark border variant-badge"></span>
                        </div>
                        <input type="hidden" name="size_mode[]" class="hidden-size-mode">
                        <input type="hidden" name="pieces_per_box[]" class="hidden-pieces-per-box" value="1">
                        <input type="hidden" name="pieces_per_m2[]" class="hidden-pieces-per-m2" value="0">
                        <input type="hidden" name="price_per_carton[]" class="hidden-price-per-carton" value="0">
                        <input type="hidden" name="boxes_qty[]" class="hidden-boxes-qty" value="0">
                        <input type="hidden" name="loose_qty[]" class="hidden-loose-qty" value="0">
                        <input type="hidden" name="length[]" class="hidden-length">
                        <input type="hidden" name="width[]" class="hidden-width">
                        <input type="hidden" name="color[]" class="hidden-variant-data">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-info fw-bold unit-toggle-btn py-0 px-2" data-unit="Pcs" title="Click to toggle unit (Carton ↔ Pcs / Kg ↔ Gm)" style="font-size:0.75rem; min-width: 55px; cursor: pointer;">Pcs</button>
                        <input type="hidden" name="unit[]" class="unit-input-val" value="Pcs">
                    </td>
                    <td>
                        <input type="number" step="any" min="0.01" name="qty[]" class="form-control text-center main-qty-input" value="1" placeholder="Qty">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" name="price[]" class="form-control text-end price" value="0">
                        </div>
                    </td>
                    <td>
                        <input type="number" step="0.01" name="item_discount[]" class="form-control text-end item-disc-percent" value="0">
                    </td>
                    <td>
                        <input type="number" class="form-control text-end input-readonly item-disc-amt" value="0.00" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control text-end input-readonly row-total" value="0.00" readonly>
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row border-0"><i class="bi bi-x-lg"></i></button>
                    </td>
                </tr>`;
                const $row = $(html);
                $('#purchaseTableBody').append($row);
                initProductSelect2($row.find('.product-select2'));
                recalcRow($row);
                recalcAll();
            };

            // Remove Row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                recalcAll();
            });

            // Inputs -> Calc
            $('#purchaseTableBody').on('input', '.main-qty-input, .price, .item-disc-percent', function() {
                recalcRow($(this).closest('tr'));
                recalcAll();
            });

            $('#billDiscount, #billDiscountPct, #extraCost').on('input', function() {
                recalcAll();
            });

            function normalizeDiscountInput() {
                let totalInlineDiscount = 0;
                $('#purchaseTableBody tr').each(function() {
                    const rowDiscAmt = parseFloat($(this).find('.item-disc-amt').val()) || 0;
                    totalInlineDiscount += rowDiscAmt;
                });

                let billDiscVal = parseFloat($('#billDiscount').val());
                if (isNaN(billDiscVal) || billDiscVal < totalInlineDiscount) {
                    $('#billDiscount').val(totalInlineDiscount.toFixed(2));
                }
                recalcAll();
            }

            $('#billDiscount, #billDiscountPct').on('blur', function() {
                normalizeDiscountInput();
            });

            $('#purchaseForm').on('submit', function() {
                normalizeDiscountInput();
            });

            // --- Payment Section Logic ---
            $('#btnAddPayment').on('click', function() {
                const row = `
                <div class="d-flex gap-2 align-items-center mb-2 payment-row flex-wrap">
                    <select class="form-select rv-account" name="payment_account_id[]" style="max-width: 300px; flex-grow: 1;">
                        <option value="" selected disabled>Select Account</option>
                        @foreach ($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->title }}</option>
                        @endforeach
                    </select>
                    <input type="number" class="form-control text-end payment-amount" name="payment_amount[]" placeholder="Amount" style="width:140px" step="0.01">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-payment">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>`;
                $('#paymentWrapper').append(row);
            });

            $(document).on('click', '.remove-payment', function() {
                $(this).closest('.payment-row').remove();
                recalcPayments();
            });

            $(document).on('input', '.payment-amount', function() {
                recalcPayments();
            });

            function recalcPayments() {
                let total = 0;
                $('.payment-amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#totalPaid').text(total.toFixed(2));
            }

            function recalcRow($row) {
                const qtyStr = ($row.find('.main-qty-input').val() || '').toString();
                const qty = parseFloat(qtyStr) || 0;
                const price = parseFloat($row.find('.price').val()) || 0;
                const discPct = parseFloat($row.find('.item-disc-percent').val()) || 0;
                const sizeMode = $row.data('sizemode') || $row.find('.hidden-size-mode').val();
                const unitVal = ($row.find('.unit-input-val').val() || '').toLowerCase();
                const pieces_per_m2 = parseFloat($row.data('pieces_per_m2')) || parseFloat($row.find('.hidden-pieces-per-m2').val()) || 0;

                const ppb = parseFloat($row.find('.hidden-pieces-per-box').val()) || parseFloat($row.data('pieces_per_box')) || 1;

                let gross = 0;
                const isCarton = (unitVal === 'carton' || unitVal === 'ctn' || unitVal === 'box' || sizeMode === 'by_cartons');

                if (sizeMode === 'by_size') {
                    gross = (pieces_per_m2 || 1) * qty * price;
                } else if (unitVal === 'gm' || unitVal === 'g') {
                    gross = (qty / 1000.0) * price;
                } else if (isCarton) {
                    let s = qtyStr.trim();
                    if (s.startsWith('.')) s = '0' + s;
                    if (s.includes('.')) {
                        const parts = s.split('.');
                        const boxes = parseInt(parts[0]) || 0;
                        const loose = parseInt(parts[1]) || 0;
                        const piecePrice = ppb > 0 ? (price / ppb) : price;
                        gross = (boxes * price) + (loose * piecePrice);
                        $row.find('.hidden-boxes-qty').val(boxes);
                        $row.find('.hidden-loose-qty').val(loose);
                    } else {
                        const boxes = parseInt(s) || 0;
                        gross = boxes * price;
                        $row.find('.hidden-boxes-qty').val(boxes);
                        $row.find('.hidden-loose-qty').val(0);
                    }
                } else {
                    gross = qty * price;
                    if (unitVal === 'pcs' || unitVal === 'pc' || unitVal === 'piece') {
                        const bQty = ppb > 0 ? (qty / ppb) : qty;
                        $row.find('.hidden-boxes-qty').val(bQty.toFixed(2));
                        $row.find('.hidden-loose-qty').val(qty);
                    }
                }

                const discAmt = gross * (discPct / 100);
                const lineTotal = Math.max(0, gross - discAmt);

                $row.find('.item-disc-amt').val(discAmt.toFixed(2));
                $row.find('.row-total').val(lineTotal.toFixed(2));
            }

            function recalcAll() {
                let totalQty = 0;
                let subtotal = 0;
                let totalInlineDiscount = 0;

                $('#purchaseTableBody tr').each(function() {
                    const qty = parseFloat($(this).find('.main-qty-input').val()) || 0;
                    const total = parseFloat($(this).find('.row-total').val()) || 0;
                    const rowDiscAmt = parseFloat($(this).find('.item-disc-amt').val()) || 0;

                    totalQty += qty;
                    subtotal += total;
                    totalInlineDiscount += rowDiscAmt;
                });

                const grossSubtotal = subtotal + totalInlineDiscount;

                $('#tQty').text(totalQty.toFixed(2));
                $('#tSub').text(subtotal.toFixed(2));
                $('#subtotalInput').val(subtotal.toFixed(2));
                $('#totalAmount').text(subtotal.toFixed(2));

                let additionalDiscount = parseFloat($('#discountInput').val()) || 0;
                let billDiscVal = parseFloat($('#billDiscount').val());

                if ($(document.activeElement).is('#billDiscount') || $(document.activeElement).is('#billDiscountPct')) {
                    if ($(document.activeElement).is('#billDiscountPct')) {
                        const pct = parseFloat($('#billDiscountPct').val()) || 0;
                        billDiscVal = grossSubtotal * (pct / 100);
                        $('#billDiscount').val(billDiscVal.toFixed(2));
                    }
                    if (!isNaN(billDiscVal)) {
                        additionalDiscount = Math.max(0, billDiscVal - totalInlineDiscount);
                    } else {
                        additionalDiscount = 0;
                    }
                } else {
                    billDiscVal = totalInlineDiscount + additionalDiscount;
                    $('#billDiscount').val(billDiscVal.toFixed(2));
                }
                
                const pct = grossSubtotal > 0 ? (billDiscVal / grossSubtotal) * 100 : 0;
                $('#billDiscountPct').val(pct.toFixed(2));

                $('#discountInput').val(additionalDiscount.toFixed(2));

                const extraCost = parseFloat($('#extraCost').val()) || 0;

                const net = subtotal - additionalDiscount + extraCost;

                $('#tPayable').text(net.toFixed(2));
                $('#netAmountInput').val(net.toFixed(2));
            }

            function initProductSelect2($el) {
                $el.select2({
                    placeholder: 'Search Product (Name / SKU / Barcode / Variant)...',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: '{{ route('products.ajax.search') }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term,
                                page: params.page || 1
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.results || [],
                                pagination: {
                                    more: (data.pagination && data.pagination.more) ? true : false
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 0,
                    templateResult: formatProduct,
                    templateSelection: formatSelection
                });

                $el.on('select2:select', function(e) {
                    const data = e.params.data;
                    const $row = $(this).closest('tr');

                    let unitName = data.unit_name || 'Pcs';
                    const ppb = parseFloat(data.pieces_per_box || data.ppb) || 1;
                    const isCartonMode = (data.size_mode === 'by_cartons' || unitName.toLowerCase() === 'carton' || unitName.toLowerCase() === 'ctn' || ppb > 1);

                    // Dynamic Unit & Style
                    if (isCartonMode) {
                        unitName = 'Carton';
                        $row.find('.unit-toggle-btn')
                            .removeClass('btn-outline-primary btn-outline-info')
                            .addClass('btn-outline-success')
                            .attr('data-unit', 'Carton')
                            .text('Carton');
                        $row.find('.unit-input-val').val('Carton');
                    } else if (data.size_mode === 'by_kg' || data.size_mode === 'by_gm') {
                        unitName = 'Kg';
                        $row.find('.unit-toggle-btn')
                            .removeClass('btn-outline-info btn-outline-success')
                            .addClass('btn-outline-primary')
                            .attr('data-unit', 'Kg')
                            .text('Kg');
                        $row.find('.unit-input-val').val('Kg');
                    } else {
                        $row.find('.unit-toggle-btn')
                            .removeClass('btn-outline-primary btn-outline-success')
                            .addClass('btn-outline-info')
                            .attr('data-unit', unitName)
                            .text(unitName);
                        $row.find('.unit-input-val').val(unitName);
                    }

                    // Variant Info Display (Size, Color)
                    let variantBadgeText = '';
                    if (data.variant_data) {
                        try {
                            const vObj = JSON.parse(atob(data.variant_data));
                            const parts = [];
                            if (vObj.size && vObj.size !== '-') parts.push('Size: ' + vObj.size);
                            if (vObj.color && vObj.color !== '-') parts.push('Color: ' + vObj.color);
                            if (parts.length > 0) {
                                variantBadgeText = parts.join(' | ');
                            }
                        } catch (err) {}
                    }
                    
                    if (variantBadgeText) {
                        $row.find('.variant-badge').text(variantBadgeText);
                        $row.find('.variant-badge-wrapper').removeClass('d-none');
                    } else {
                        $row.find('.variant-badge-wrapper').addClass('d-none');
                    }

                    // Prices
                    const pPiece = parseFloat(data.purchase_price_per_piece) || parseFloat(data.trade_price) || 0;
                    const pBox = parseFloat(data.purchase_price_per_box) || (pPiece * ppb);
                    const pM2 = parseFloat(data.purchase_price_per_m2) || 0;
                    const sizeMode = data.size_mode || 'std';

                    // Populate Snapshots
                    $row.find('.hidden-size-mode').val(data.size_mode || '');
                    $row.find('.hidden-pieces-per-box').val(ppb);
                    $row.find('.hidden-pieces-per-m2').val(data.pieces_per_m2 || 0);
                    $row.find('.hidden-price-per-carton').val(pBox);
                    $row.find('.hidden-length').val(data.length || '');
                    $row.find('.hidden-width').val(data.width || '');
                    $row.find('.hidden-variant-data').val(data.variant_data || '');

                    // Set default discount
                    $row.find('.item-disc-percent').val(data.purchase_discount_percent || 0);

                    // Set Price based on unit mode
                    let finalPrice = pPiece;
                    if (sizeMode === 'by_size') {
                        finalPrice = pM2;
                    } else if (isCartonMode) {
                        finalPrice = pBox > 0 ? pBox : (pPiece * ppb);
                    } else {
                        finalPrice = pPiece;
                    }

                    $row.find('.price').val(finalPrice % 1 === 0 ? finalPrice : finalPrice.toFixed(2));

                    // Data Attributes
                    $row.data('sizemode', sizeMode);
                    $row.data('pieces_per_m2', Number(data.pieces_per_m2) || 0);
                    $row.data('p_price_piece', pPiece);
                    $row.data('p_price_box', pBox);
                    $row.data('pieces_per_box', ppb);

                    // Qty default
                    let curQty = parseFloat($row.find('.main-qty-input').val()) || 0;
                    if (curQty <= 0) {
                        $row.find('.main-qty-input').val(1);
                    }

                    $row.find('.main-qty-input').focus().select();
                    recalcRow($row);
                    recalcAll();
                });
            }

            function formatProduct(repo) {
                if (repo.loading) return repo.text;
                let stock = repo.stock !== undefined ? repo.stock : 0;
                let sku = repo.sku || 'N/A';
                let unit = repo.unit_name || 'Pcs';
                let stockVal = parseFloat(repo.stock_pieces !== undefined ? repo.stock_pieces : repo.stock) || 0;
                let badgeClass = stockVal > 0 ? 'bg-success' : 'bg-secondary';
                let buyPrice = parseFloat(repo.purchase_price_per_piece || repo.trade_price || 0);

                return $(`
                <div class="clearfix py-1">
                    <div class="float-start">
                        <div class="fw-bold text-dark">${repo.name || repo.text}</div>
                        <small class="text-muted">SKU: ${sku} | Unit: ${unit} | Buy Price: Rs. ${buyPrice.toFixed(2)}</small>
                    </div>
                    <div class="float-end">
                        <span class="badge ${badgeClass} rounded-pill">Stock: ${stock}</span>
                    </div>
                </div>`);
            }

            function formatSelection(repo) {
                return repo.name || repo.text;
            }
        });
    </script>
@endsection
