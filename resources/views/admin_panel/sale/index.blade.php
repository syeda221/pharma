@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* Minimal KPI Strip */
        .kpi-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .kpi-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            flex: 1 1 0;
            min-width: 100px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            position: relative;
        }
        .kpi-card .kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #64748b;
            white-space: nowrap;
            margin-bottom: 3px;
        }
        .kpi-card .kpi-value {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }
        .kpi-card .kpi-sub {
            font-size: 9.5px;
            color: #94a3b8;
            margin-top: 2px;
            white-space: nowrap;
        }
        .kpi-card.kpi-gross  { border-top: 3px solid #2563eb; }
        .kpi-card.kpi-disc   { border-top: 3px solid #dc2626; }
        .kpi-card.kpi-net    { border-top: 3px solid #10b981; }
        .kpi-card.kpi-count  { border-top: 3px solid #0284c7; }
        .kpi-card.kpi-qty    { border-top: 3px solid #8b5cf6; background: #faf5ff; }
        .kpi-card.kpi-pharma { border-top: 3px solid #0284c7; background: #f0f9ff; }
        .kpi-card.kpi-doc    { border-top: 3px solid #059669; background: #f0fdf4; }
        .kpi-card.kpi-dist   { border-top: 3px solid #6366f1; background: #f5f3ff; }

        /* Ultra-Compact Minimal Single-Row Filter Toolbar */
        .filter-panel {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            padding: 6px 10px !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03) !important;
        }

        .filter-toolbar-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
            width: 100%;
        }

        .filter-panel .form-control,
        .filter-panel .form-select {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
            color: #0f172a !important;
            height: 30px !important;
            font-size: 11.5px !important;
            background-color: #ffffff !important;
            padding: 2px 6px !important;
        }
        
        .filter-panel .form-control:focus,
        .filter-panel .form-select:focus {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }

        /* Compact Select2 in Filter Panel */
        .filter-panel .select2-container {
            width: 100% !important;
        }
        .filter-panel .select2-container--default .select2-selection--single {
            height: 30px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 2px 6px !important;
            background-color: #ffffff !important;
        }
        .filter-panel .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            color: #0f172a !important;
            font-weight: 500 !important;
            font-size: 11.5px !important;
            padding-left: 0 !important;
        }
        .filter-panel .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
            right: 4px !important;
        }
        .filter-panel .select2-container--default.select2-container--focus .select2-selection--single,
        .filter-panel .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.12) !important;
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-shadow: 0 6px 16px -2px rgba(0, 0, 0, 0.1) !important;
            font-size: 12px !important;
            z-index: 9999 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 4px 8px !important;
            font-size: 12px !important;
        }

        /* Compact Filter Action Buttons */
        .filter-panel .btn-compact-primary {
            background-color: #2563eb !important;
            border: 1px solid #1d4ed8 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            height: 30px !important;
            padding: 0 10px !important;
            font-size: 11.5px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .filter-panel .btn-compact-primary:hover {
            background-color: #1d4ed8 !important;
        }
        .filter-panel .btn-compact-secondary {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            border-radius: 6px !important;
            height: 30px !important;
            padding: 0 8px !important;
            font-size: 11.5px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .filter-panel .btn-compact-secondary:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }

        /* Premium Buttons */
        .btn-premium-primary {
            background-color: #2563eb !important;
            border: 1.5px solid #1d4ed8 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0 16px !important;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-premium-primary:hover {
            background-color: #1d4ed8 !important;
            transform: translateY(-1px);
        }
        
        .btn-premium-secondary {
            background-color: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            height: 38px !important;
            padding: 0 16px !important;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-premium-secondary:hover {
            background-color: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #1e293b !important;
        }

        /* Premium Table Styling with Crisp Column Partitions */
        .premium-card {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
            background-color: #ffffff;
        }

        .premium-table {
            border: 1.5px solid #cbd5e1 !important;
            border-collapse: collapse !important;
            width: 100% !important;
        }

        /* Table Scroll and Sticky Action */
        .table-responsive {
            border-radius: 8px !important;
            overflow-x: auto !important;
            min-height: 380px;
            position: relative;
        }

        /* Premium Dropdown Menu Customizations */
        .dropdown-menu {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.15), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            padding: 6px 0 !important;
            z-index: 1060 !important;
        }
        .dropdown-item {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            padding: 8px 16px !important;
            transition: all 0.15s ease-in-out !important;
        }
        .dropdown-item:hover {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }
        .dropdown-divider {
            border-top: 1.5px solid #e2e8f0 !important;
            margin: 6px 0 !important;
        }
        
        .text-end, .text-right {
            text-align: right !important;
        }

        .premium-table thead th.text-right,
        .premium-table thead th.text-end,
        .premium-table tbody td.text-right,
        .premium-table tbody td.text-end {
            text-align: right !important;
        }
        
        .premium-table thead th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.3px;
            border: 1px solid #e2e8f0 !important;
            border-bottom: 2px solid #cbd5e1 !important;
            border-right: 1px solid #e2e8f0 !important;
            padding: 8px 6px !important;
            vertical-align: middle !important;
            white-space: nowrap;
            position: relative;
        }

        /* DataTables sorting padding */
        table.dataTable.premium-table thead th.sorting,
        table.dataTable.premium-table thead th.sorting_asc,
        table.dataTable.premium-table thead th.sorting_desc {
            padding-right: 20px !important;
        }

        table.dataTable.premium-table thead th.text-right.sorting,
        table.dataTable.premium-table thead th.text-right.sorting_asc,
        table.dataTable.premium-table thead th.text-right.sorting_desc {
            padding-right: 20px !important;
        }
        
        .premium-table tbody td {
            border: 1px solid #f1f5f9 !important;
            border-right: 1px solid #e2e8f0 !important;
            padding: 5px 6px !important;
            font-size: 11.5px !important;
            color: #1e293b !important;
            background-color: #ffffff;
            vertical-align: middle !important;
        }
        
        .premium-table tbody tr:hover td {
            background-color: #f8fafc !important;
        }

        /* Sticky Action Column so it is ALWAYS visible */
        .premium-table th:last-child,
        .premium-table td:last-child {
            position: sticky !important;
            right: 0 !important;
            background-color: #ffffff !important;
            z-index: 5 !important;
            box-shadow: -3px 0 6px -2px rgba(0, 0, 0, 0.12) !important;
        }
        .premium-table thead th:last-child {
            background-color: #f8fafc !important;
            z-index: 6 !important;
        }
        .premium-table tbody tr:hover td:last-child {
            background-color: #f8fafc !important;
        }

        /* Dropdown Action Button */
        .btn-premium-action {
            background-color: #f8fafc !important;
            border: 1px solid #cbd5e1 !important;
            color: #475569 !important;
            font-weight: 600 !important;
            border-radius: 5px !important;
            height: 27px !important;
            padding: 0 8px !important;
            font-size: 11px !important;
            letter-spacing: 0.2px;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s ease-in-out !important;
            white-space: nowrap !important;
        }
        .btn-premium-action:hover, 
        .btn-premium-action:focus, 
        .btn-premium-action[aria-expanded="true"] {
            background-color: #f1f5f9 !important;
            border-color: #94a3b8 !important;
            color: #1e293b !important;
        }

        /* Responsive Breakpoints (< 768px) */
        @media (max-width: 768px) {
            .sales-hdr-actions {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                width: 100%;
            }
            .sales-hdr-actions .btn {
                width: 100%;
                justify-content: center;
                height: 38px;
                font-size: 0.8rem;
            }
            .sales-status-pills {
                display: flex !important;
                gap: 6px;
                overflow-x: auto;
                padding-bottom: 6px;
                -webkit-overflow-scrolling: touch;
            }
            .sales-status-pills .btn {
                flex: 0 0 auto;
                white-space: nowrap;
            }
            /* DataTables Mobile Search Controls */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                float: none !important;
                text-align: left !important;
                margin-bottom: 10px;
            }
            .dataTables_wrapper .dataTables_filter input {
                width: 100% !important;
                margin-left: 0 !important;
            }
        }
        @media (min-width: 769px) {
            .sales-hdr-actions {
                display: flex;
                gap: 8px;
            }
            .sales-status-pills {
                display: flex;
                gap: 8px;
            }
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-4">

                {{-- Page Header --}}
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-shopping-cart text-primary"></i> Sales Management
                        </h4>
                        <p class="text-muted mb-0 small">View, search, filter and edit your sales invoices & bookings</p>
                    </div>
                    <div class="sales-hdr-actions">
                        <a class="btn btn-outline-danger px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ route('sale.return.index') }}" style="border-radius: 8px;">
                            <i class="fas fa-undo"></i> Returns
                        </a>
                        <a class="btn btn-outline-primary px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ url('bookings') }}" style="border-radius: 8px;">
                            <i class="fas fa-bookmark"></i> Bookings
                        </a>
                        @can('sales.create')
                            <a class="btn btn-primary px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                                href="{{ route('sale.add') }}" style="border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add Sale
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- KPI Stat Strip — all 7 cards in one minimal row --}}
                <div class="kpi-strip mb-3">

                    {{-- Gross Total --}}
                    <div class="kpi-card kpi-gross">
                        <div class="kpi-label">Gross Total</div>
                        <div class="kpi-value text-dark" id="statTotalGross">Rs. {{ number_format($stats['total_gross'] ?? 0, 2) }}</div>
                    </div>

                    {{-- Total Discounts --}}
                    <div class="kpi-card kpi-disc">
                        <div class="kpi-label">Discounts</div>
                        <div class="kpi-value text-danger" id="statTotalDiscount">Rs. {{ number_format($stats['total_discount'] ?? 0, 2) }}</div>
                        <div id="statDiscountSub" class="kpi-sub">
                            Line: Rs. {{ number_format($stats['inline_discount'] ?? 0, 2) }}
                            @if(($stats['additional_discount'] ?? 0) > 0)
                                &nbsp;· Extra: Rs. {{ number_format($stats['additional_discount'] ?? 0, 2) }}
                            @endif
                        </div>
                    </div>

                    {{-- Net Revenue --}}
                    <div class="kpi-card kpi-net">
                        <div class="kpi-label">Net Revenue</div>
                        <div class="kpi-value text-success" id="statTotalNet">Rs. {{ number_format($stats['total_net'] ?? 0, 2) }}</div>
                    </div>

                    {{-- Invoices Count --}}
                    <div class="kpi-card kpi-count">
                        <div class="kpi-label">Invoices</div>
                        <div class="kpi-value text-dark" id="statTotalCount">{{ number_format($stats['total_count'] ?? 0) }}</div>
                        <div id="statStatusCounts" class="kpi-sub">
                            P:{{ $stats['posted_count'] ?? 0 }}
                            @if(($stats['booked_count'] ?? 0) > 0) &nbsp;B:{{ $stats['booked_count'] ?? 0 }} @endif
                            @if(($stats['returned_count'] ?? 0) > 0) &nbsp;R:{{ $stats['returned_count'] ?? 0 }} @endif
                        </div>
                    </div>

                    {{-- Products Sold Qty / Bonus --}}
                    <div class="kpi-card kpi-qty">
                        <div class="kpi-label" style="color:#7c3aed;">Product Qty / Bonus</div>
                        <div class="kpi-value" style="color:#7c3aed;" id="statTotalQty">{{ number_format($stats['total_qty'] ?? 0) }} / {{ number_format($stats['total_bonus_qty'] ?? 0) }}</div>
                        <div class="kpi-sub" style="color:#7c3aed; opacity:0.8;">Sold / Bonus Qty</div>
                    </div>

                    {{-- Pharmacy Discount --}}
                    <div class="kpi-card kpi-pharma">
                        <div class="kpi-label" style="color:#0284c7;">Pharmacy</div>
                        <div class="kpi-value" style="color:#0284c7;" id="statDiscountMed">Rs. {{ number_format($stats['discount_medical'] ?? 0, 2) }}</div>
                        <div class="kpi-sub" style="color:#0284c7; opacity:0.75;">Inline Disc · Medical</div>
                    </div>

                    {{-- Doctor Discount --}}
                    <div class="kpi-card kpi-doc">
                        <div class="kpi-label" style="color:#059669;">Doctor</div>
                        <div class="kpi-value" style="color:#059669;" id="statDiscountDoc">Rs. {{ number_format($stats['discount_doctor'] ?? 0, 2) }}</div>
                        <div class="kpi-sub" style="color:#059669; opacity:0.75;">Inline Disc · Doctor</div>
                    </div>

                    {{-- Distribution Discount --}}
                    <div class="kpi-card kpi-dist">
                        <div class="kpi-label" style="color:#6366f1;">Distribution</div>
                        <div class="kpi-value" style="color:#6366f1;" id="statDiscountDist">Rs. {{ number_format($stats['discount_distribution'] ?? 0, 2) }}</div>
                        <div class="kpi-sub" style="color:#6366f1; opacity:0.75;">Inline Disc · Distribution</div>
                    </div>

                </div>

                {{-- Status Filter Pills --}}
                <div class="mb-4 sales-status-pills">
                    <a href="{{ route('sale.index', ['status' => 'all']) }}"
                        class="btn btn-sm {{ request('status') == 'all' || !request('status') ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-3 shadow-sm px-3 fw-bold">
                        All <span class="badge bg-white text-dark ms-1">{{ $stats['total_count'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('sale.index', ['status' => 'posted']) }}"
                        class="btn btn-sm {{ request('status') == 'posted' ? 'btn-success' : 'btn-outline-success' }} rounded-3 shadow-sm px-3 fw-bold">
                        Posted <span class="badge bg-white text-success ms-1">{{ $stats['posted_count'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('sale.index', ['status' => 'draft']) }}"
                        class="btn btn-sm {{ request('status') == 'draft' ? 'btn-warning text-dark' : 'btn-outline-warning' }} rounded-3 shadow-sm px-3 fw-bold">
                        Draft <span class="badge bg-white text-dark ms-1">{{ $stats['draft_count'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('sale.index', ['status' => 'booked']) }}"
                        class="btn btn-sm {{ request('status') == 'booked' ? 'btn-info text-white' : 'btn-outline-info' }} rounded-3 shadow-sm px-3 fw-bold">
                        Booked <span class="badge bg-white text-info ms-1">{{ $stats['booked_count'] ?? 0 }}</span>
                    </a>
                    <a href="{{ route('sale.index', ['status' => 'returned']) }}"
                        class="btn btn-sm {{ request('status') == 'returned' ? 'btn-danger' : 'btn-outline-danger' }} rounded-3 shadow-sm px-3 fw-bold">
                        Returned <span class="badge bg-white text-danger ms-1">{{ $stats['returned_count'] ?? 0 }}</span>
                    </a>
                </div>

                <div class="card premium-card">
                    <div class="card-body p-3 p-md-4">
                        @if (session('success'))
                            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ session('success') }}</span>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ session('error') }}</span>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Mobile Filter Panel Toggle Button --}}
                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 d-md-none mb-3 fw-bold d-flex align-items-center justify-content-center gap-2" id="toggleFilterPanel">
                            <i class="fas fa-filter"></i> Search & Filters Toggle
                        </button>

                        {{-- Compact Filter Toolbar --}}
                        <div class="card filter-panel mb-3" id="filterPanelContainer">
                            <div class="card-body p-0">
                                <form id="filterForm" autocomplete="off" onsubmit="return false;">
                                    <div class="filter-toolbar-row">
                                        {{-- Quick Filter --}}
                                        <div style="width: 82px; flex-shrink: 0;">
                                            <select id="quick_filter" class="form-select text-center" title="Quick Filter">
                                                <option value="custom">Range</option>
                                                <option value="daily">Today</option>
                                                <option value="weekly">Week</option>
                                                <option value="monthly">Month</option>
                                                <option value="yearly">Year</option>
                                            </select>
                                        </div>

                                        {{-- From Date (exact width for 10-char date like 06/10/2026) --}}
                                        <div style="width: 95px; flex-shrink: 0;">
                                            <input type="text" class="form-control datepicker-custom bg-white text-center" name="from_date" id="filter_from_date" placeholder="From Date">
                                        </div>

                                        {{-- To Date --}}
                                        <div style="width: 95px; flex-shrink: 0;">
                                            <input type="text" class="form-control datepicker-custom bg-white text-center" name="to_date" id="filter_to_date" placeholder="To Date">
                                        </div>

                                        {{-- Inv / Bill# --}}
                                        <div style="width: 90px; flex-shrink: 0;">
                                            <input type="text" class="form-control text-center" name="bill_no" id="filter_bill_no" value="{{ request('bill_no') ?? request('invoice_no') }}" placeholder="Inv/Bill#">
                                        </div>

                                        {{-- M.Bill Ref --}}
                                        <div style="width: 85px; flex-shrink: 0;">
                                            <input type="text" class="form-control text-center" name="reference" id="filter_reference" placeholder="M.Bill">
                                        </div>

                                        {{-- Customer (flexible to fill remaining space) --}}
                                        <div style="flex: 1 1 180px; min-width: 140px;">
                                            <select class="form-select select2-customer" name="customer_id" id="filter_customer_id" style="width:100%;">
                                                <option value="">All Customers</option>
                                                @foreach ($customers as $c)
                                                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                                        {{ $c->customer_name }} {{ $c->mobile ? '('.$c->mobile.')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Customer Type --}}
                                        <div style="width: 82px; flex-shrink: 0;">
                                            <select class="form-select text-center" name="customer_type" id="filter_customer_type" title="Customer Type">
                                                <option value="">Type</option>
                                                <option value="doctor">Doctor</option>
                                                <option value="company">Company</option>
                                                <option value="normal">Normal</option>
                                                <option value="distributor">Distributor</option>
                                            </select>
                                        </div>

                                        {{-- Zone --}}
                                        <div style="width: 82px; flex-shrink: 0;">
                                            <select class="form-select text-center" name="zone_id" id="filter_zone_id" title="Zone">
                                                <option value="">Zone</option>
                                                @foreach($zones as $z)
                                                    <option value="{{ $z->id }}">{{ $z->zone }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Reset & Search Buttons --}}
                                        <div class="d-flex align-items-center gap-1" style="flex-shrink: 0;">
                                            <button type="button" class="btn btn-compact-secondary" id="btnReset" title="Reset Filters">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button type="button" class="btn btn-compact-primary" id="btnSearch">
                                                <i class="fas fa-search me-1"></i>Search
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Table Container --}}
                        <div class="table-responsive">
                            <table id="sales-table" class="table table-bordered table-hover align-middle datanew premium-table" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-2 px-2 text-secondary fw-semibold text-uppercase small" style="width: 80px;">Invoice#</th>
                                        <th class="py-2 px-2 text-secondary fw-semibold text-uppercase small" style="width: 110px;">Customer</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-center" style="width: 45px;" data-orderable="false">M.Bill</th>
                                        <th class="py-2 px-2 text-secondary fw-semibold text-uppercase small" style="width: 95px;" data-orderable="false">Product</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-center" style="width: 45px;">Qty</th>
                                        <th class="py-2 px-2 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 80px;">Gross</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 68px;" data-orderable="false">Pharm</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 68px;" data-orderable="false">Doctor</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 58px;" data-orderable="false">Dist</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 58px;" data-orderable="false">Extra</th>
                                        <th class="py-2 px-2 text-secondary fw-semibold text-uppercase small text-right" style="text-align: right !important; width: 80px;">Net Total</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-center" style="width: 70px;">Date</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-center" style="width: 65px;">Status</th>
                                        <th class="py-2 px-1 text-secondary fw-semibold text-uppercase small text-center" style="width: 65px;" data-orderable="false">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="salesTableBody">
                                    @include('admin_panel.sale.partials.sales_table_body')
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2 with search for customer dropdown
            if ($('.select2-customer').length > 0) {
                $('.select2-customer').select2({
                    placeholder: "All Customers",
                    allowClear: true,
                    width: '100%'
                });
            }

            // Function to initialize DataTable safely
            function initDataTable() {
                try {
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sales-table')) {
                        $('#sales-table').DataTable().destroy();
                    }
                    if ($.fn.DataTable) {
                        $('#sales-table').DataTable({
                            "pageLength": 10,
                            "order": [],
                            "autoWidth": false,
                            "columnDefs": [
                                { "orderable": false, "targets": [2, 3, 6, 7, 8, 9, 13] }
                            ],
                            "language": {
                                "search": "",
                                "searchPlaceholder": "Search sales..."
                            },
                            "dom": "<'row mb-3 align-items-center'<'col-12 col-md-6 mb-2 mb-md-0'l><'col-12 col-md-6'f>>" +
                                "<'row'<'col-12'tr>>" +
                                "<'row mt-3 align-items-center'<'col-12 col-md-5 mb-2 mb-md-0'i><'col-12 col-md-7'p>>",
                        });
                    }
                } catch(e) {
                    console.error("DataTable initialization error: ", e);
                }
            }

            // Initial call
            initDataTable();

            // Mobile Filter Panel Toggle
            $('#toggleFilterPanel').on('click', function() {
                $('#filterPanelContainer').slideToggle(200);
            });

            // Core AJAX Filter Function
            function applySalesFilter() {
                const $btn = $('#btnSearch');
                const origHtml = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Searching...');

                let formData = $('#filterForm').serialize();
                let urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('status')) {
                    formData += '&status=' + encodeURIComponent(urlParams.get('status'));
                }

                $.ajax({
                    url: '{{ route("sale.index") }}',
                    method: 'GET',
                    data: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).html(origHtml);
                        
                        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sales-table')) {
                            $('#sales-table').DataTable().destroy();
                        }
                        
                        $('#salesTableBody').html(response.html);
                        
                        // Update Stat Cards dynamically if present
                        if (response.stats) {
                            let soldText = Number(response.stats.total_qty || 0).toLocaleString();
                            let bonusText = Number(response.stats.total_bonus_qty || 0).toLocaleString();
                            $('#statTotalQty').text(soldText + ' / ' + bonusText);
                            $('#statTotalGross').text('Rs. ' + Number(response.stats.total_gross || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statTotalDiscount').text('Rs. ' + Number(response.stats.total_discount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            let discSubHtml = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 9.5px;">Line: Rs. ' + Number(response.stats.inline_discount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span>';
                            if (Number(response.stats.additional_discount || 0) > 0) {
                                discSubHtml += '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" style="font-size: 9.5px;">Extra: Rs. ' + Number(response.stats.additional_discount || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</span>';
                            }
                            $('#statDiscountSub').html(discSubHtml);
                            $('#statDiscountMed').text('Rs. ' + Number(response.stats.discount_medical || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statDiscountDoc').text('Rs. ' + Number(response.stats.discount_doctor || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statDiscountDist').text('Rs. ' + Number(response.stats.discount_distribution || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            $('#statTotalNet').text('Rs. ' + Number(response.stats.total_net || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            let statusHtml = '<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 9.5px;"><i class="fas fa-check-circle me-0.5"></i> Posted: ' + (response.stats.posted_count || 0) + '</span>';
                            if (Number(response.stats.booked_count || 0) > 0) {
                                statusHtml += '<span class="badge bg-info-subtle text-info border border-info-subtle ms-1" style="font-size: 9.5px;">Booked: ' + (response.stats.booked_count || 0) + '</span>';
                            }
                            if (Number(response.stats.returned_count || 0) > 0) {
                                statusHtml += '<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 9.5px;">Ret: ' + (response.stats.returned_count || 0) + '</span>';
                            }
                            $('#statStatusCounts').html(statusHtml);
                        }

                        initDataTable();
                    },
                    error: function(err) {
                        $btn.prop('disabled', false).html(origHtml);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', 'Failed to retrieve filtered list.', 'error');
                        } else {
                            alert('Failed to retrieve filtered list.');
                        }
                    }
                });
            }

            // Quick Filter Logic
            $(document).on('change', '#quick_filter', function() {
                let val = $(this).val();
                if (val === 'custom') return;

                let today = new Date();
                let start = new Date();
                let end = new Date();

                if (val === 'daily') {
                    start = new Date();
                    end = new Date();
                } else if (val === 'weekly') {
                    let day = today.getDay();
                    let diff = today.getDate() - day + (day === 0 ? -6 : 1);
                    start = new Date(today.setDate(diff));
                    end = new Date();
                } else if (val === 'monthly') {
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = new Date();
                } else if (val === 'yearly') {
                    start = new Date(today.getFullYear(), 0, 1);
                    end = new Date();
                }

                let formatDate = function(d) {
                    let year = d.getFullYear();
                    let month = String(d.getMonth() + 1).padStart(2, '0');
                    let day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                let startStr = formatDate(start);
                let endStr = formatDate(end);

                let pickerFrom = document.getElementById('filter_from_date') ? document.getElementById('filter_from_date')._flatpickr : null;
                let pickerTo = document.getElementById('filter_to_date') ? document.getElementById('filter_to_date')._flatpickr : null;

                if (pickerFrom) pickerFrom.setDate(startStr, true);
                else $("#filter_from_date").val(startStr);

                if (pickerTo) pickerTo.setDate(endStr, true);
                else $("#filter_to_date").val(endStr);

                applySalesFilter();
            });

            // Trigger search on button click & enter key
            $('#btnSearch').on('click', function(e) {
                e.preventDefault();
                applySalesFilter();
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                applySalesFilter();
                return false;
            });

            $(document).on('keypress', '#filterForm input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    applySalesFilter();
                    return false;
                }
            });

            // Reset form completely and fetch unfiltered list via AJAX
            $('#btnReset').on('click', function(e) {
                e.preventDefault();

                // 1. Explicitly clear all filter inputs
                $('#filter_from_date').val('');
                $('#filter_to_date').val('');
                $('#filter_bill_no').val('');
                $('#filter_reference').val('');
                $('#quick_filter').val('custom');
                $('#filter_customer_type').val('');
                $('#filter_zone_id').val('');
                
                // 2. Clear Select2 Customer Dropdown properly
                if ($('.select2-customer').length > 0) {
                    $('.select2-customer').val('').trigger('change');
                }
                
                // 3. Clear Flatpickr instances
                let fromElem = document.getElementById('filter_from_date');
                let toElem = document.getElementById('filter_to_date');
                if (fromElem && fromElem._flatpickr) {
                    fromElem._flatpickr.clear();
                }
                if (toElem && toElem._flatpickr) {
                    toElem._flatpickr.clear();
                }

                // Clear any Flatpickr visible alt-inputs inside filter container
                $('#filterPanelContainer .datepicker-custom').val('');
                $('#filterPanelContainer input.input').val('');

                // 4. Clear DataTables client search if any
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sales-table')) {
                    $('#sales-table').DataTable().search('');
                }

                // 5. Clean browser URL query parameters (revert back to clean /sale or preserve status tab)
                let urlParams = new URLSearchParams(window.location.search);
                let newUrl = window.location.pathname;
                if (urlParams.has('status')) {
                    newUrl += '?status=' + encodeURIComponent(urlParams.get('status'));
                }
                if (window.history.replaceState) {
                    window.history.replaceState({}, '', newUrl);
                }

                // 6. Trigger AJAX to fetch complete unfiltered data
                applySalesFilter();
            });

            // Confirm Booking Action
            $(document).on('click', '.confirm-booking-btn', function(e) {
                e.preventDefault();
                let form = $(this).closest("form");

                Swal.fire({
                    title: "Confirm Booking?",
                    text: "Are you sure you want to convert this booking to a posted sale? This will update stocks and post ledgers.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#28a745",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, Confirm it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
