@extends('admin_panel.layout.app')

@section('content')
    <style>
        /* Modern Overview Card for Discounts (Matching Profit-Loss Report) */
        .overview-card {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }
        .overview-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.06);
        }
        .overview-card .ov-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .overview-card .ov-label {
            font-size: .64rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .overview-card .ov-val {
            font-size: .95rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .overview-card .ov-sub {
            font-size: .62rem;
            font-weight: 600;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Summary Pill Bar & Stat Pills (Matching Profit-Loss Report) */
        .summary-pill-bar {
            display: flex;
            align-items: stretch;
            justify-content: space-between;
            gap: 8px;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: 2px;
        }
        .summary-pill-bar::-webkit-scrollbar {
            height: 5px;
        }
        .summary-pill-bar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .summary-pill-bar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .stat-pill {
            flex: 1 1 0px;
            min-width: 105px;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .row .stat-pill {
            min-width: 0 !important;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .stat-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,0.06);
        }
        .stat-pill .stat-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        .stat-pill .stat-val {
            font-size: .92rem;
            font-weight: 800;
            line-height: 1.2;
        }
        .stat-pill .stat-sub {
            font-size: .60rem;
            font-weight: 600;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

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
        .kpi-card.kpi-bonus  { border-top: 3px solid #f59e0b; background: #fffbeb; }
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
            z-index: 1070 !important;
            min-width: 195px !important;
        }
        .dropdown-item {
            font-size: 12px !important;
            font-weight: 600 !important;
            color: #475569 !important;
            padding: 8px 16px !important;
            transition: all 0.15s ease-in-out !important;
            white-space: nowrap !important;
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

        /* Elevate active row and sticky action cell when dropdown is opened so rows below don't paint over it */
        .premium-table tr:has(.dropdown.show),
        .premium-table tr.dropdown-open {
            position: relative !important;
            z-index: 1060 !important;
        }
        .premium-table tr:has(.dropdown.show) td:last-child,
        .premium-table td:has(.dropdown.show),
        .premium-table tr.dropdown-open td:last-child,
        .premium-table td.dropdown-open {
            z-index: 1065 !important;
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
                        <a class="btn btn-outline-success px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ route('sale.return.create_direct') }}" style="border-radius: 8px;" title="Direct Return without invoice">
                            <i class="fas fa-plus-circle"></i> + Direct Return
                        </a>
                        <a class="btn btn-outline-primary px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                            href="{{ url('bookings') }}" style="border-radius: 8px;">
                            <i class="fas fa-bookmark"></i> Bookings
                        </a>
                        <a class="btn btn-outline-warning px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1 text-dark"
                            href="{{ route('sale.bulk_discount') }}" style="border-radius: 8px;" title="Bulk Discount & Tier Review Tool">
                            <i class="fas fa-tags text-warning"></i> Bulk Discounts
                        </a>
                        @can('sales.create')
                            <button type="button" id="btnOpenIssueSampleModal" 
                                class="btn px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1 text-white"
                                style="background: #7c3aed; border-color: #6d28d9; border-radius: 8px; cursor: pointer;"
                                data-toggle="modal" data-target="#issueSampleModal"
                                data-bs-toggle="modal" data-bs-target="#issueSampleModal">
                                <i class="fas fa-vial"></i> Issue Sample
                            </button>
                            <a class="btn btn-primary px-3 shadow-sm fw-medium d-inline-flex align-items-center justify-content-center gap-1"
                                href="{{ route('sale.add') }}" style="border-radius: 8px;">
                                <i class="fas fa-plus"></i> Add Sale
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- LINE 1: Primary Revenue Flow Waterfall (Gross sale, Sale return, Sale after return, Total discount, After sale) --}}
                @php 
                    $rawGross = (float)($stats['raw_gross'] ?? 0);
                    $retGross = (float)($stats['return_gross'] ?? 0);
                    $retNet = (float)($stats['total_returned'] ?? 0);
                    $displayRet = $retGross > 0 ? $retGross : $retNet;
                    $auditNet = (float)($stats['audit_net'] ?? max(0, $rawGross - $retGross));
                    $rawDisc = (float)($stats['raw_discount'] ?? ($stats['total_discount'] ?? 0));
                    $netTot = (float)($stats['total_net'] ?? ($rawGross - $retNet - $rawDisc));
                @endphp
                <div class="row g-2 mb-2 align-items-stretch">
                    {{-- 1. Gross Sale --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #f8fafc; border-color: #cbd5e1;">
                            <div class="stat-label text-secondary"><i class="fas fa-file-invoice-dollar me-1"></i>Gross Sale</div>
                            <div class="stat-val text-dark" id="statRawGross">Rs {{ $rawGross == floor($rawGross) ? number_format($rawGross, 0) : number_format($rawGross, 2) }}</div>
                            <div class="stat-sub text-muted" id="statRawGrossSub" title="Actual Trade Price (Pre-Return & Pre-Discount)">Billed Trade Price</div>
                        </div>
                    </div>

                    {{-- 2. Sale Return (-4,718) --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #fef2f2; border-color: #fca5a5;">
                            <div class="stat-label text-danger"><i class="fas fa-rotate-left me-1"></i>Sale Return</div>
                            <div class="stat-val text-danger" id="statSalesReturn">-Rs {{ $displayRet == floor($displayRet) ? number_format($displayRet, 0) : number_format($displayRet, 2) }}</div>
                            <div class="stat-sub text-danger opacity-75" id="statSalesReturnSub">@if($retGross > 0 && $retNet > 0 && $retGross != $retNet) Net Refund: Rs {{ number_format($retNet, 0) }} @else Returns Deducted @endif</div>
                        </div>
                    </div>

                    {{-- 3. Sale After Return (Audit Net: 196,333) --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #eff6ff; border-color: #93c5fd;">
                            <div class="stat-label text-primary"><i class="fas fa-balance-scale me-1"></i>Sale After Return</div>
                            <div class="stat-val text-primary" id="statAuditNet">Rs {{ $auditNet == floor($auditNet) ? number_format($auditNet, 0) : number_format($auditNet, 2) }}</div>
                            <div class="stat-sub text-primary opacity-75" id="statAuditNetSub">Audit Net · Gross - Return</div>
                        </div>
                    </div>

                    {{-- 4. Total Discount (-40,940) --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #fff1f2; border-color: #fecdd3;">
                            <div class="stat-label text-danger"><i class="fas fa-tags me-1"></i>Total Discount</div>
                            <div class="stat-val text-danger" id="statTopTotalDiscount">-Rs {{ $rawDisc == floor($rawDisc) ? number_format($rawDisc, 0) : number_format($rawDisc, 2) }}</div>
                            <div class="stat-sub text-danger opacity-75">All Deductions</div>
                        </div>
                    </div>

                    {{-- 5. After Sale (155,865) --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #ecfdf5; border-color: #a7f3d0;">
                            <div class="stat-label text-success"><i class="fas fa-receipt me-1"></i>After Sale</div>
                            <div class="stat-val text-success" id="statTotalNet">Rs {{ $netTot == floor($netTot) ? number_format($netTot, 0) : number_format($netTot, 2) }}</div>
                            <div class="stat-sub text-success opacity-75" id="statGrossRevenueSub">Realized Net Revenue</div>
                        </div>
                    </div>

                    {{-- 6. WOS (Worth of Samples) --}}
                    @php 
                        $wosAmt = (float)($stats['total_wos_amount'] ?? 0); 
                        $wosQty = (int)($stats['total_samples_qty'] ?? 0);
                    @endphp
                    <div class="col-12 col-sm-6 col-md-4 col-lg" style="flex: 1 1 0; min-width: 140px;">
                        <div class="stat-pill h-100" style="background: #faf5ff; border-color: #ddd6fe;">
                            <div class="stat-label" style="color: #7c3aed;"><i class="fas fa-vial me-1"></i>WOS (Samples)</div>
                            <div class="stat-val" style="color: #6d28d9;" id="statTotalWos">Rs {{ $wosAmt == floor($wosAmt) ? number_format($wosAmt, 0) : number_format($wosAmt, 2) }}</div>
                            <div class="stat-sub opacity-75" style="color: #7c3aed;" id="statTotalWosSub">{{ number_format($wosQty, 0) }} Pcs Free (At Cost)</div>
                        </div>
                    </div>
                </div>

                {{-- LINE 2: Operational Volume (Invoice, Product Qty/Bonus) --}}
                <div class="row g-2 mb-2 align-items-stretch">
                    {{-- 1. Invoice --}}
                    <div class="col-6 col-md-6">
                        <div class="stat-pill h-100" style="background: #ffffff; border-color: #cbd5e1;">
                            <div class="stat-label text-secondary"><i class="fas fa-file-invoice me-1 text-info"></i>Invoice</div>
                            <div class="stat-val text-dark" id="statTotalCount">{{ number_format($stats['total_count'] ?? 0) }}</div>
                            <div id="statStatusCounts" class="stat-sub text-muted">
                                P:{{ $stats['posted_count'] ?? 0 }}
                                @if(($stats['booked_count'] ?? 0) > 0) &nbsp;B:{{ $stats['booked_count'] ?? 0 }} @endif
                                @if(($stats['returned_count'] ?? 0) > 0) &nbsp;R:{{ $stats['returned_count'] ?? 0 }} @endif
                            </div>
                        </div>
                    </div>

                    {{-- 2. Product Qty / Bonus --}}
                    <div class="col-6 col-md-6">
                        <div class="stat-pill h-100" style="background: #faf5ff; border-color: #d8b4fe;">
                            <div class="stat-label" style="color: #7c3aed;"><i class="fas fa-cubes me-1"></i>Product Qty / Bonus</div>
                            <div class="stat-val" style="color: #7c3aed;" id="statTotalQty">{{ number_format($stats['total_qty'] ?? 0) }} / {{ number_format($stats['total_bonus_qty'] ?? 0) }} Pcs</div>
                            <div class="stat-sub" style="color: #7c3aed; opacity: 0.85;">Sold / Bonus Units</div>
                        </div>
                    </div>
                </div>

                {{-- LINE 3: Costs & Breakdown (Product Cost, Bonus Cost, Doctor, Medical, Distributor, Other) --}}
                <div class="row g-2 mb-3 align-items-stretch">
                    {{-- 1. Product Cost --}}
                    @php $cogsTot = (float)($stats['total_cogs'] ?? 0); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #fffbeb; border-color: #fde68a;">
                            <div class="stat-label" style="color: #d97706 !important;"><i class="fas fa-boxes-stacked me-1"></i>Product Cost</div>
                            <div class="stat-val" style="color: #b45309;" id="statTotalCOGS">Rs {{ $cogsTot == floor($cogsTot) ? number_format($cogsTot, 0) : number_format($cogsTot, 2) }}</div>
                            <div class="stat-sub" style="color: #d97706;">Total COGS</div>
                        </div>
                    </div>

                    {{-- 2. Bonus Cost --}}
                    @php $bAmt = (float)($stats['total_bonus_amount'] ?? 0); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #fffbeb; border-color: #f59e0b;">
                            <div class="stat-label" style="color: #d97706 !important;"><i class="fas fa-gift me-1"></i>Bonus Cost</div>
                            <div class="stat-val" style="color: #b45309;" id="statTotalBonusAmount">Rs {{ $bAmt == floor($bAmt) ? number_format($bAmt, 0) : number_format($bAmt, 2) }}</div>
                            <div class="stat-sub" style="color: #d97706;" id="statBonusAmountSub">At Cost · {{ number_format($stats['total_bonus_qty'] ?? 0) }} Pcs</div>
                        </div>
                    </div>

                    {{-- 3. Doctor --}}
                    @php $docDisc = (float)($stats['discount_doctor'] ?? 0); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #faf5ff; border-color: #e9d5ff;">
                            <div class="stat-label" style="color: #7c3aed;"><i class="fas fa-user-doctor me-1"></i>Doctor</div>
                            <div class="stat-val" style="color: #7c3aed;" id="statDiscountDoc">Rs {{ $docDisc == floor($docDisc) ? number_format($docDisc, 0) : number_format($docDisc, 2) }}</div>
                            <div class="stat-sub" style="color: #7c3aed; opacity: 0.75;">Clinic Concessions</div>
                        </div>
                    </div>

                    {{-- 4. Medical --}}
                    @php $mDisc = (float)($stats['discount_medical'] ?? 0); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #ecfdf5; border-color: #a7f3d0;">
                            <div class="stat-label text-success"><i class="fas fa-prescription-bottle-medical me-1"></i>Medical</div>
                            <div class="stat-val text-success" id="statDiscountMed">Rs {{ $mDisc == floor($mDisc) ? number_format($mDisc, 0) : number_format($mDisc, 2) }}</div>
                            <div class="stat-sub text-success opacity-75">Pharmacy Subsidies</div>
                        </div>
                    </div>

                    {{-- 5. Distributor --}}
                    @php $distDisc = (float)($stats['discount_distribution'] ?? 0); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #eff6ff; border-color: #bfdbfe;">
                            <div class="stat-label text-primary"><i class="fas fa-truck-fast me-1"></i>Distributor</div>
                            <div class="stat-val text-primary" id="statDiscountDist">Rs {{ $distDisc == floor($distDisc) ? number_format($distDisc, 0) : number_format($distDisc, 2) }}</div>
                            <div class="stat-sub text-primary opacity-75">Wholesale Subsidies</div>
                        </div>
                    </div>

                    {{-- 6. Other --}}
                    @php $oDisc = (float)($stats['discount_other'] ?? ($stats['additional_discount'] ?? 0)); @endphp
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="stat-pill h-100" style="background: #fffbeb; border-color: #fde68a;">
                            <div class="stat-label" style="color: #b45309;"><i class="fas fa-file-invoice-dollar me-1"></i>Other</div>
                            <div class="stat-val" style="color: #b45309;" id="statDiscountOther">Rs {{ $oDisc == floor($oDisc) ? number_format($oDisc, 0) : number_format($oDisc, 2) }}</div>
                            <div class="stat-sub" style="color: #b45309; opacity: 0.75;">Extra / Manual</div>
                        </div>
                    </div>
                </div>

                {{-- Hidden fallback holders for JS compatibility --}}
                <div style="display:none;" id="statGrossProfitBox">
                    <span id="statGrossProfitLabel"></span>
                    <span id="statTotalGrossProfit"></span>
                    <span id="statGrossProfitSub"></span>
                    <span id="statInvoicedNet"></span>
                    <span id="statEffectiveGross"></span>
                    <span id="statTotalDiscount"></span>
                </div>

                {{-- Status Filter Pills --}}
                <div class="mb-4 sales-status-pills">
                    <button type="button" data-status="all"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'all' || !request('status') ? 'btn-secondary' : 'btn-outline-secondary' }} rounded-3 shadow-sm px-3 fw-bold">
                        All <span class="badge bg-white text-dark ms-1" id="statBadgeAll">{{ $stats['total_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="posted"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'posted' ? 'btn-success' : 'btn-outline-success' }} rounded-3 shadow-sm px-3 fw-bold">
                        Posted <span class="badge bg-white text-success ms-1" id="statBadgePosted">{{ $stats['posted_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="draft"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'draft' ? 'btn-warning text-dark' : 'btn-outline-warning' }} rounded-3 shadow-sm px-3 fw-bold">
                        Draft <span class="badge bg-white text-dark ms-1" id="statBadgeDraft">{{ $stats['draft_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="booked"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'booked' ? 'btn-info text-white' : 'btn-outline-info' }} rounded-3 shadow-sm px-3 fw-bold">
                        Booked <span class="badge bg-white text-info ms-1" id="statBadgeBooked">{{ $stats['booked_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="returned"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'returned' ? 'btn-danger' : 'btn-outline-danger' }} rounded-3 shadow-sm px-3 fw-bold">
                        Returned <span class="badge bg-white text-danger ms-1" id="statBadgeReturned">{{ $stats['returned_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="direct_returns"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'direct_returns' ? 'btn-danger' : 'btn-outline-danger' }} rounded-3 shadow-sm px-3 fw-bold">
                        <i class="fas fa-undo-alt me-1"></i> Direct Returns <span class="badge bg-white text-danger ms-1" id="statBadgeDirectReturns">{{ $stats['direct_returns_count'] ?? 0 }}</span>
                    </button>
                    <button type="button" data-status="samples"
                        class="btn btn-sm status-filter-btn {{ request('status') == 'samples' ? 'btn-purple text-white' : 'btn-outline-secondary' }} rounded-3 shadow-sm px-3 fw-bold" style="border-color: #8b5cf6; color: #7c3aed;">
                        <i class="fas fa-vial me-1"></i> Samples <span class="badge bg-white text-dark ms-1" id="statBadgeSamples">{{ $stats['samples_count'] ?? 0 }}</span>
                    </button>
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
                                        <div style="width: 105px; flex-shrink: 0;">
                                            <select id="quick_filter" class="form-select text-center" title="Quick Filter">
                                                <option value="custom">Range</option>
                                                <option value="sep2026">Sep 2026 (Audit)</option>
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
                                        <div style="width: 105px; flex-shrink: 0;">
                                            <select class="form-select text-center" name="customer_type" id="filter_customer_type" title="Customer Type">
                                                <option value="">All Types</option>
                                                @if(isset($customerTypes) && count($customerTypes))
                                                    @foreach($customerTypes as $ct)
                                                        <option value="{{ $ct }}" {{ request('customer_type') == $ct ? 'selected' : '' }}>{{ $ct }}</option>
                                                    @endforeach
                                                @else
                                                    <option value="Doctors (DG khan)">Doctors (DG khan)</option>
                                                    <option value="Medical Store">Medical Store</option>
                                                    <option value="doctor">Doctors</option>
                                                @endif
                                            </select>
                                        </div>

                                        {{-- Zone --}}
                                        <div style="width: 95px; flex-shrink: 0;">
                                            <select class="form-select text-center" name="zone_id" id="filter_zone_id" title="Zone">
                                                <option value="">All Zones</option>
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

            // Elevate row & sticky cell z-index when Action dropdown opens
            $(document).on('show.bs.dropdown', '.premium-table .dropdown', function() {
                $(this).closest('tr').addClass('dropdown-open').css('z-index', 1060);
                $(this).closest('td').addClass('dropdown-open').css('z-index', 1065);
            });
            $(document).on('hidden.bs.dropdown', '.premium-table .dropdown', function() {
                $(this).closest('tr').removeClass('dropdown-open').css('z-index', '');
                $(this).closest('td').removeClass('dropdown-open').css('z-index', '');
            });

            // Mobile Filter Panel Toggle
            $('#toggleFilterPanel').on('click', function() {
                $('#filterPanelContainer').slideToggle(200);
            });

            let currentStatus = '{{ request("status") ?? "all" }}';

            // Core AJAX Filter Function
            function applySalesFilter() {
                const $btn = $('#btnSearch');
                const origHtml = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Searching...');

                // Ensure Flatpickr values are synced to hidden inputs before serializing
                let $fromElem = $('#filter_from_date');
                let $fromAlt = $fromElem.next('input.flatpickr-input');
                if ($fromAlt.length) {
                    if (!$fromAlt.val()) {
                        $fromElem.val('');
                    } else if (!$fromElem.val()) {
                        $fromElem.val($fromAlt.val());
                    }
                }
                let $toElem = $('#filter_to_date');
                let $toAlt = $toElem.next('input.flatpickr-input');
                if ($toAlt.length) {
                    if (!$toAlt.val()) {
                        $toElem.val('');
                    } else if (!$toElem.val()) {
                        $toElem.val($toAlt.val());
                    }
                }

                let formData = $('#filterForm').serialize();
                let statusVal = currentStatus;
                if (statusVal && statusVal !== 'all') {
                    formData += '&status=' + encodeURIComponent(statusVal);
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
                            let fmt = function(n) {
                                let num = parseFloat(n) || 0;
                                let decimals = (num % 1 !== 0) ? 2 : 0;
                                return num.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: 2});
                            };

                            let rawGross = parseFloat(response.stats.raw_gross || 0);
                            let rawDisc = parseFloat(response.stats.raw_discount !== undefined ? response.stats.raw_discount : (response.stats.total_discount || 0));
                            let invoicedNet = parseFloat(response.stats.invoiced_net !== undefined ? response.stats.invoiced_net : (rawGross - rawDisc));
                            let retGross = parseFloat(response.stats.return_gross || 0);
                            let retNet = parseFloat(response.stats.total_returned || 0);
                            let effGross = parseFloat(response.stats.effective_gross || 0);
                            let netVal = parseFloat(response.stats.total_net || 0);
                            let cogsVal = parseFloat(response.stats.total_cogs || 0);
                            let bonusAmtVal = parseFloat(response.stats.total_bonus_amount || 0);
                            let bonusQtyVal = parseFloat(response.stats.total_bonus_qty || 0);
                            let soldQtyVal = parseFloat(response.stats.total_qty || 0);

                            let displayRet = retGross > 0 ? retGross : retNet;
                            let auditNet = parseFloat(response.stats.audit_net !== undefined ? response.stats.audit_net : Math.max(0, rawGross - retGross));

                            $('#statRawGross').text('Rs ' + fmt(rawGross));
                            $('#statAuditNet').text('Rs ' + fmt(auditNet));
                            $('#statSalesReturn').text((displayRet > 0 ? '-Rs ' : 'Rs ') + fmt(displayRet));
                            $('#statSalesReturnSub').text((retGross > 0 && retNet > 0 && retGross !== retNet) ? ('Net Refund: Rs ' + fmt(retNet)) : 'Returns Deducted');
                            $('#statTopTotalDiscount, #statTotalDiscount').text((rawDisc > 0 ? '-Rs ' : 'Rs ') + fmt(rawDisc));
                            $('#statInvoicedNet').text('Rs ' + fmt(invoicedNet));
                            $('#statEffectiveGross').text('Rs ' + fmt(effGross));
                            $('#statTotalNet').text('Rs ' + fmt(netVal));
                            $('#statTotalCOGS').text('Rs ' + fmt(cogsVal));
                            $('#statTotalBonusAmount').text('Rs ' + fmt(bonusAmtVal));
                            $('#statBonusAmountSub').text('At Cost · ' + fmt(bonusQtyVal) + ' Pcs');

                            let wosAmtVal = parseFloat(response.stats.total_wos_amount || 0);
                            let wosQtyVal = parseFloat(response.stats.total_samples_qty || 0);
                            $('#statTotalWos').text('Rs ' + fmt(wosAmtVal));
                            $('#statTotalWosSub').text(fmt(wosQtyVal) + ' Pcs Free (At Cost)');
                            if (response.stats.samples_count !== undefined) {
                                $('#statBadgeSamples').text(Number(response.stats.samples_count || 0).toLocaleString());
                            }

                            // Gross Profit
                            let gpVal = parseFloat(response.stats.total_gross_profit || 0);
                            let gpColor = gpVal >= 0 ? '#059669' : '#dc2626';
                            let gpBg = gpVal >= 0 ? '#f0fdf4' : '#fef2f2';
                            let gpBorder = gpVal >= 0 ? '#86efac' : '#fca5a5';
                            let gpSign = gpVal < 0 ? '-' : '';
                            $('#statTotalGrossProfit').text(gpSign + 'Rs ' + fmt(Math.abs(gpVal))).css('color', gpColor);
                            $('#statGrossProfitLabel, #statGrossProfitSub').css('color', gpColor);
                            $('#statGrossProfitBox').css({'background': gpBg, 'border-color': gpBorder});

                            $('#statTotalCount').text(Number(response.stats.total_count || 0).toLocaleString());
                            $('#statTotalQty').text(fmt(soldQtyVal) + ' / ' + fmt(bonusQtyVal) + ' Pcs');

                            // Row 2 Discount Cards
                            let totDisc   = parseFloat(response.stats.total_discount || 0);
                            let medDisc   = parseFloat(response.stats.discount_medical || 0);
                            let distDisc  = parseFloat(response.stats.discount_distribution || 0);
                            let docDisc   = parseFloat(response.stats.discount_doctor || 0);
                            let otherDisc = parseFloat(response.stats.discount_other !== undefined ? response.stats.discount_other : (response.stats.additional_discount || 0));

                            $('#statTotalDiscount').text('Rs ' + fmt(totDisc));
                            $('#statTotalDiscountHeader').text('Rs ' + fmt(totDisc));
                            $('#statDiscountMed').text('Rs ' + fmt(medDisc));
                            $('#statDiscountDist').text('Rs ' + fmt(distDisc));
                            $('#statDiscountDoc').text('Rs ' + fmt(docDisc));
                            $('#statDiscountOther').text('Rs ' + fmt(otherDisc));

                            // Status Filter Badges
                            $('#statBadgeAll').text(Number(response.stats.total_count || 0).toLocaleString());
                            $('#statBadgePosted').text(Number(response.stats.posted_count || 0).toLocaleString());
                            $('#statBadgeDraft').text(Number(response.stats.draft_count || 0).toLocaleString());
                            $('#statBadgeBooked').text(Number(response.stats.booked_count || 0).toLocaleString());
                            $('#statBadgeReturned').text(Number(response.stats.returned_count || 0).toLocaleString());
                            if (response.stats.direct_returns_count !== undefined) {
                                $('#statBadgeDirectReturns').text(Number(response.stats.direct_returns_count || 0).toLocaleString());
                            }

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
                let startStr = '';
                let endStr = '';

                let formatDate = function(d) {
                    let year = d.getFullYear();
                    let month = String(d.getMonth() + 1).padStart(2, '0');
                    let day = String(d.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };

                if (val === 'sep2026') {
                    startStr = '2026-09-01';
                    endStr = '2026-09-30';
                } else if (val === 'daily') {
                    start = new Date();
                    end = new Date();
                    startStr = formatDate(start);
                    endStr = formatDate(end);
                } else if (val === 'weekly') {
                    let day = today.getDay();
                    let diff = today.getDate() - day + (day === 0 ? -6 : 1);
                    start = new Date(today.setDate(diff));
                    end = new Date();
                    startStr = formatDate(start);
                    endStr = formatDate(end);
                } else if (val === 'monthly') {
                    start = new Date(today.getFullYear(), today.getMonth(), 1);
                    end = new Date();
                    startStr = formatDate(start);
                    endStr = formatDate(end);
                } else if (val === 'yearly') {
                    start = new Date(today.getFullYear(), 0, 1);
                    end = new Date();
                    startStr = formatDate(start);
                    endStr = formatDate(end);
                }

                let pickerFrom = document.getElementById('filter_from_date') ? document.getElementById('filter_from_date')._flatpickr : null;
                let pickerTo = document.getElementById('filter_to_date') ? document.getElementById('filter_to_date')._flatpickr : null;

                if (pickerFrom) pickerFrom.setDate(startStr, true);
                else $("#filter_from_date").val(startStr);

                if (pickerTo) pickerTo.setDate(endStr, true);
                else $("#filter_to_date").val(endStr);

                applySalesFilter();
            });

            // Direct listeners on Select2, Zone, Customer Type dropdowns
            $('#filter_customer_id').on('change select2:select select2:clear', function() {
                applySalesFilter();
            });
            $('#filter_zone_id, #filter_customer_type').on('change', function() {
                applySalesFilter();
            });

            // Delegated fallback for dynamic changes
            $(document).on('change', '#filter_zone_id, #filter_customer_type, #filter_customer_id', function() {
                applySalesFilter();
            });

            // Auto-filter on Date inputs change
            $(document).on('change input', '#filter_from_date, #filter_to_date', function() {
                applySalesFilter();
            });

            // Bind Flatpickr onChange listener safely
            function bindFlatpickrChange() {
                let fromPicker = document.getElementById('filter_from_date') ? document.getElementById('filter_from_date')._flatpickr : null;
                let toPicker = document.getElementById('filter_to_date') ? document.getElementById('filter_to_date')._flatpickr : null;
                if (fromPicker && fromPicker.config) {
                    fromPicker.config.onChange = [function() { applySalesFilter(); }];
                }
                if (toPicker && toPicker.config) {
                    toPicker.config.onChange = [function() { applySalesFilter(); }];
                }
            }
            bindFlatpickrChange();
            setTimeout(bindFlatpickrChange, 300);
            setTimeout(bindFlatpickrChange, 800);
            setTimeout(bindFlatpickrChange, 1500);

            // Debounced search on Bill# / Reference typing
            let filterDebounce = null;
            $(document).on('input', '#filter_bill_no, #filter_reference', function() {
                clearTimeout(filterDebounce);
                filterDebounce = setTimeout(function() {
                    applySalesFilter();
                }, 500);
            });

            // Status filter button click (AJAX - no page reload)
            $(document).on('click', '.status-filter-btn', function(e) {
                e.preventDefault();
                let status = $(this).data('status');
                currentStatus = status;

                $('.status-filter-btn').each(function() {
                    let s = $(this).data('status');
                    if (s === status) {
                        if (s === 'all') $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
                        else if (s === 'posted') $(this).removeClass('btn-outline-success').addClass('btn-success');
                        else if (s === 'draft') $(this).removeClass('btn-outline-warning').addClass('btn-warning text-dark');
                        else if (s === 'booked') $(this).removeClass('btn-outline-info').addClass('btn-info text-white');
                        else if (s === 'returned') $(this).removeClass('btn-outline-danger').addClass('btn-danger');
                        else if (s === 'direct_returns') $(this).removeClass('btn-outline-danger').addClass('btn-danger text-white');
                    } else {
                        if (s === 'all') $(this).removeClass('btn-secondary').addClass('btn-outline-secondary');
                        else if (s === 'posted') $(this).removeClass('btn-success').addClass('btn-outline-success');
                        else if (s === 'draft') $(this).removeClass('btn-warning text-dark').addClass('btn-outline-warning');
                        else if (s === 'booked') $(this).removeClass('btn-info text-white').addClass('btn-outline-info');
                        else if (s === 'returned') $(this).removeClass('btn-danger').addClass('btn-outline-danger');
                        else if (s === 'direct_returns') $(this).removeClass('btn-danger text-white').addClass('btn-outline-danger');
                    }
                });

                applySalesFilter();
            });

            // Trigger search on button click & enter key
            $(document).on('click', '#btnSearch', function(e) {
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

        // ============================================================
        // DELETE SALE — Simple Confirmation + Form Submit
        // ============================================================
        function confirmDeleteSale(saleId, invoiceNo, saleStatus) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to delete sale ${invoiceNo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('delete-sale-form');
                    form.action = `/sales/${saleId}`;
                    form.submit();
                }
            });
        }
    </script>

    {{-- Hidden form for DELETE Sale (method spoofing) --}}
    <form id="delete-sale-form" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- ======================================================== --}}
    {{-- ISSUE PHYSICIAN / MEDICAL SAMPLE MODAL                    --}}
    {{-- ======================================================== --}}
    <style>
        #issueSampleModal .select2-container {
            width: 100% !important;
        }
        #issueSampleModal .select2-container--default .select2-selection--single {
            height: 33px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            display: flex !important;
            align-items: center !important;
            background-color: #ffffff !important;
        }
        #issueSampleModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 31px !important;
            color: #0f172a !important;
            font-size: 12.5px !important;
            padding-left: 8px !important;
            font-weight: 500 !important;
        }
        #issueSampleModal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 31px !important;
            right: 6px !important;
        }
        #issueSampleModal .select2-dropdown {
            z-index: 1065 !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }
        #issueSampleModal .modal-content {
            border-radius: 12px !important;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
            border: none !important;
        }
    </style>

    <div class="modal fade" id="issueSampleModal" tabindex="-1" aria-labelledby="issueSampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                {{-- Modal Header --}}
                <div class="modal-header text-white align-items-center" style="background: linear-gradient(135deg, #6d28d9 0%, #4f46e5 100%); padding: 14px 20px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-purple" style="width: 38px; height: 38px; color: #6d28d9; font-size: 16px; flex-shrink: 0;">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0 text-white" style="font-size: 1.15rem; letter-spacing: -0.2px;">
                                Issue Physician / Medical Sample
                            </h5>
                            <div class="text-white opacity-85 small" style="font-size: 11.5px;">
                                Free Promotional Stock <span class="mx-1">·</span> Stock Auto-Deducted <span class="mx-1">·</span> WOS Tracked at Purchase Cost
                            </div>
                        </div>
                    </div>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 1.6rem; text-shadow: none; border: none; background: transparent; cursor: pointer;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="issueSampleForm" method="POST" action="{{ route('samples.store') }}">
                    @csrf
                    <div class="modal-body p-3 p-md-4" style="background: #f8fafc; max-height: calc(88vh - 130px); overflow-y: auto;">
                        
                        {{-- Card 1: Doctor / Recipient & Logistics --}}
                        <div class="card border-0 shadow-sm mb-3" style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0 !important;">
                            <div class="card-body p-3">
                                <div class="row g-2 align-items-end">
                                    {{-- 1. Doctor / Clinic Dropdown with Select2 --}}
                                    <div class="col-md-5">
                                        <label class="form-label font-weight-bold text-dark small mb-1 d-flex justify-content-between">
                                            <span><i class="fas fa-user-md text-primary mr-1"></i> Select Registered Doctor / Hospital</span>
                                            <span class="text-muted" style="font-size: 10px;">(Auto-fills details)</span>
                                        </label>
                                        <select name="customer_id" id="sample_customer_id" class="form-control form-control-sm select2-sample-doctor" style="width: 100%;">
                                            <option value="">-- Search & Choose Doctor / Hospital --</option>
                                            @php
                                                $docGroups = $customers->groupBy(function($c) {
                                                    return $c->customer_type ?: 'General Doctors';
                                                });
                                            @endphp
                                            @foreach($docGroups as $groupName => $groupCusts)
                                                <optgroup label="🩺 {{ $groupName }}">
                                                    @foreach($groupCusts as $c)
                                                        <option value="{{ $c->id }}" data-name="{{ $c->customer_name }}">
                                                            {{ $c->customer_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 2. Doctor Name / Clinic Text Input (auto-filled or custom) --}}
                                    <div class="col-md-4">
                                        <label class="form-label font-weight-bold text-dark small mb-1">
                                            <i class="fas fa-stethoscope text-info mr-1"></i> Doctor / Clinic Name (On Challan) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="doctor_name" id="sample_doctor_name" class="form-control form-control-sm" placeholder="e.g. DR ZAHID LATIF / City Clinic" required style="border-color: #cbd5e1; height: 33px; font-weight: 500;">
                                    </div>

                                    {{-- 3. Medical Rep (MR) --}}
                                    <div class="col-md-3">
                                        <label class="form-label font-weight-bold text-dark small mb-1">
                                            <i class="fas fa-user-tie text-secondary mr-1"></i> Medical Rep (MR) / Delivered By
                                        </label>
                                        <input type="text" name="mr_name" class="form-control form-control-sm" placeholder="e.g. Ali (MR)" style="border-color: #cbd5e1; height: 33px;">
                                    </div>
                                </div>

                                <div class="row g-2 align-items-end mt-2 pt-2 border-top" style="border-color: #f1f5f9 !important;">
                                    {{-- 4. Warehouse (Default to Sale's Warehouse) --}}
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold text-dark small mb-1">
                                            <i class="fas fa-warehouse text-warning mr-1"></i> Stock Source Warehouse <span class="text-danger">*</span>
                                        </label>
                                        @php $defaultWhId = auth()->user()->warehouse_id ?? 1; @endphp
                                        <select name="warehouse_id" id="sample_warehouse_id" class="form-control form-control-sm" required style="border-color: #cbd5e1; height: 33px; font-weight: 500;">
                                            @foreach($allWarehouses as $wh)
                                                @php 
                                                    $whTitle = $wh->warehouse_name ?? ($wh->name ?? ('Warehouse #' . $wh->id));
                                                    if (!empty($wh->location)) {
                                                        $whTitle .= ' (' . $wh->location . ')';
                                                    }
                                                @endphp
                                                <option value="{{ $wh->id }}" {{ $wh->id == $defaultWhId ? 'selected' : '' }}>
                                                    🏬 {{ $whTitle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 5. Issue Date --}}
                                    <div class="col-md-6">
                                        <label class="form-label font-weight-bold text-dark small mb-1">
                                            <i class="fas fa-calendar-day text-primary mr-1"></i> Issue Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="sample_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required style="border-color: #cbd5e1; height: 33px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Sample Medicines Repeater --}}
                        <div class="card border-0 shadow-sm mb-3" style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0 !important; overflow: hidden;">
                            <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom" style="background: #f8fafc !important;">
                                <div>
                                    <span class="font-weight-bold small text-dark"><i class="fas fa-boxes-stacked mr-1 text-primary"></i> Sample Medicines</span>
                                    <span class="text-muted small ml-1 d-none d-sm-inline">(Search medicines, verify stock, enter sample pieces)</span>
                                </div>
                                <button type="button" class="btn btn-sm text-white px-3 font-weight-bold" id="btnAddSampleRow" style="background: #7c3aed; font-size: 12px; border-radius: 6px;">
                                    <i class="fas fa-plus mr-1"></i> Add Medicine
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0" id="sampleItemsTable" style="margin-bottom: 0;">
                                        <thead style="background: #f1f5f9; font-size: 11.5px; text-transform: uppercase; color: #475569; letter-spacing: 0.3px;">
                                            <tr>
                                                <th style="min-width: 320px; padding: 9px 12px;">Medicine / Product</th>
                                                <th style="width: 140px; padding: 9px 10px;" class="text-center">Available Stock</th>
                                                <th style="width: 130px; padding: 9px 10px;" class="text-center">Sample Qty (Pcs) <span class="text-danger">*</span></th>
                                                <th style="width: 130px; padding: 9px 10px;" class="text-right">Unit Cost</th>
                                                <th style="width: 140px; padding: 9px 12px;" class="text-right">Est. WOS</th>
                                                <th style="width: 50px; padding: 9px 10px;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sampleItemsBody">
                                            {{-- Rows dynamically appended --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Notes & Financial Summary --}}
                        <div class="row g-3 align-items-stretch">
                            <div class="col-md-6">
                                <div class="card h-100 border-0 shadow-sm" style="background: #ffffff; border-radius: 10px; border: 1px solid #e2e8f0 !important;">
                                    <div class="card-body p-3">
                                        <label class="form-label font-weight-bold text-dark small mb-1">
                                            <i class="fas fa-clipboard-list text-secondary mr-1"></i> Clinical Notes / Purpose of Sample
                                        </label>
                                        <textarea name="remarks" class="form-control form-control-sm" rows="3" placeholder="e.g. Promotional physician samples for Bonirex & Mebiotic launch with Dr. Salman" style="border-color: #cbd5e1; border-radius: 6px;"></textarea>
                                        <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                            <i class="fas fa-info-circle text-info mr-1"></i> These remarks will be printed on the Physician Sample Delivery Slip.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 shadow-sm h-100 d-flex flex-column justify-content-between" style="background: #faf5ff; border: 1.5px solid #d8b4fe; border-radius: 10px;">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom" style="border-color: #e9d5ff !important;">
                                            <span class="font-weight-bold text-dark small"><i class="fas fa-calculator mr-1" style="color: #7c3aed;"></i> Sample Value Summary</span>
                                            <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 11px;">Rs 0.00 (100% FREE)</span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1.5">
                                            <span class="text-muted font-weight-medium">Total Sample Pieces:</span>
                                            <strong class="text-dark font-monospace fs-6" id="sampleTotalPcs">0 Pcs</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span style="color: #6d28d9; font-weight: 700;">Worth of Samples (WOS):</span>
                                            <strong class="font-monospace" style="color: #6d28d9; font-size: 1.25rem;" id="sampleTotalWos">Rs 0.00</strong>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-top mt-2" style="border-color: #e9d5ff !important;">
                                        <small class="text-muted d-block" style="font-size: 11px; line-height: 1.35;">
                                            <i class="fas fa-shield-halved text-success mr-1"></i> Stock will deduct from warehouse instantly. WOS (Purchase Cost) will deduct from Gross Profit in P&L.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-white border-top py-2.5 px-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-light btn-sm px-3 text-secondary border font-weight-bold" data-dismiss="modal" data-bs-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                        <button type="submit" class="btn btn-sm text-white px-4 font-weight-bold shadow-sm" id="btnSubmitSample" style="background: linear-gradient(135deg, #7c3aed, #6d28d9); border: none; font-size: 13px; padding-top: 7px; padding-bottom: 7px;">
                            <i class="fas fa-check-circle mr-1"></i> Issue Sample Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Product Options Hidden Select for JS Repeater --}}
    <select id="sampleProductOptionsTpl" style="display: none !important;">
        <option value="">-- Search & Select Medicine --</option>
        @foreach($activeProducts as $ap)
            @php
                $cost = (float)($ap->purchase_price_per_piece ?: 0);
                $stockMap = $ap->warehouseStocks ? $ap->warehouseStocks->pluck('total_pieces', 'warehouse_id')->toArray() : [];
            @endphp
            <option value="{{ $ap->id }}" 
                    data-purch-price="{{ $cost }}"
                    data-stocks="{{ json_encode($stockMap) }}"
                    data-ppb="{{ (float)($ap->pieces_per_box ?: 1) }}">
                {{ $ap->item_name }} (Cost: Rs {{ number_format($cost, 2) }})
            </option>
        @endforeach
    </select>

    <script>
        $(document).ready(function() {
            // Explicit click handler to open Issue Sample Modal
            $(document).on('click', '#btnOpenIssueSampleModal', function(e) {
                e.preventDefault();
                $('#issueSampleModal').modal('show');
            });

            // Initialize Doctor Select2 when modal opens
            $('#issueSampleModal').on('shown.bs.modal', function() {
                $('#sample_customer_id').select2({
                    dropdownParent: $('#issueSampleModal'),
                    placeholder: '-- Search & Choose Doctor / Hospital --',
                    allowClear: true,
                    width: '100%'
                });

                if ($('#sampleItemsBody tr').length === 0) {
                    addSampleRow();
                } else {
                    $('#sampleItemsBody .sample-product-select:not(.select2-hidden-accessible)').select2({
                        dropdownParent: $('#issueSampleModal'),
                        placeholder: '-- Search & Select Medicine --',
                        width: '100%'
                    });
                }
            });

            // When a Doctor is selected from dropdown, auto-fill doctor name
            $(document).on('change select2:select', '#sample_customer_id', function() {
                let docName = $(this).find('option:selected').data('name');
                if (docName) {
                    $('#sample_doctor_name').val(docName);
                }
            });

            // Function to update live stock badge for a row
            function updateRowStockBadge($row) {
                let $opt = $row.find('.sample-product-select option:selected');
                let prodId = $opt.val();
                if (!prodId) {
                    $row.find('.sample-stock-display').html('<span class="badge badge-light border text-muted">Select Product</span>');
                    return;
                }
                let stocks = $opt.data('stocks') || {};
                let whId = $('#sample_warehouse_id').val();
                let availPcs = stocks[whId] !== undefined ? parseFloat(stocks[whId]) : 0;
                
                let badgeHtml = '';
                if (availPcs > 0) {
                    badgeHtml = `<span class="badge badge-success font-weight-bold px-2 py-1"><i class="fas fa-check-circle mr-1"></i> ${availPcs} Pcs</span>`;
                } else if (availPcs === 0) {
                    badgeHtml = `<span class="badge badge-warning text-dark font-weight-bold px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i> 0 Pcs</span>`;
                } else {
                    badgeHtml = `<span class="badge badge-danger font-weight-bold px-2 py-1"><i class="fas fa-times-circle mr-1"></i> ${availPcs} Pcs</span>`;
                }
                $row.find('.sample-stock-display').html(badgeHtml);
            }

            // On Warehouse change, update all stock badges
            $(document).on('change', '#sample_warehouse_id', function() {
                $('#sampleItemsBody tr').each(function() {
                    updateRowStockBadge($(this));
                });
            });

            // Function to add a new sample item row
            function addSampleRow() {
                let optionsHtml = $('#sampleProductOptionsTpl').html() || '';
                let rowHtml = `
                    <tr class="sample-row align-middle">
                        <td style="padding: 6px 10px; min-width: 300px;">
                            <select name="product_id[]" class="form-control form-control-sm sample-product-select" style="width: 100%;" required>
                                ${optionsHtml}
                            </select>
                        </td>
                        <td class="text-center align-middle sample-stock-display" style="padding: 6px 10px;">
                            <span class="badge badge-light border text-muted">Select Product</span>
                        </td>
                        <td style="padding: 6px 10px; width: 130px;">
                            <input type="number" name="qty[]" class="form-control form-control-sm text-center font-weight-bold sample-qty-input" min="1" step="1" value="1" required style="border-color: #cbd5e1; height: 33px;">
                        </td>
                        <td class="text-right font-monospace sample-cost-display align-middle text-muted" style="text-align: right; padding: 6px 10px; font-size: 13px;">Rs 0.00</td>
                        <td class="text-right font-monospace font-weight-bold sample-wos-display align-middle" style="text-align: right; color: #6d28d9; font-size: 13.5px; padding: 6px 10px;">Rs 0.00</td>
                        <td class="text-center align-middle" style="padding: 6px 10px;">
                            <button type="button" class="btn btn-outline-danger btn-sm py-0.5 px-1.5 btn-remove-sample-row rounded-circle" title="Remove Item">
                                <i class="fas fa-trash-alt" style="font-size: 11px;"></i>
                            </button>
                        </td>
                    </tr>
                `;
                let $newRow = $(rowHtml);
                $('#sampleItemsBody').append($newRow);

                // Initialize Select2 on the new product select
                $newRow.find('.sample-product-select').select2({
                    dropdownParent: $('#issueSampleModal'),
                    placeholder: '-- Search & Select Medicine --',
                    width: '100%'
                });

                recalcSampleTotals();
            }

            // Add row button
            $('#btnAddSampleRow').on('click', function() {
                addSampleRow();
            });

            // Remove row button
            $(document).on('click', '.btn-remove-sample-row', function() {
                if ($('#sampleItemsBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    recalcSampleTotals();
                } else {
                    Swal.fire('Notice', 'At least one medicine is required in the sample.', 'info');
                }
            });

            // On Product or Qty change
            $(document).on('change keyup select2:select', '.sample-product-select, .sample-qty-input', function() {
                let $row = $(this).closest('tr');
                let $select = $row.find('.sample-product-select');
                let $opt = $select.find('option:selected');
                let purchPrice = parseFloat($opt.data('purch-price') || 0);
                let qty = parseFloat($row.find('.sample-qty-input').val() || 0);

                let wos = qty * purchPrice;
                $row.find('.sample-cost-display').text('Rs ' + purchPrice.toFixed(2));
                $row.find('.sample-wos-display').text('Rs ' + wos.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                updateRowStockBadge($row);
                recalcSampleTotals();
            });

            function recalcSampleTotals() {
                let totalPcs = 0;
                let totalWos = 0;

                $('#sampleItemsBody tr').each(function() {
                    let $row = $(this);
                    let $opt = $row.find('.sample-product-select option:selected');
                    let purchPrice = parseFloat($opt.data('purch-price') || 0);
                    let qty = parseFloat($row.find('.sample-qty-input').val() || 0);

                    if (qty > 0) {
                        totalPcs += qty;
                        totalWos += (qty * purchPrice);
                    }
                });

                $('#sampleTotalPcs').text(totalPcs + ' Pcs');
                $('#sampleTotalWos').text('Rs ' + totalWos.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }

            // AJAX Form Submit for Issue Sample
            $('#issueSampleForm').on('submit', function(e) {
                e.preventDefault();
                let $btn = $('#btnSubmitSample');
                let origText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Issuing...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(resp) {
                        $btn.prop('disabled', false).html(origText);
                        if (resp.success) {
                            $('#issueSampleModal').modal('hide');
                            $('#issueSampleForm')[0].reset();
                            $('#sampleItemsBody').empty();

                            Swal.fire({
                                icon: 'success',
                                title: 'Sample Issued!',
                                text: resp.message || 'Medical sample issued successfully. Stock updated.',
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Refresh Sales listing & stats
                            applySalesFilter();
                        } else {
                            Swal.fire('Error', resp.message || 'Failed to issue sample.', 'error');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).html(origText);
                        let errMsg = 'Failed to issue sample.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire('Validation Error', errMsg, 'error');
                    }
                });
            });
        });

        // Delete Sample Confirmation
        function confirmDeleteSample(sampleId, sampleNo) {
            Swal.fire({
                title: 'Delete Sample?',
                text: `Do you want to delete Sample #${sampleNo}? Stock will be restored to warehouse.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete & Restore Stock',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/samples/${sampleId}`,
                        method: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}'
                        },
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire('Deleted', resp.message || 'Sample deleted and stock restored.', 'success');
                                applySalesFilter();
                            } else {
                                Swal.fire('Error', resp.message || 'Failed to delete sample.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to delete sample.', 'error');
                        }
                    });
                }
            });
        }
    </script>

@endsection