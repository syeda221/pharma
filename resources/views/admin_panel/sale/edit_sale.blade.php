@extends('admin_panel.layout.app')

@section('content')
    <!-- Loader Overlay -->
    <div id="pageLoader"
        class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column gap-3 justify-content-center align-items-center"
        style="background: rgba(255,255,255,0.9); z-index: 1055;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="fw-bold text-primary fs-5">Loading Sale Data...</div>
    </div>
    <link href="{{ asset('assets/vendors/bootstrap5/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        /* ================= MODERN PROFESSIONAL POS & ERP UI ================= */
        :root {
            --pos-bg: #f8fafc;
            --pos-card-bg: #ffffff;
            --pos-border: #e2e8f0;
            --pos-border-focus: #3b82f6;
            --pos-primary: #2563eb;
            --pos-primary-hover: #1d4ed8;
            --pos-success: #10b981;
            --pos-success-hover: #059669;
            --pos-danger: #ef4444;
            --pos-text-main: #0f172a;
            --pos-text-muted: #64748b;
            --pos-radius: 8px;
            --pos-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.04);
            --pos-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--pos-bg) !important;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            color: var(--pos-text-main) !important;
            -webkit-font-smoothing: antialiased;
        }

        .main-container {
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            box-shadow: var(--pos-shadow) !important;
            background-color: var(--pos-card-bg) !important;
            padding: 10px !important;
            max-width: 100%;
        }

        /* Modern Top Information Card */
        .top-info-card {
            background-color: #f8fafc !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 10px 12px !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }

        .meta-label {
            font-size: 0.7rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            color: #475569 !important;
            margin-bottom: 4px !important;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .card-panel {
            background-color: #ffffff !important;
            border: 1px solid var(--pos-border) !important;
            border-radius: var(--pos-radius) !important;
            padding: 10px !important;
            box-shadow: var(--pos-shadow) !important;
        }

        /* Section Header Titles */
        .section-header-title {
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            color: var(--pos-text-main) !important;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Uniform Form Inputs in Top Bar */
        .form-control,
        .form-select {
            border: 1px solid var(--pos-border) !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            font-weight: 500 !important;
            color: var(--pos-text-main) !important;
            background-color: #ffffff !important;
            transition: all 0.15s ease-in-out !important;
            height: 32px !important;
            font-size: 0.8rem !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pos-border-focus) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
            outline: none !important;
            background-color: #ffffff !important;
        }

        .input-readonly {
            background-color: #f1f5f9 !important;
            border-color: var(--pos-border) !important;
            color: #475569 !important;
            font-weight: 600 !important;
            cursor: not-allowed !important;
        }

        /* Invoice Series Input Group */
        .invoice-group .btn-prefix {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
            border: 1px solid #0284c7 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-top-left-radius: 6px !important;
            border-bottom-left-radius: 6px !important;
            height: 32px !important;
            padding: 0 10px !important;
            font-size: 0.78rem !important;
        }

        .invoice-group .btn-refresh {
            background: #f1f5f9 !important;
            border: 1px solid var(--pos-border) !important;
            border-left: none !important;
            color: #475569 !important;
            border-top-right-radius: 6px !important;
            border-bottom-right-radius: 6px !important;
            height: 32px !important;
            padding: 0 10px !important;
            transition: all 0.15s;
        }
        .invoice-group .btn-refresh:hover {
            background: #e2e8f0 !important;
            color: var(--pos-primary) !important;
        }

        /* Top Save Sale Button */
        .btn-top-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            height: 32px !important;
            padding: 0 12px !important;
            font-size: 0.8rem !important;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }
        .btn-top-save:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
            color: #ffffff !important;
        }

        /* ================= TRANSACTION GRID / TABLE ================= */
        .table-responsive {
            border: 1px solid var(--pos-border) !important;
            border-radius: 6px !important;
            overflow-x: auto !important;
            overflow-y: visible !important;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
            min-height: 240px;
            background-color: #ffffff;
        }

        .sales-table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100%;
            margin-bottom: 0 !important;
        }

        .sales-table thead th {
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            font-weight: 700 !important;
            font-size: 0.72rem !important;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 8px 6px !important;
            border-bottom: 2px solid var(--pos-border) !important;
            border-top: none !important;
            border-right: 1px solid var(--pos-border) !important;
            border-left: none !important;
            vertical-align: middle !important;
            text-align: center;
        }
        .sales-table thead th:last-child {
            border-right: none !important;
        }

        .sales-table tbody tr {
            transition: background-color 0.1s;
        }
        .sales-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .sales-table tbody td {
            border-bottom: 1px solid var(--pos-border) !important;
            border-right: 1px solid var(--pos-border) !important;
            padding: 0 !important;
            vertical-align: middle !important;
            background-color: transparent;
        }
        .sales-table tbody td:last-child {
            border-right: none !important;
        }

        /* Clean Flat Inputs inside Grid */
        .sales-table tbody .form-control,
        .sales-table tbody .form-select {
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            height: 30px !important;
            padding: 2px 6px !important;
            width: 100% !important;
            background-color: transparent !important;
            font-size: 0.8rem !important;
            color: var(--pos-text-main) !important;
        }

        .sales-table tbody .form-control:focus,
        .sales-table tbody .form-select:focus {
            outline: none !important;
            background-color: #eff6ff !important;
            box-shadow: inset 0 0 0 1.5px var(--pos-primary) !important;
        }

        .sales-table tbody .input-readonly {
            background-color: #f8fafc !important;
            color: #475569 !important;
        }

        /* Select2 Flat in Table */
        .sales-table tbody .select2-container--default .select2-selection--single {
            height: 30px !important;
            border: none !important;
            border-radius: 0 !important;
            background-color: transparent !important;
            padding: 0 !important;
        }
        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            padding-left: 6px !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            color: #1e293b !important;
        }
        .sales-table tbody .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
            right: 4px !important;
        }

        /* Discount Input & Toggle */
        .discount-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .discount-wrapper .discount-value {
            padding-right: 28px !important;
        }
        .discount-wrapper .discount-toggle {
            position: absolute;
            right: 2px;
            top: 50%;
            transform: translateY(-50%);
            height: 22px;
            width: 24px;
            padding: 0 !important;
            font-size: 0.65rem !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .discount-wrapper .discount-toggle:hover {
            background-color: #e2e8f0;
            color: var(--pos-primary);
        }

        /* Action Delete Row Button */
        .btn-del-row {
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.1rem;
            width: 100%;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            cursor: pointer;
        }
        .btn-del-row:hover {
            color: var(--pos-danger);
            background-color: #fef2f2;
        }

        /* Price Mode Toggle Button */
        .price-mode-row-toggle {
            font-size: 0.65rem !important;
            height: 24px !important;
            min-width: 22px !important;
            padding: 0 4px !important;
            font-weight: 700 !important;
            border-radius: 4px !important;
        }

        /* ================= RIGHT SIDEBAR: SUMMARY & CHECKOUT ================= */
        .summary-card {
            background-color: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 12px;
            box-shadow: var(--pos-shadow);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 0.8rem;
        }

        .summary-row.border-top {
            border-top: 1px dashed var(--pos-border) !important;
            margin-top: 4px;
            padding-top: 6px;
        }

        .summary-val-net {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--pos-primary);
            font-family: 'JetBrains Mono', monospace;
        }

        .summary-val-change {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--pos-danger);
            font-family: 'JetBrains Mono', monospace;
        }

        /* Payment Methods Card */
        .payment-methods-card {
            background-color: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 12px;
            box-shadow: var(--pos-shadow);
        }

        .btn-save-complete {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);
            transition: all 0.2s ease-in-out;
        }
        .btn-save-complete:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.35);
            color: #ffffff !important;
        }

        /* Bottom Sticky Summary Strip */
        .bottom-summary-strip {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: var(--pos-radius);
            padding: 8px 14px;
            margin-top: 10px;
            box-shadow: var(--pos-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Direct Add Product Sidebar Card */
        .pos-product-card {
            background: #ffffff;
            border: 1px solid var(--pos-border);
            border-radius: 6px;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            transition: all 0.15s;
        }
        .pos-product-card:hover {
            border-color: var(--pos-border-focus);
            box-shadow: var(--pos-shadow);
            background: #f8fafc;
        }
        .pos-product-img {
            width: 34px;
            height: 34px;
            background: #f1f5f9;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pos-product-info {
            flex: 1;
            min-width: 0;
        }
        .pos-product-name {
            font-size: 0.76rem;
            font-weight: 700;
            color: var(--pos-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pos-product-sub {
            font-size: 0.68rem;
            color: var(--pos-text-muted);
        }
        .pos-product-price {
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--pos-primary);
            text-align: right;
        }
        .pos-product-add-btn {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: var(--pos-primary);
            border-radius: 4px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .pos-product-add-btn:hover {
            background: var(--pos-primary);
            color: #ffffff;
            border-color: var(--pos-primary);
        }

        .badge-stock-green {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            font-weight: 700 !important;
            border: 1px solid #bbf7d0 !important;
            padding: 2px 6px !important;
            border-radius: 4px !important;
            font-size: 0.7rem !important;
        }
    </style>

    <div class="container-fluid py-2 px-2">
        <div class="main-container bg-white border mx-auto p-3 rounded-3">

            <div id="alertBox" class="alert d-none mb-2" role="alert" style="padding:6px 12px; font-size:0.8rem;"></div>

            <form id="saleForm" autocomplete="off">
                @csrf
                <input type="hidden" id="booking_id" name="booking_id" value="{{ $sale->id }}">
                <input type="hidden" id="action" name="action" value="sale">
                <input type="hidden" name="cash" value="{{ $sale->cash ?? 0 }}">
                <input type="hidden" id="totalBalance" value="{{ $sale->total_net ?? 0 }}">

                {{-- TOP HEADER BAR --}}
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('sale.index') }}" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Back"><i class="fas fa-arrow-left text-secondary"></i></a>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1.05rem;">
                                <i class="fas fa-edit text-primary"></i> Edit Sale #{{ $sale->invoice_no }}
                            </h5>
                            <small class="text-muted" style="font-size: 0.72rem;">Update invoice details & order items</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem;">Created: {{ $sale->created_at ? $sale->created_at->format('d/m/Y H:i') : '--' }}</span>
                        <button type="button" class="btn btn-sm btn-light border rounded-2 text-secondary px-2 py-1" title="Calculator"><i class="fas fa-calculator"></i></button>
                        <button type="button" class="btn btn-sm btn-light border rounded-2 text-secondary px-2 py-1" title="Fullscreen" onclick="document.documentElement.requestFullscreen()"><i class="fas fa-expand"></i></button>
                    </div>
                </div>

                <!-- TOP INFORMATION PANEL -->
                <div class="top-info-card mb-3">
                    <div class="row g-2 align-items-end w-100 m-0">
                        <!-- Invoice No -->
                        <div class="col-sm-6 col-md-3 col-lg-2">
                            <label class="meta-label"><i class="fas fa-receipt text-primary"></i> Invoice No.</label>
                            <input type="text" class="form-control text-center fw-bold input-readonly" name="Invoice_no" id="inputInvoiceNo" value="{{ $sale->invoice_no }}" readonly style="font-family: 'JetBrains Mono', monospace; font-size: 0.8rem;">
                        </div>

                        <!-- Date -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <label class="meta-label"><i class="far fa-calendar-alt text-primary"></i> Date</label>
                            <input type="text" name="sale_date" class="form-control datepicker-custom text-center fw-bold" id="displayDateInput" value="{{ $sale->created_at ? $sale->created_at->format('Y-m-d') : date('Y-m-d') }}">
                        </div>

                        <!-- Cr. Days -->
                        <div class="col-sm-6 col-md-1 col-lg-1">
                            <label class="meta-label"><i class="fas fa-clock text-muted"></i> Cr. Days</label>
                            <input type="number" class="form-control text-center fw-bold" name="credit_days" placeholder="0" min="0" value="{{ $sale->credit_days ?? '0' }}">
                        </div>

                        <!-- Remarks -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <label class="meta-label"><i class="far fa-comment-dots text-muted"></i> Remarks / M.Bill</label>
                            <input type="text" class="form-control" name="reference" id="remarks" value="{{ $sale->reference ?? '' }}" placeholder="Notes / Ref...">
                        </div>

                        <!-- Customer Type -->
                        <div class="col-sm-6 col-md-2 col-lg-2" id="customerTypeCol">
                            <label class="meta-label"><i class="fas fa-user-tag text-primary"></i> Customer Type</label>
                            <select class="form-select fw-bold" id="partyTypeSelect" name="partyType">
                                @foreach(\App\Models\CustomerType::orderBy('name')->get() as $type)
                                    <option value="{{ $type->name }}" {{ $type->name === ($sale->walkin_name ? 'Walking Customer' : (optional($sale->customer_relation)->customer_type ?? 'Main Customer')) ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer & Walk-in Toggle -->
                        <div class="col-sm-6 col-md-2 col-lg-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="meta-label mb-0"><i class="fas fa-user text-primary"></i> Customer</label>
                                <button type="button" id="btnOpenAddCustomerModal" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-bold btn-open-customer-modal" data-toggle="modal" data-target="#addCustomerModal" data-bs-toggle="modal" data-bs-target="#addCustomerModal" title="Quick Add Customer (Alt+C or F2)" style="font-size: 0.7rem; height: 20px; line-height: 1;">
                                    <i class="fas fa-user-plus"></i> Quick Customer
                                </button>
                            </div>
                            <div id="customerInputWrapper">
                                <input type="text" class="form-control fw-bold {{ $sale->walkin_name ? '' : 'd-none' }}" name="walkin_name" id="walkinNameInput" value="{{ $sale->walkin_name ?? 'Walk-in Customer' }}" placeholder="Enter Walk-in Name...">
                                <select class="form-select {{ $sale->walkin_name ? 'd-none' : '' }}" id="customerSelect" name="customer" style="width:100%">
                                    @if($sale->customer_relation)
                                        <option value="{{ $sale->customer_id }}" selected>{{ $sale->customer_relation->customer_id }} - {{ $sale->customer_relation->customer_name }}</option>
                                    @else
                                        <option value=""></option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Save Sale Button -->
                        <div class="col-sm-12 col-md-12 col-lg-1 d-flex align-items-end">
                            <input type="hidden" name="is_walkin" id="is_walkin" value="{{ $sale->walkin_name ? '1' : '0' }}">
                            <button type="button" class="btn btn-top-save w-100 fw-bold d-flex align-items-center justify-content-center gap-1" id="btnHeaderSaveSale" style="font-size: 0.75rem;">
                                <i class="fas fa-check"></i> Save
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Hidden fields for backend --}}
                <input type="hidden" id="address" name="address" value="{{ optional($sale->customer_relation)->address }}">
                <input type="hidden" id="tel" name="tel" value="{{ optional($sale->customer_relation)->mobile }}">
                <input type="hidden" id="previousBalance" value="{{ optional($sale->customer_relation)->previous_balance ?? 0 }}">
                <input type="hidden" id="rangeBalance" value="{{ optional($sale->customer_relation)->balance_range ?? 0 }}">

                <!-- 2-COLUMN RESPONSIVE POS LAYOUT -->
                <div class="row g-3 align-items-stretch">
                    <!-- LEFT MAIN AREA: Items Grid Table (col-lg-8 col-xl-9) -->
                    <div class="col-lg-8 col-xl-9">
                        <div class="card-panel d-flex flex-column h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="section-header-title">
                                        <i class="fas fa-list-check text-primary"></i> Order Items
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size:0.7rem;" id="itemsRowCount">{{ count($sale->items ?? []) }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-2 fw-semibold d-flex align-items-center gap-1" data-bs-toggle="offcanvas" data-bs-target="#quickProductsOffcanvas" style="font-size:0.75rem;">
                                        <i class="fas fa-th"></i> Quick Products
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm py-1 px-3 rounded-2 fw-bold d-flex align-items-center gap-1 shadow-sm" id="btnAdd" style="font-size:0.75rem;">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive flex-grow-1">
                                <table class="table table-bordered sales-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:30px;" class="text-center">#</th>
                                            <th class="col-product" style="min-width: 160px;">PRODUCT</th>
                                            <th class="col-stock" style="width: 60px;">STOCK</th>
                                            <th class="col-qty" style="width: 85px;">QTY</th>
                                            <th class="col-size" style="width: 55px;">SIZE</th>
                                            <th class="col-color" style="width: 65px;">BONUS</th>
                                            <th class="col-pieces" style="width: 55px;">PCS</th>
                                            <th class="col-price-p" style="width: 85px;">PRICE</th>
                                            <th class="col-disc" style="width: 85px;">DISCOUNT</th>
                                            <th class="col-amount" style="width: 95px;">AMOUNT</th>
                                            <th class="col-action" style="width: 34px;">Ã—</th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesTableBody">
                                        @if(isset($sale) && $sale->items && count($sale->items) > 0)
                                            @foreach ($sale->items as $index => $item)
                                                @php
                                                    $prod = $item->product;
                                                    $sizeMode = $item->size_mode ?? ($prod->size_mode ?? 'std');

                                                    $variantData = [];
                                                    if ($item->color) {
                                                        try {
                                                            $b64 = base64_decode($item->color, true);
                                                            $variantData = ($b64 !== false) ? json_decode($b64, true) : json_decode($item->color, true);
                                                            if (!is_array($variantData)) $variantData = [];
                                                        } catch (\Exception $e) {
                                                            $variantData = [];
                                                        }
                                                    }

                                                    // Check live product variant data for updated pack size / conversion factor
                                                    $liveVariant = null;
                                                    if ($prod && !empty($prod->color)) {
                                                        $prodVariants = json_decode($prod->color, true);
                                                        if (is_array($prodVariants)) {
                                                            if (!empty($variantData['barcode'])) {
                                                                $liveVariant = collect($prodVariants)->firstWhere('barcode', $variantData['barcode']);
                                                            }
                                                            if (!$liveVariant && !empty($variantData['name'])) {
                                                                $liveVariant = collect($prodVariants)->first(function($v) use ($variantData) {
                                                                    $n1 = strtolower(trim($v['name'] ?? ''));
                                                                    $n2 = strtolower(trim($variantData['name'] ?? ''));
                                                                    return $n1 === $n2 || ($n1 && $n2 && (str_contains($n1, $n2) || str_contains($n2, $n1)));
                                                                });
                                                            }
                                                            if (!$liveVariant && !empty($variantData['size']) && $variantData['size'] !== '-') {
                                                                $liveVariant = collect($prodVariants)->first(function($v) use ($variantData) {
                                                                    $s1 = strtolower(trim($v['size'] ?? ''));
                                                                    $s2 = strtolower(trim($variantData['size'] ?? ''));
                                                                    return $s1 === $s2 || ($s1 && $s2 && (str_starts_with($s1, $s2) || str_starts_with($s2, $s1)));
                                                                });
                                                            }
                                                            if (!$liveVariant && count($prodVariants) === 1) {
                                                                $liveVariant = $prodVariants[0];
                                                            }
                                                        }
                                                    }

                                                    $ppb = 1;
                                                    if ($liveVariant && !empty($liveVariant['conv_factor']) && (float)$liveVariant['conv_factor'] > 0) {
                                                        $ppb = (float)$liveVariant['conv_factor'];
                                                    } elseif ($liveVariant && !empty($liveVariant['pieces_per_box']) && (float)$liveVariant['pieces_per_box'] > 0) {
                                                        $ppb = (float)$liveVariant['pieces_per_box'];
                                                    } elseif ($prod && (float)$prod->pieces_per_box > 0) {
                                                        $ppb = (float)$prod->pieces_per_box;
                                                    } elseif (!empty($variantData['conv_factor']) && (float)$variantData['conv_factor'] > 0) {
                                                        $ppb = (float)$variantData['conv_factor'];
                                                    } elseif (!empty($variantData['pieces_per_box']) && (float)$variantData['pieces_per_box'] > 0) {
                                                        $ppb = (float)$variantData['pieces_per_box'];
                                                    } elseif (!empty($item->pieces_per_box) && (float)$item->pieces_per_box > 0) {
                                                        $ppb = (float)$item->pieces_per_box;
                                                    }

                                                    if ($ppb <= 0) $ppb = 1;

                                                    $cartons = 0;
                                                    $loose = 0;
                                                    if ($ppb > 0) {
                                                        $cartons = floor($item->total_pieces / $ppb);
                                                        $loose = $item->total_pieces % $ppb;
                                                    } else {
                                                        $loose = $item->total_pieces;
                                                    }

                                                    // Determine Sub-Unit Mode and Toggle Button configuration for Edit
                                                    $unitMode = 'main';
                                                    $toggleText = 'Kg';
                                                    $toggleBtnClass = 'd-none';
                                                    $displayQty = $cartons;
                                                    $itemTotalPieces = (float)$item->total_pieces;
                                                    $isPcs = false;

                                                    if ($sizeMode === 'by_cartons') {
                                                        $variantUnit = strtolower($variantData['unit'] ?? ($liveVariant['unit'] ?? ''));
                                                        if ($variantUnit === 'pcs' || $variantUnit === 'piece' || $variantUnit === 'pieces') {
                                                            $isPcs = true;
                                                        }

                                                        if ($isPcs) {
                                                            $unitMode = 'pcs';
                                                            $toggleText = 'Pcs';
                                                            $toggleBtnClass = 'btn-outline-info';
                                                            $displayQty = (float) $item->total_pieces;
                                                            $itemTotalPieces = (float) $item->total_pieces;
                                                        } else {
                                                            $unitMode = 'ctn';
                                                            $toggleText = 'Ctn';
                                                            $toggleBtnClass = 'btn-outline-success';
                                                            // In Carton dot notation: e.g. 1 carton + 6 loose pieces is '1.6'
                                                            $displayQty = $loose > 0 ? "{$cartons}.{$loose}" : $cartons;
                                                            $itemTotalPieces = ($cartons * $ppb) + $loose;
                                                        }
                                                    } elseif ($sizeMode === 'by_kg') {
                                                        $unitMode = 'kg';
                                                        $toggleText = 'Kg';
                                                        $toggleBtnClass = 'btn-outline-primary';
                                                        $displayQty = (float) ($item->qty ?: $item->total_pieces);
                                                    } elseif ($sizeMode === 'by_gm') {
                                                        $unitMode = 'gm';
                                                        $toggleText = 'Gm';
                                                        $toggleBtnClass = 'btn-outline-info';
                                                        $displayQty = (float) ($item->qty ?: $item->total_pieces);
                                                    } elseif ($sizeMode === 'by_feet') {
                                                        $unitMode = 'ft';
                                                        $toggleText = 'Ft';
                                                        $toggleBtnClass = 'btn-outline-primary';
                                                        $displayQty = (float) ($item->qty ?: $item->total_pieces);
                                                    } elseif ($sizeMode === 'by_meter') {
                                                        $unitMode = 'm';
                                                        $toggleText = 'Mtr';
                                                        $toggleBtnClass = 'btn-outline-primary';
                                                        $displayQty = (float) ($item->qty ?: $item->total_pieces);
                                                    } else {
                                                        $displayQty = (float) ($item->qty ?: $item->total_pieces);
                                                    }

                                                    $pieceRetailPrice = 0;
                                                    if ($liveVariant && !empty($liveVariant['sale_price']) && (float)$liveVariant['sale_price'] > 0) {
                                                        $pieceRetailPrice = (float) $liveVariant['sale_price'];
                                                    } elseif (!empty($variantData['sale_price']) && (float)$variantData['sale_price'] > 0) {
                                                        $pieceRetailPrice = (float) $variantData['sale_price'];
                                                    } elseif ($prod && (float) $prod->sale_price_per_piece > 0) {
                                                        $pieceRetailPrice = (float) $prod->sale_price_per_piece;
                                                    } elseif ($item && (float) $item->price > 0) {
                                                        $pieceRetailPrice = ($sizeMode === 'by_cartons' && !$isPcs && $ppb > 0) ? ((float)$item->price / $ppb) : (float)$item->price;
                                                    }

                                                    $pieceWholesalePrice = 0;
                                                    if ($liveVariant && !empty($liveVariant['wholesale_price']) && (float)$liveVariant['wholesale_price'] > 0) {
                                                        $pieceWholesalePrice = (float) $liveVariant['wholesale_price'];
                                                    } elseif (!empty($variantData['wholesale_price']) && (float)$variantData['wholesale_price'] > 0) {
                                                        $pieceWholesalePrice = (float) $variantData['wholesale_price'];
                                                    } elseif ($prod && (float) $prod->wholesale_price > 0) {
                                                        $pieceWholesalePrice = (float) $prod->wholesale_price;
                                                    }

                                                    $selStockDisp = '';
                                                    if (!empty($variantData['current_stock'])) {
                                                        $selStockDisp = $variantData['current_stock'];
                                                    } elseif (isset($variantData['stock']) && $variantData['stock'] !== '') {
                                                        $selStockDisp = $variantData['stock'];
                                                    } elseif ($prod) {
                                                        $stk = 0;
                                                        if ($item->warehouse_id) {
                                                            $selWs = $prod->warehouseStocks?->where('warehouse_id', $item->warehouse_id)->first();
                                                            if ($selWs) {
                                                                $stk = (float) $selWs->total_pieces;
                                                                if ($stk <= 0 && $selWs->quantity > 0) {
                                                                    $stk = $selWs->quantity * $ppb;
                                                                }
                                                            }
                                                        }
                                                        if ($stk == 0 && $prod->warehouseStocks) {
                                                            $stk = (float) $prod->warehouseStocks->sum('total_pieces');
                                                        }

                                                        if (in_array($sizeMode, ['by_cartons', 'by_size']) && $ppb > 1) {
                                                            $b = floor($stk / $ppb);
                                                            $l = $stk % $ppb;
                                                            $selStockDisp = $l > 0 ? "$b.$l" : $b;
                                                        } elseif ($sizeMode === 'by_kg') {
                                                            if ($stk > 0 && $stk < 1) {
                                                                $gm = round($stk * 1000);
                                                                $selStockDisp = "{$stk} Kg ({$gm} Gm)";
                                                            } else {
                                                                $selStockDisp = "{$stk} Kg";
                                                            }
                                                        } else {
                                                            $selStockDisp = $stk;
                                                        }
                                                    }

                                                    $variantLabel = '';
                                                    $vSize = '-';
                                                    $vCol = '-';
                                                    if ($variantData && isset($variantData['name'])) {
                                                        $vSize = (isset($variantData['size']) && $variantData['size'] !== '-') ? $variantData['size'] : '-';
                                                        $vCol  = (isset($variantData['color']) && $variantData['color'] !== '-') ? $variantData['color'] : '-';
                                                        $sStr = $vSize !== '-' ? " {$vSize}" : '';
                                                        $cStr = $vCol !== '-' ? " ({$vCol})" : '';
                                                        $variantLabel = ' â€” ' . $variantData['name'] . $sStr . $cStr;
                                                    }
                                                @endphp
                                                <tr data-size_mode="{{ $sizeMode }}"
                                                    data-pieces_per_box="{{ $ppb }}"
                                                    data-price_per_m2="{{ $prod->price_per_m2 ?? 0 }}">
                                                    <!-- # ROW INDEX -->
                                                    <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">{{ $index + 1 }}</td>

                                                    <!-- PRODUCT -->
                                                    <td class="col-product">
                                                        <select class="form-select product" style="width:100%">
                                                            @if ($prod)
                                                                <option value="{{ $item->product_id }}" selected>
                                                                    {{ $prod->item_name }}{{ $variantLabel }}</option>
                                                            @endif
                                                        </select>
                                                        <input type="hidden" class="product-id-hidden" name="product_id[]" value="{{ $item->product_id }}">
                                                        <input type="hidden" class="variant-data-hidden" name="color[]" value="{{ $item->color ?? '' }}">
                                                        <input type="hidden" class="item-code-display" value="{{ $prod->item_code ?? '' }}">
                                                        <input type="hidden" class="size-h" value="{{ $prod->height ?? '-' }}">
                                                        <input type="hidden" class="size-w" value="{{ $prod->width ?? '-' }}">
                                                        <input type="hidden" class="size-mode-text" value="{{ $sizeMode }}">
                                                    </td>

                                                    <!-- STOCK -->
                                                    <td class="col-stock">
                                                        <input type="text"
                                                            class="form-control stock text-center input-readonly" readonly
                                                            value="{{ $selStockDisp }}" tabindex="-1">
                                                        <input type="hidden" class="warehouse" name="warehouse_id[]" value="{{ $item->warehouse_id ?? (auth()->user()->warehouse_id ?? 1) }}">
                                                        <input type="hidden" class="variant-stock-value" value="{{ $selStockDisp }}">
                                                    </td>

                                                    <!-- Qty cell with Sub-Unit toggle -->
                                                    <td style="width:85px;" class="col-qty-wrapper">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <input type="number" step="any" class="form-control carton-qty text-start fw-bold"
                                                                name="carton_qty[]" value="{{ $displayQty }}" placeholder="0" min="0" style="flex: 1; min-width: 0; padding-left: 6px;">
                                                            <button type="button" class="btn btn-sm {{ $toggleBtnClass }} qty-unit-toggle px-1 py-0 {{ $toggleBtnClass === 'd-none' ? 'd-none' : '' }}" 
                                                                    data-unit-mode="{{ $unitMode }}" title="Toggle Unit" style="font-size: 0.65rem; height: 26px; min-width: 28px; font-weight: 700; border-radius: 4px; flex-shrink: 0;">
                                                                {{ $toggleText }}
                                                            </button>
                                                        </div>
                                                        <input type="hidden" class="hidden-sub-unit-mode" name="sub_unit_mode[]" value="{{ $unitMode }}">
                                                    </td>

                                                    <!-- Loose Pieces (hidden) -->
                                                    <td style="width:70px;" class="d-none">
                                                        <input type="number" class="form-control loose-pcs-input text-end"
                                                            name="loose_qty[]" value="0" placeholder="" min="0">
                                                    </td>

                                                    <!-- Size -->
                                                    <td class="col-size">
                                                        <input type="text"
                                                            class="form-control size-display text-center"
                                                            name="size_display[]"
                                                            value="{{ ($vSize ?? '') !== '-' ? ($vSize ?? '') : '' }}"
                                                            placeholder="-">
                                                        <input type="hidden" class="pack-qty" name="pack_qty[]" value="{{ $ppb }}">
                                                    </td>
                                                    
                                                    <!-- Bonus (Editable) -->
                                                    <td class="col-color">
                                                        <input type="text"
                                                            class="form-control color-display text-center"
                                                            name="bonus_display[]"
                                                            value="{{ ($vCol ?? '') !== '-' ? ($vCol ?? '') : '' }}"
                                                            placeholder="0">
                                                    </td>

                                                    <!-- Total Pieces -->
                                                    <td class="col-pieces">
                                                        <input type="text"
                                                            class="form-control total-pieces text-end input-readonly fw-semibold"
                                                            name="total_pieces[]" readonly value="{{ $itemTotalPieces }}"
                                                            placeholder="0" tabindex="-1">
                                                        <input type="hidden" class="sales-qty" name="qty[]" value="{{ $isPcs ? $item->total_pieces : ($cartons . ($loose > 0 ? '.' . $loose : '')) }}">
                                                    </td>

                                                    <!-- Price/Piece -->
                                                    <td class="col-price-p">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <input type="text"
                                                                class="form-control visible-price text-end fw-semibold"
                                                                name="visible_price[]"
                                                                value="{{ $item->price }}"
                                                                placeholder="0" style="flex: 1; min-width: 0;">
                                                            <button type="button" class="btn btn-sm btn-outline-primary price-mode-row-toggle px-1 py-0" 
                                                                    data-mode="retail" title="Retail Mode">
                                                                R
                                                            </button>
                                                        </div>
                                                        <input type="hidden" class="price-per-piece"
                                                            name="price_per_piece[]"
                                                            value="{{ $item->price }}">
                                                        <input type="hidden" class="retail-price"
                                                            value="{{ $pieceRetailPrice }}">
                                                        <input type="hidden" class="wholesale-price"
                                                            value="{{ $pieceWholesalePrice }}">
                                                        <input type="hidden" class="weight-per-piece"
                                                            value="{{ $variantData['weight_per_piece'] ?? ($prod->weight_per_piece ?? 0) }}">
                                                    </td>

                                                    <!-- Discount -->
                                                    <td class="col-disc">
                                                        <div class="discount-wrapper">
                                                            <input type="number" class="form-control discount-value text-end"
                                                                name="item_disc[]" value="{{ $item->discount_percent }}" placeholder="0">
                                                            <input type="hidden" class="discount-type-hidden" name="discount_type[]" value="percent">
                                                            <button type="button"
                                                                class="btn btn-outline-secondary discount-toggle"
                                                                data-type="percent" tabindex="-1">%</button>
                                                        </div>
                                                        <input type="hidden" class="discount-amount" value="{{ $item->discount_amount ?? 0 }}">
                                                    </td>

                                                    <!-- Net Amount -->
                                                    <td class="col-amount">
                                                        <input type="text"
                                                            class="form-control sales-amount text-end input-readonly fw-bold text-dark"
                                                            name="total[]" value="{{ $item->total }}" readonly
                                                            tabindex="-1">
                                                        <input type="hidden" class="gross-amount" name="gross_amount[]" value="{{ $item->total + ($item->discount_amount ?? 0) }}">
                                                    </td>

                                                    <!-- Action -->
                                                    <td class="col-action text-center">
                                                        <button type="button" class="btn-del-row del-row" tabindex="-1" title="Delete Row">&times;</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center fw-bold text-muted row-index" style="vertical-align:middle; font-size:0.75rem;">1</td>
                                                <td class="col-product">
                                                    <select class="form-select product" style="width:100%"><option value=""></option></select>
                                                    <input type="hidden" class="product-id-hidden" name="product_id[]">
                                                    <input type="hidden" class="variant-data-hidden" name="color[]">
                                                    <input type="hidden" class="item-code-display">
                                                    <input type="hidden" class="size-h"><input type="hidden" class="size-w"><input type="hidden" class="size-mode-text">
                                                </td>
                                                <td class="col-stock">
                                                    <input type="text" class="form-control stock text-center input-readonly" readonly tabindex="-1">
                                                    <input type="hidden" class="warehouse" name="warehouse_id[]" value="{{ auth()->user()->warehouse_id ?? 1 }}">
                                                    <input type="hidden" class="variant-stock-value">
                                                </td>
                                                <td style="width:85px;" class="col-qty-wrapper">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input type="number" step="any" class="form-control carton-qty text-start fw-bold" name="carton_qty[]" placeholder="0" min="0" value="" style="flex: 1; min-width: 0; padding-left: 6px;">
                                                        <button type="button" class="btn btn-sm btn-outline-primary qty-unit-toggle px-1 py-0 d-none" data-unit-mode="main" title="Toggle Unit" style="font-size: 0.65rem; height: 26px; min-width: 28px; font-weight: 700; border-radius: 4px; flex-shrink: 0;">Kg</button>
                                                    </div>
                                                    <input type="hidden" class="hidden-sub-unit-mode" name="sub_unit_mode[]" value="main">
                                                </td>
                                                <td style="width:70px;" class="d-none">
                                                    <input type="number" class="form-control loose-pcs-input text-end" name="loose_qty[]" placeholder="" min="0" value="">
                                                </td>
                                                <td class="col-size"><input type="text" class="form-control size-display text-center" name="size_display[]" placeholder="-"><input type="hidden" class="pack-qty" name="pack_qty[]" value="1"></td>
                                                <td class="col-color"><input type="text" class="form-control color-display text-center" name="bonus_display[]" placeholder="0"></td>
                                                <td class="col-pieces"><input type="text" class="form-control total-pieces text-end input-readonly fw-semibold" name="total_pieces[]" readonly placeholder="0" tabindex="-1"><input type="hidden" class="sales-qty" name="qty[]" value="0"></td>
                                                <td class="col-price-p">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <input type="text" class="form-control visible-price text-end fw-semibold" name="visible_price[]" placeholder="0" style="flex: 1; min-width: 0;">
                                                        <button type="button" class="btn btn-sm btn-outline-primary price-mode-row-toggle px-1 py-0" data-mode="retail" title="Retail Mode">R</button>
                                                    </div>
                                                    <input type="hidden" class="price-per-piece" name="price_per_piece[]"><input type="hidden" class="retail-price"><input type="hidden" class="wholesale-price"><input type="hidden" class="weight-per-piece">
                                                </td>
                                                <td class="col-disc">
                                                    <div class="discount-wrapper">
                                                        <input type="number" class="form-control discount-value text-end" name="item_disc[]" placeholder="0">
                                                        <input type="hidden" class="discount-type-hidden" name="discount_type[]" value="percent">
                                                        <button type="button" class="btn btn-outline-secondary discount-toggle" data-type="percent" tabindex="-1">%</button>
                                                    </div>
                                                    <input type="hidden" class="discount-amount" value="0">
                                                </td>
                                                <td class="col-amount"><input type="text" class="form-control sales-amount text-end input-readonly fw-bold text-dark" name="total[]" value="0" readonly tabindex="-1"><input type="hidden" class="gross-amount" name="gross_amount[]"></td>
                                                <td class="col-action text-center"><button type="button" class="btn-del-row del-row" tabindex="-1" title="Delete Row">&times;</button></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="9" class="text-end fw-bold text-uppercase text-secondary" style="font-size:0.8rem;">GRID TOTAL:</td>
                                            <td class="text-end fw-bold text-success fs-6"><span id="totalAmount">0.00</span></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT PANEL: Summary & Payment Methods (col-lg-4 col-xl-3) -->
                    <div class="col-lg-4 col-xl-3">
                        <div class="d-flex flex-column h-100 gap-3">
                            <!-- Executive Summary Card -->
                            <div class="summary-card">
                                <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                                    <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.85rem;"><i class="fas fa-calculator text-primary"></i> Summary</span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size:0.7rem;">Live</span>
                                </div>
                                
                                <div class="summary-row">
                                    <span class="text-muted">Total Amount</span>
                                    <span class="fw-bold text-dark" id="tGross">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Line Discount</span>
                                    <span class="fw-bold text-danger" id="tLineDisc">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="fw-bold text-dark">Net Total</span>
                                    <span class="summary-val-net" id="tSub">0.00</span>
                                </div>
                                <div class="summary-row">
                                    <span class="text-muted">Total Paid</span>
                                    <span class="fw-bold text-success fs-6" id="receiptsTotal">0.00</span>
                                    <span id="receiptsTotalBadge" style="display:none;">0.00</span>
                                </div>
                                <div class="summary-row pt-1">
                                    <span class="fw-bold text-dark">Change</span>
                                    <span class="summary-val-change" id="walkinChange">-0.00</span>
                                </div>
                                <div class="summary-row pt-2 align-items-center" id="changeAccountRow" style="display: none; border-top: 1px dashed #f1aeb5; background: #fff8f8; padding: 6px 8px; border-radius: 6px;">
                                    <span class="text-danger fw-bold d-flex align-items-center gap-1" style="font-size:0.76rem;"><i class="fas fa-hand-holding-usd"></i> Change A/C</span>
                                    <select class="form-select form-select-sm bg-white fw-bold text-danger border-danger" name="change_account_id" id="changeAccountId" style="width: 135px; font-size:0.75rem; height: 28px; padding: 2px 6px;">
                                        @foreach ($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ ($sale->change_account_id == $acc->id || str_contains(strtolower($acc->title), 'cash')) ? 'selected' : '' }}>{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Payment Methods Card -->
                            <div class="payment-methods-card flex-grow-1 d-flex flex-column">
                                <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom">
                                    <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size:0.85rem;"><i class="fas fa-wallet text-success"></i> Payment Methods</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2 fw-bold" id="btnAddRV" style="font-size:0.7rem;"><i class="fas fa-plus me-1"></i>Add Account</button>
                                </div>

                                <div id="rvWrapper" class="mb-3">
                                    <div class="d-flex gap-2 align-items-center mb-2 rv-row">
                                        <select class="form-select form-select-sm rv-account bg-light fw-bold" name="receipt_account_id[]" style="font-size:0.78rem;">
                                            @foreach ($accounts as $acc)
                                                <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') || str_contains(strtolower($acc->title), 'easypaisa') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm text-end rv-amount fw-bold" name="receipt_amount[]" value="{{ $sale->cash > 0 ? $sale->cash : '' }}" placeholder="0.00" style="width: 110px; font-size:0.8rem;">
                                    </div>
                                </div>

                                <button type="button" class="btn btn-save-complete w-100 mt-auto py-2 d-flex align-items-center justify-content-center gap-2" id="btnSaveAndComplete">
                                    <i class="fas fa-check-circle"></i> Save & Complete (F9)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTTOM SUMMARY STRIP -->
                <div class="bottom-summary-strip">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Net Total</span>
                        <span class="fs-5 fw-bolder text-primary" id="walkinNetTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Discount (Rs.)</span>
                        <div class="input-group input-group-sm" style="width: 120px;">
                            <input type="number" class="form-control text-end fw-bold text-danger" id="walkinDiscountRs" value="{{ $sale->total_extradiscount ?? 0 }}" placeholder="0">
                            <span class="input-group-text bg-light text-muted fw-bold" style="font-size:0.75rem;">%</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Payments</span>
                        <span class="fs-6 fw-bold text-success" id="bottomPaymentsTotal">0.00</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted fw-bold" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Change</span>
                        <span class="fs-6 fw-bold text-danger" id="bottomChangeVal">-0.00</span>
                    </div>

                    <div class="align-items-center gap-1" id="bottomChangeAccountWrapper" style="display: none;">
                        <span class="text-danger fw-bold" style="font-size:0.76rem;">Change A/C:</span>
                        <select class="form-select form-select-sm bg-light fw-bold text-danger border-danger" id="bottomChangeAccountId" style="width: 135px; font-size:0.75rem; height: 30px; padding: 2px 6px;">
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ ($sale->change_account_id == $acc->id || str_contains(strtolower($acc->title), 'cash')) ? 'selected' : '' }}>{{ $acc->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" class="btn btn-save-complete d-flex align-items-center gap-2" id="btnSaveAndComplete2">
                        <i class="fas fa-check-circle"></i> Save & Complete (F9)
                    </button>
                </div>

                {{-- ACTION BUTTONS ROW --}}
                <div class="d-flex flex-wrap gap-2 justify-content-center py-2 px-3 mt-3 border-top bg-light rounded-3">
                    <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnSave"><i class="fas fa-bookmark"></i> Booking</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold rounded-2 d-flex align-items-center gap-1 shadow-sm" id="btnPosted"><i class="fas fa-shopping-cart"></i> Sale</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnPrint"><i class="fas fa-print"></i> A4 Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnEstimate"><i class="fas fa-file-invoice"></i> Estimate</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnPrint2"><i class="fas fa-receipt"></i> Thermal Print</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-2 d-flex align-items-center gap-1" id="btnDcThermal"><i class="fas fa-truck"></i> DC</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Products Offcanvas Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="quickProductsOffcanvas" style="width: 360px;">
        <div class="offcanvas-header bg-light py-2 border-bottom">
            <h6 class="offcanvas-title fw-bold text-dark mb-0"><i class="fas fa-th text-primary me-2"></i>Quick Products Panel</h6>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-2">
            <div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control" id="sidebarProductSearch" placeholder="Search product by name, barcode or SKU...">
                <button class="btn btn-primary px-2" type="button"><i class="fas fa-search"></i></button>
            </div>
            <div class="overflow-auto pe-1" id="sidebarProductContainer" style="max-height: calc(100vh - 120px);">
                @if(isset($recentProducts) && count($recentProducts) > 0)
                    @foreach($recentProducts as $prod)
                        <div class="pos-product-card">
                            <div class="pos-product-img">
                                <i class="fas fa-box text-secondary fs-5"></i>
                            </div>
                            <div class="pos-product-info">
                                <div class="pos-product-name" title="{{ $prod->item_name }}">{{ $prod->item_name }}</div>
                                <div class="pos-product-sub">
                                    <span class="badge-stock-green">{{ $prod->total_pieces ?? 0 }} Pcs</span> Stock
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pos-product-price">{{ number_format($prod->retail_price ?? 0, 2) }}</div>
                                <button type="button" class="pos-product-add-btn add-product-direct-btn" data-id="{{ $prod->id }}" title="Add to Grid"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title font-weight-bold fw-bold text-white mb-0" id="addCustomerModalLabel" style="font-size: 1rem;">
                        <i class="fas fa-user-plus me-2 mr-2"></i>Quick Customer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem; line-height: 1; opacity: 0.9; cursor: pointer;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="ajaxAddCustomerForm" autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6 mb-2">
                                <label class="form-label font-weight-bold fw-bold">Customer Type <span class="text-danger">*</span></label>
                                <select class="form-control form-select" name="customer_type" id="modalCustomerType" required>
                                    @foreach(\App\Models\CustomerType::orderBy('name')->get() as $type)
                                        <option value="{{ $type->name }}" {{ $type->name === 'Main Customer' ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label font-weight-bold fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" id="modalCustomerName" required placeholder="Customer Name">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label font-weight-bold fw-bold">Mobile</label>
                                <input type="text" class="form-control" name="mobile" placeholder="0300-1234567">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label font-weight-bold fw-bold">Opening Balance</label>
                                <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                            </div>
                            <div class="col-12 mb-2">
                                <label class="form-label font-weight-bold fw-bold">Address</label>
                                <input type="text" class="form-control" name="address" placeholder="Address">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm fw-bold" id="btnSaveAjaxCustomer">
                        <i class="fas fa-save me-1 mr-1"></i> Save Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Product Modal --}}
    @include('admin_panel.partials.quick_add_product_modal')
@endsection

@section('js')
    @include('admin_panel.sale.scripts.shared_logic')

    <script>
        $(document).ready(function() {
            window.isEditModeLoading = true;

            // --- Initial Setup ---
            $('#salesTableBody tr').each(function() {
                initProductSelect2($(this).find('.product'));
            });

            // Recalculate row values
            $('#salesTableBody tr').each(function() {
                if (typeof computeRow === 'function') {
                    computeRow($(this));
                }
            });

            window.isEditModeLoading = false;

            // Recompute Receipts and updateGrandTotals
            if (typeof window.recomputeReceipts === 'function') {
                window.recomputeReceipts();
            } else {
                updateGrandTotals();
            }

            refreshPostedState();

            // ============================================================
            // CUSTOMER SELECT2 AJAX SEARCH (Name or Code)
            // ============================================================
            function getPartyType() {
                return $('#partyTypeSelect').val() || 'Main Customer';
            }

            $('#customerSelect').select2({
                placeholder: 'Search by Name or Code...',
                allowClear: true,
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('salecustomers.index') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            type: getPartyType(),
                            search: params.term || ''
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(function(c) {
                                return {
                                    id: c.id,
                                    text: (c.customer_id || '') + ' - ' + c.customer_name,
                                    customer: c
                                };
                            })
                        };
                    },
                    cache: false
                },
                language: {
                    noResults: function() {
                        return $('<div>No customer found. <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary py-0 px-2 mt-1 btn-open-customer-modal" style="font-size:0.75rem;"><i class="fas fa-user-plus"></i> Quick Add Customer</a></div>');
                    }
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                templateResult: function(item) {
                    if (item.loading) return item.text;
                    if (!item.customer) return item.text;
                    const c = item.customer;
                    return $(`<div>
                        <strong>${c.customer_name}</strong>
                        <small class="text-muted ms-2">${c.customer_id || ''}</small>
                        ${c.mobile ? '<br><small class="text-muted">' + c.mobile + '</small>' : ''}
                    </div>`);
                },
                templateSelection: function(item) {
                    if (!item.customer) return item.text;
                    return item.customer.customer_id + ' - ' + item.customer.customer_name;
                }
            });

            // Party type change â†’ reset customer only on user change
            $(document).on('change', '#partyTypeSelect', function(e) {
                if (!e.isTrigger) {
                    $('#customerSelect').val(null).trigger('change');
                    clearCustomerInfo();
                }
            });

            // Customer selected â†’ load details
            $('#customerSelect').on('select2:select', function(e) {
                const id = e.params.data.id;
                if (!id) return;

                $.get("{{ url('sale/customers') }}/" + id + "?t=" + new Date().getTime(), function(d) {
                    // Fill hidden fields
                    $('#address').val(d.address || '');
                    $('#tel').val(d.mobile || '');
                    const prev = parseFloat(d.previous_balance || 0);
                    const range = parseFloat(d.balance_range || 0);
                    $('#previousBalance').val(prev.toFixed(2));
                    $('#rangeBalance').val(range.toFixed(2));

                    if (typeof updateGrandTotals === 'function') updateGrandTotals();
                }).fail(function() {
                    showAlert('error', 'Failed to load customer details');
                });
            });

            // Customer cleared
            $('#customerSelect').on('select2:clear', function() {
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            function clearCustomerInfo() {
                $('#address, #tel').val('');
                $('#previousBalance, #rangeBalance').val('0');
            }

            $('#clearCustomerData').on('click', function() {
                $('#customerSelect').val(null).trigger('change');
                clearCustomerInfo();
                if (typeof updateGrandTotals === 'function') updateGrandTotals();
            });

            $('#btnPrint').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice', '_blank'));
            });
            $('#btnEstimate').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/invoice?type=estimate', '_blank'));
            });
            $('#btnPrint2').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/recepit', '_blank'));
            });
            $('#btnDcThermal').on('click', function() {
                ensureSaved().then(id => window.open('{{ url('sales') }}/' + id + '/dc-thermal', '_blank'));
            });

            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            // QUICK CUSTOMER MODAL LOGIC & EVENT HANDLERS
            // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
            window.openCustomerModal = function(initialName = '') {
                $('#ajaxAddCustomerForm')[0].reset();
                let currentParty = $('#partyTypeSelect').val() || 'Main Customer';
                $('#modalCustomerType').val(currentParty);
                if (initialName && typeof initialName === 'string') {
                    $('#modalCustomerName').val(initialName.trim());
                }
                
                if (typeof $('#addCustomerModal').modal === 'function') {
                    $('#addCustomerModal').modal('show');
                } else if (window.bootstrap && window.bootstrap.Modal) {
                    let m = bootstrap.Modal.getOrCreateInstance(document.getElementById('addCustomerModal'));
                    m.show();
                }

                setTimeout(function() {
                    $('#modalCustomerName').focus();
                }, 400);
            };

            window.closeCustomerModal = function() {
                try {
                    $('#addCustomerModal').modal('hide');
                } catch(e) {}
                if (window.bootstrap && window.bootstrap.Modal) {
                    let m = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
                    if (m) m.hide();
                }
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
            };

            // Explicit click listener on any button with btn-open-customer-modal or #btnOpenAddCustomerModal
            $(document).on('click', '#btnOpenAddCustomerModal, .btn-open-customer-modal', function(e) {
                e.preventDefault();
                let term = '';
                if ($('.select2-search__field:visible').length) {
                    term = $('.select2-search__field:visible').val();
                    $('#customerSelect').select2('close');
                }
                openCustomerModal(term);
            });

            // Keyboard shortcut (F2 or Alt+C) to open Quick Customer modal
            $(document).on('keydown', function(e) {
                if ((e.key === 'F2' || (e.altKey && (e.key === 'c' || e.key === 'C'))) && !$('#addCustomerModal').is(':visible')) {
                    e.preventDefault();
                    openCustomerModal();
                }
            });

            // AJAX Customer Submit
            $('#btnSaveAjaxCustomer').on('click', function() {
                let form = $('#ajaxAddCustomerForm');
                if (!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }
                
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: '{{ route('customers.store') }}',
                    type: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1 mr-1"></i> Save Customer');
                        if (res.success) {
                            closeCustomerModal();
                            form[0].reset();
                            
                            // Make sure partyTypeSelect matches the customer type
                            if (res.customer.customer_type) {
                                $('#partyTypeSelect').val(res.customer.customer_type);
                            }
                            
                            // Auto select new customer
                            let displayText = (res.customer.customer_id ? res.customer.customer_id + ' - ' : '') + res.customer.customer_name;
                            let newOption = new Option(displayText, res.customer.id, true, true);
                            $('#customerSelect').append(newOption).trigger('change');
                            
                            // Trigger select2 API selection to load customer details
                            $('#customerSelect').trigger({
                                type: 'select2:select',
                                params: {
                                    data: {
                                        id: res.customer.id,
                                        text: displayText
                                    }
                                }
                            });
                            
                            showAlert('success', 'Customer added successfully!');
                        } else {
                            showAlert('error', res.message || 'Failed to save customer.');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1 mr-1"></i> Save Customer');
                        let msg = 'Error adding customer. Check inputs.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showAlert('error', msg);
                    }
                });
            });

            setTimeout(() => {
                $('#pageLoader').addClass('d-none');
            }, 300);
        });
    </script>
@endsection

