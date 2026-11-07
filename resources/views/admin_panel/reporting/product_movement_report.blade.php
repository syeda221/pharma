@extends('admin_panel.layout.app')

@section('content')
<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendors/select2/css/select2.min.css') }}">

<style>
    /* Base Container Styling (Matching Equity Ledger Standard) */
    .movement-container {
        padding: 10px 14px;
        background: #f1f5f9;
        min-height: calc(100vh - 75px);
        width: 100%;
    }

    /* Page Header Bar */
    .page-title-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        padding: 2px 4px;
    }

    /* Filter Card */
    .filter-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        padding: 12px 14px;
        margin-bottom: 12px;
    }
    .filter-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #475569;
        margin-bottom: 3px;
        display: block;
    }
    .quick-preset-btn {
        font-size: 0.68rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        line-height: 1.3;
        transition: all 0.15s ease;
    }
    .quick-preset-btn:hover, .quick-preset-btn.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }

    /* Select2 Precise Strict Styling (Constrained to Parent Column Width) */
    .select2-container {
        width: 100% !important;
        max-width: 100% !important;
        display: block !important;
    }
    .select2-container .select2-selection--single {
        height: 33px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding: 2px 8px !important;
        background-color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 27px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        padding-left: 0 !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        white-space: nowrap !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px !important;
        right: 6px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
    }
    .select2-dropdown {
        border: 1px solid #3b82f6 !important;
        border-radius: 6px !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
        font-size: 0.78rem !important;
        z-index: 1060 !important;
        background: #ffffff !important;
        overflow: hidden !important;
    }
    .select2-search--dropdown {
        padding: 5px !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        padding: 4px 8px !important;
        font-size: 0.78rem !important;
        outline: none !important;
    }
    .select2-results__options {
        max-height: 220px !important;
    }
    .select2-results__option {
        padding: 5px 8px !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2563eb !important;
    }

    /* Standard Controls */
    .form-control-custom {
        height: 33px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 8px;
        color: #1e293b;
        background-color: #ffffff;
    }
    .form-control-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
    }

    /* KPI Cards Matching Exact Equity Ledger Overview Cards */
    .overview-card {
        padding: 7px 10px;
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
        box-shadow: 0 3px 6px rgba(0,0,0,0.06);
    }
    .overview-card .ov-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 2px;
    }
    .overview-card .ov-label {
        font-size: .63rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .overview-card .ov-val {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .overview-card .ov-sub {
        font-size: .62rem;
        font-weight: 600;
        margin-top: 1px;
        white-space: nowrap;
    }

    /* Card Themes */
    .theme-sold { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .theme-bonus { background: #ecfeff; border-color: #a5f3fc; color: #0891b2; }
    .theme-dispatch { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .theme-returns { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
    .theme-net-sold { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .theme-net-bonus { background: #fffbeb; border-color: #fde68a; color: #d97706; }
    .theme-net-deliv { background: #f0fdf4; border-color: #86efac; color: #15803d; }
    .theme-revenue { background: #faf5ff; border-color: #e9d5ff; color: #7c3aed; }

    /* Master Table Container */
    .report-table-card {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .report-table-toolbar {
        padding: 8px 12px;
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .report-table-wrap {
        overflow-x: auto;
        max-height: 68vh;
        width: 100%;
    }

    /* Clean Cohesive Table Header with Unified Height & Crisp Borders */
    .movement-table {
        width: 100%;
        margin-bottom: 0;
        font-size: 0.76rem;
        border-collapse: separate;
        border-spacing: 0;
    }
    .movement-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #1e293b !important;
        color: #ffffff !important;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 7px 6px;
        border-right: 1px solid #334155;
        border-bottom: 1px solid #334155;
        vertical-align: middle;
        white-space: nowrap;
    }
    .movement-table thead th.group-cell {
        text-align: center;
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.4px;
        background-color: #0f172a !important;
    }
    .movement-table tbody td {
        padding: 6px 8px;
        border-bottom: 1px solid #f1f5f9;
        border-right: 1px solid #f8fafc;
        vertical-align: middle;
        white-space: nowrap;
        font-size: 0.76rem;
    }
    .movement-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .movement-table tfoot td {
        position: sticky;
        bottom: 0;
        z-index: 9;
        background-color: #e2e8f0 !important;
        color: #0f172a !important;
        font-weight: 800;
        font-size: 0.76rem;
        padding: 8px 6px;
        border-top: 2px solid #cbd5e1;
        border-right: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    /* Print View */
    @media print {
        body * { visibility: hidden; }
        #printableArea, #printableArea * { visibility: visible; }
        #printableArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: #ffffff;
            padding: 10px;
        }
        .no-print { display: none !important; }
        .report-table-wrap { max-height: none !important; overflow: visible !important; }
        .movement-table thead th { background: #e2e8f0 !important; color: #000 !important; }
        .movement-table tfoot td { background: #f1f5f9 !important; color: #000 !important; }
    }
</style>

@php
    $latestSale = \App\Models\Sale::max('created_at');
    $defaultFrom = $latestSale ? \Carbon\Carbon::parse($latestSale)->startOfMonth()->format('Y-m-d') : date('Y-m-01');
    $defaultTo = $latestSale ? \Carbon\Carbon::parse($latestSale)->endOfMonth()->format('Y-m-d') : date('Y-m-d');
@endphp

<div class="movement-container">
    <!-- Top Action Title Bar -->
    <div class="page-title-bar no-print">
        <div>
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center" style="font-size: 1.05rem;">
                <i class="fa-solid fa-boxes-packing text-primary me-2"></i>
                Product Movement (Sales, Bonus & Returns Audit Report)
            </h5>
            <span class="text-muted small" style="font-size: 0.73rem;">Real-time audit of Billed Pieces, Free Bonus, Sold Returns, Bonus Returns, and Net Delivered Volume.</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-success fw-bold px-3" id="exportCsvBtn" style="height: 32px; border-radius: 6px;">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel
            </button>
            <button class="btn btn-sm btn-dark fw-bold px-3" id="printReportBtn" style="height: 32px; border-radius: 6px;">
                <i class="fa-solid fa-print me-1"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Filter Card: Clean 2-Row Spacious Layout -->
    <div class="filter-card no-print">
        <form id="filterForm">
            <!-- Row 1: Date Range, Return Mode, and Action Buttons -->
            <div class="row g-2 align-items-end mb-2 pb-2 border-bottom">
                <!-- Date Range & Quick Presets -->
                <div class="col-lg-5 col-md-6">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="filter-label mb-0">Date Range</span>
                        <div class="d-flex gap-1">
                            <button type="button" class="quick-preset-btn" data-preset="today">Today</button>
                            <button type="button" class="quick-preset-btn active" data-preset="this_month">This Month</button>
                            <button type="button" class="quick-preset-btn" data-preset="last_month">Last Month</button>
                            <button type="button" class="quick-preset-btn" data-preset="all">All Time</button>
                        </div>
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="date" class="form-control form-control-custom" id="from_date" name="from_date" value="{{ $defaultFrom }}">
                        <span class="input-group-text bg-light text-muted px-2" style="font-size: 0.75rem;">to</span>
                        <input type="date" class="form-control form-control-custom" id="to_date" name="to_date" value="{{ $defaultTo }}">
                    </div>
                </div>

                <!-- Return Mode Filter -->
                <div class="col-lg-3 col-md-3">
                    <label class="filter-label">Return Calculation Mode</label>
                    <select class="form-select form-control-custom" id="return_mode" name="return_mode">
                        <option value="sale_date" selected>Audit Mode (by Sale Date)</option>
                        <option value="transaction_date">Accounts Mode (by Return Date)</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-lg-4 col-md-3 d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" id="applyFilterBtn" style="height: 33px; border-radius: 6px;">
                        <i class="fa-solid fa-filter me-1"></i> Apply Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-light border px-3 fw-bold text-secondary" id="resetFilterBtn" style="height: 33px; border-radius: 6px;" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Row 2: Searchable Dropdowns (Zone, Customer, Product) with Equal Full Width -->
            <div class="row g-2">
                <!-- Zone Filter -->
                <div class="col-md-4">
                    <label class="filter-label">Zone</label>
                    <select class="form-select select2-filter" id="zone_id" name="zone_id">
                        <option value="all" selected>-- All Zones --</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->zone }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Filter -->
                <div class="col-md-4">
                    <label class="filter-label">Customer</label>
                    <select class="form-select select2-filter" id="customer_id" name="customer_id">
                        <option value="all" selected>-- All Customers --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->customer_name }} ({{ $c->zone ?: '-' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Filter -->
                <div class="col-md-4">
                    <label class="filter-label">Product</label>
                    <select class="form-select select2-filter" id="product_id" name="product_id">
                        <option value="all" selected>-- All Products --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->item_name }} ({{ $p->item_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Printable Area Wrapper -->
    <div id="printableArea">
        <div class="d-none d-print-block mb-3 border-bottom pb-2">
            <h3 class="fw-bold mb-0 text-center">PRODUCT MOVEMENT & SALES/BONUS AUDIT REPORT</h3>
            <p class="text-center text-muted small mb-0" id="printSubTitle">All Zones | {{ $defaultFrom }} to {{ $defaultTo }}</p>
        </div>

        <!-- Balanced 4 x 2 KPI Cards Grid (Matching Equity Ledger Standard) -->
        <div class="row g-2 mb-2">
            <!-- Card 1: Gross Sold -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-sold">
                    <div class="ov-header">
                        <span class="ov-label text-dark"><i class="fa-solid fa-cart-shopping text-primary me-1"></i>Gross Sold (Billed)</span>
                    </div>
                    <div class="ov-val text-dark" id="kpiSoldPcs">0 Pcs</div>
                    <div class="ov-sub text-muted">Total Billed Invoiced Pieces</div>
                </div>
            </div>

            <!-- Card 2: Bonus Dispatched -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-bonus">
                    <div class="ov-header">
                        <span class="ov-label" style="color: #0891b2;"><i class="fa-solid fa-gift me-1"></i>Bonus Dispatched</span>
                    </div>
                    <div class="ov-val" style="color: #0891b2;" id="kpiBonusPcs">0 Pcs</div>
                    <div class="ov-sub text-muted" id="kpiBonusRatio">0% of billed volume</div>
                </div>
            </div>

            <!-- Card 3: Total Dispatched -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-dispatch">
                    <div class="ov-header">
                        <span class="ov-label text-primary"><i class="fa-solid fa-truck-fast me-1"></i>Total Dispatched</span>
                    </div>
                    <div class="ov-val text-primary" id="kpiDispatchedPcs">0 Pcs</div>
                    <div class="ov-sub text-muted">Gross Outflow (Sold + Bonus)</div>
                </div>
            </div>

            <!-- Card 4: Customer Returns -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-returns">
                    <div class="ov-header">
                        <span class="ov-label text-danger"><i class="fa-solid fa-rotate-left me-1"></i>Customer Returns</span>
                    </div>
                    <div class="ov-val text-danger" id="kpiReturnPcs">0 Pcs</div>
                    <div class="ov-sub text-danger" id="kpiReturnSub">0 Sold Ret . 0 Bonus Ret</div>
                </div>
            </div>
        </div>

        <div class="row g-2 mb-3">
            <!-- Card 5: Net Sold -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-net-sold">
                    <div class="ov-header">
                        <span class="ov-label" style="color: #4338ca;"><i class="fa-solid fa-receipt me-1"></i>Net Sold Pieces</span>
                    </div>
                    <div class="ov-val" style="color: #4338ca;" id="kpiNetSoldPcs">0 Pcs</div>
                    <div class="ov-sub text-muted">Sold - Sold Returned</div>
                </div>
            </div>

            <!-- Card 6: Net Bonus -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-net-bonus">
                    <div class="ov-header">
                        <span class="ov-label" style="color: #b45309;"><i class="fa-solid fa-hand-holding-dollar me-1"></i>Net Bonus In-Market</span>
                    </div>
                    <div class="ov-val" style="color: #d97706;" id="kpiNetBonusPcs">0 Pcs</div>
                    <div class="ov-sub text-muted">Bonus - Bonus Returned</div>
                </div>
            </div>

            <!-- Card 7: Net Delivered -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-net-deliv">
                    <div class="ov-header">
                        <span class="ov-label text-success"><i class="fa-solid fa-cubes-stacked me-1"></i>Net Delivered Total</span>
                    </div>
                    <div class="ov-val text-success" id="kpiNetDeliveredPcs">0 Pcs</div>
                    <div class="ov-sub text-success">Retained (Net Sold + Net Bonus)</div>
                </div>
            </div>

            <!-- Card 8: Net Revenue -->
            <div class="col-6 col-md-3">
                <div class="overview-card theme-revenue">
                    <div class="ov-header">
                        <span class="ov-label" style="color: #7c3aed;"><i class="fa-solid fa-coins me-1"></i>Realized Net Revenue</span>
                    </div>
                    <div class="ov-val" style="color: #7c3aed;" id="kpiNetRevenue">Rs 0.00</div>
                    <div class="ov-sub text-muted" id="kpiInvoicesCount">0 Invoices across 0 Products</div>
                </div>
            </div>
        </div>

        <!-- Master Table Card with Crisp Seamless Header -->
        <div class="report-table-card">
            <div class="report-table-toolbar no-print">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: 0.80rem;">
                        <i class="fa-solid fa-list-check me-1 text-primary"></i> Product Movement Master Ledger
                    </span>
                    <span class="badge bg-primary px-2 py-0.5 rounded-pill" id="productsBadge" style="font-size: 0.68rem;">0 Products</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0 py-0"><i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 11px;"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 form-control-custom" id="tableSearchInput" placeholder="Quick search product name..." style="height: 30px;">
                    </div>
                </div>
            </div>

            <div class="report-table-wrap">
                <table class="table table-bordered table-hover align-middle movement-table" id="movementTable">
                    <thead>
                        <tr>
                            <th rowspan="2" class="text-center" style="width: 35px;">#</th>
                            <th rowspan="2" style="width: 75px;">CODE</th>
                            <th rowspan="2">PRODUCT NAME</th>
                            <th rowspan="2" style="width: 110px;">BRAND / CAT</th>
                            <th colspan="3" class="group-cell">
                                <span class="badge bg-primary px-1.5 py-0.5 me-1" style="font-size: 9px;">OUTFLOW</span> DISPATCHED
                            </th>
                            <th colspan="3" class="group-cell">
                                <span class="badge bg-danger px-1.5 py-0.5 me-1" style="font-size: 9px;">RETURNS</span> CUSTOMER RETURNS
                            </th>
                            <th colspan="4" class="group-cell">
                                <span class="badge bg-success px-1.5 py-0.5 me-1" style="font-size: 9px;">IN-MARKET</span> NET DELIVERED
                            </th>
                            <th colspan="4" class="group-cell">
                                <span class="badge bg-secondary px-1.5 py-0.5 me-1" style="font-size: 9px;">FINANCIALS</span> REVENUE WATERFALL
                            </th>
                        </tr>
                        <tr>
                            <!-- Dispatched columns -->
                            <th class="text-center" style="width: 65px;" title="Billed Sold Quantity">SOLD</th>
                            <th class="text-center" style="width: 65px;" title="Muft Bonus Given">BONUS</th>
                            <th class="text-center" style="width: 75px;" title="Total Pieces (Sold + Bonus)">TOTAL OUT</th>

                            <!-- Returns columns -->
                            <th class="text-center" style="width: 65px;" title="Returned Billed Pieces">RET SOLD</th>
                            <th class="text-center" style="width: 65px;" title="Returned Free Pieces">RET BONUS</th>
                            <th class="text-center" style="width: 70px;" title="Total Returned Pieces">TOTAL RET</th>

                            <!-- Net Delivered columns -->
                            <th class="text-center" style="width: 65px;" title="Sold - Ret Sold">NET SOLD</th>
                            <th class="text-center" style="width: 65px;" title="Bonus - Ret Bonus">NET BONUS</th>
                            <th class="text-center" style="width: 75px;" title="Net Sold + Net Bonus">NET DELIV</th>
                            <th style="width: 120px;" title="Delivered Volume in Cartons and Loose Pieces">DELIV PACKING</th>

                            <!-- Financial columns -->
                            <th class="text-end" style="width: 75px;">AVG TP</th>
                            <th class="text-end" style="width: 85px;">GROSS TP</th>
                            <th class="text-end" style="width: 75px;">DISC</th>
                            <th class="text-end" style="width: 95px;">NET REVENUE</th>
                        </tr>
                    </thead>
                    <tbody id="movementTableBody">
                        <tr>
                            <td colspan="18" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                                <p class="mb-0 fw-semibold">Loading Product Movement Data...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="movementTableFoot">
                        <!-- Summary Totals Injected via JS -->
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- Select2 JS (Loaded in section js after jQuery) -->
<script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>

<script>
    let reportData = null;

    $(document).ready(function() {
        // Initialize Select2 strictly matching container width (dropdownAutoWidth: false)
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-filter').select2({
                width: '100%',
                dropdownAutoWidth: false
            });

            // Trigger re-fetch on select2 change
            $('.select2-filter, #return_mode').on('change', function() {
                loadProductMovement();
            });
        }

        // Quick Presets
        $('.quick-preset-btn').on('click', function() {
            $('.quick-preset-btn').removeClass('active');
            $(this).addClass('active');
            const preset = $(this).data('preset');
            const now = new Date();

            if (preset === 'today') {
                const str = now.toISOString().split('T')[0];
                $('#from_date').val(str);
                $('#to_date').val(str);
            } else if (preset === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
                $('#from_date').val(firstDay);
                $('#to_date').val(lastDay);
            } else if (preset === 'last_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().split('T')[0];
                const lastDay = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().split('T')[0];
                $('#from_date').val(firstDay);
                $('#to_date').val(lastDay);
            } else if (preset === 'all') {
                $('#from_date').val('');
                $('#to_date').val('');
            }
            loadProductMovement();
        });

        // Date Inputs change
        $('#from_date, #to_date').on('change', function() {
            $('.quick-preset-btn').removeClass('active');
            loadProductMovement();
        });

        // Filter button
        $('#applyFilterBtn').on('click', function() {
            loadProductMovement();
        });

        // Reset Filter button
        $('#resetFilterBtn').on('click', function() {
            $('#from_date').val('{{ $defaultFrom }}');
            $('#to_date').val('{{ $defaultTo }}');
            $('#return_mode').val('sale_date');
            $('#tableSearchInput').val('');
            $('.quick-preset-btn').removeClass('active');
            $('[data-preset="this_month"]').addClass('active');

            if (typeof $.fn.select2 !== 'undefined') {
                $('#zone_id').val('all').trigger('change.select2');
                $('#customer_id').val('all').trigger('change.select2');
                $('#product_id').val('all').trigger('change.select2');
            } else {
                $('#zone_id').val('all');
                $('#customer_id').val('all');
                $('#product_id').val('all');
            }
            loadProductMovement();
        });

        // Client-side quick search in table
        $('#tableSearchInput').on('input', function() {
            filterTableRows($(this).val().toLowerCase().trim());
        });

        // Print Report
        $('#printReportBtn').on('click', function() {
            window.print();
        });

        // Export CSV
        $('#exportCsvBtn').on('click', function() {
            exportToCSV();
        });

        // Initial Load immediately
        loadProductMovement();
    });

    function loadProductMovement() {
        const tbody = $('#movementTableBody');
        tbody.html(`
            <tr>
                <td colspan="18" class="text-center py-4 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 text-primary"></i>
                    <p class="mb-0 fw-semibold">Loading Product Movement Data...</p>
                </td>
            </tr>
        `);

        const params = {
            from_date: $('#from_date').val(),
            to_date: $('#to_date').val(),
            zone_id: $('#zone_id').val() || 'all',
            customer_id: $('#customer_id').val() || 'all',
            product_id: $('#product_id').val() || 'all',
            return_mode: $('#return_mode').val() || 'sale_date'
        };

        const zoneText = $('#zone_id option:selected').text() || 'All Zones';
        const fromStr = params.from_date || 'Start';
        const toStr = params.to_date || 'End';
        $('#printSubTitle').text(`${zoneText} | ${fromStr} to ${toStr}`);

        $.ajax({
            url: "{{ route('report.product_movement.fetch') }}",
            type: "GET",
            data: params,
            dataType: "json",
            success: function(data) {
                reportData = data;
                renderKPIs(data.summary);
                renderTable(data.products, data.summary);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching movement report:', error, xhr.responseText);
                tbody.html(`
                    <tr>
                        <td colspan="18" class="text-center py-4 text-danger">
                            <i class="fa-solid fa-triangle-exclamation fa-2x mb-2"></i>
                            <p class="mb-0 fw-semibold">Failed to load data. Please try again or check connection.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderKPIs(summary) {
        $('#kpiSoldPcs').text(`${summary.total_sold_qty} Pcs`);
        $('#kpiBonusPcs').text(`${summary.total_bonus_qty} Pcs`);

        const soldVal = parseFloat(summary.total_sold_qty.replace(/,/g, '')) || 0;
        const bonusVal = parseFloat(summary.total_bonus_qty.replace(/,/g, '')) || 0;
        const bRatio = soldVal > 0 ? ((bonusVal / soldVal) * 100).toFixed(1) : '0';
        $('#kpiBonusRatio').text(`${bRatio}% of billed volume`);

        $('#kpiDispatchedPcs').text(`${summary.total_dispatched_qty} Pcs`);
        $('#kpiReturnPcs').text(`${summary.total_return_qty} Pcs`);
        $('#kpiReturnSub').text(`${summary.total_return_sold_qty} Sold Ret . ${summary.total_return_bonus_qty} Bonus Ret`);

        $('#kpiNetSoldPcs').text(`${summary.net_sold_qty} Pcs`);
        $('#kpiNetBonusPcs').text(`${summary.net_bonus_qty} Pcs`);
        $('#kpiNetDeliveredPcs').text(`${summary.net_delivered_qty} Pcs`);
        $('#kpiNetRevenue').text(summary.grand_net_revenue);
        $('#kpiInvoicesCount').text(`${summary.total_invoices} Invoices across ${summary.total_products} Products`);
        $('#productsBadge').text(`${summary.total_products} Products`);
    }

    function renderTable(products, summary) {
        const tbody = $('#movementTableBody');
        const tfoot = $('#movementTableFoot');

        if (!products || products.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="18" class="text-center py-4 text-muted">
                        <i class="fa-solid fa-inbox fa-2x mb-2 text-secondary"></i>
                        <p class="mb-0 fw-semibold">No sales or returns found for the selected criteria.</p>
                    </td>
                </tr>
            `);
            tfoot.html('');
            return;
        }

        let bodyHtml = '';
        products.forEach((p, index) => {
            const soldQty = parseFloat(p.sold_qty) || 0;
            const bonusQty = parseFloat(p.bonus_qty) || 0;
            const dispQty = parseFloat(p.total_dispatched) || 0;
            const retSold = parseFloat(p.return_sold_qty) || 0;
            const retBonus = parseFloat(p.return_bonus_qty) || 0;
            const retTotal = parseFloat(p.total_return_qty) || 0;
            const netSold = parseFloat(p.net_sold_qty) || 0;
            const netBonus = parseFloat(p.net_bonus_qty) || 0;
            const netDeliv = parseFloat(p.net_delivered_qty) || 0;

            bodyHtml += `
                <tr class="product-row" data-search="${(p.item_code || '').toLowerCase()} ${(p.product_name || '').toLowerCase()}">
                    <td class="text-center text-muted fw-bold">${index + 1}</td>
                    <td><span class="font-monospace text-primary fw-bold">${p.item_code}</span></td>
                    <td>
                        <span class="fw-bold text-dark">${p.product_name}</span>
                        ${p.pieces_per_box > 0 ? `<span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">${p.pieces_per_box}/ctn</span>` : ''}
                    </td>
                    <td class="text-muted small">${p.brand_name} / ${p.category_name}</td>

                    <!-- Dispatched (Clean Typography) -->
                    <td class="text-center fw-bold text-dark">${soldQty.toLocaleString()}</td>
                    <td class="text-center fw-bold" style="color: #0891b2;">${bonusQty > 0 ? bonusQty.toLocaleString() : '<span class="text-muted">-</span>'}</td>
                    <td class="text-center fw-bold text-primary">${dispQty.toLocaleString()}</td>

                    <!-- Customer Returns -->
                    <td class="text-center fw-bold ${retSold > 0 ? 'text-danger' : 'text-muted'}">${retSold > 0 ? retSold.toLocaleString() : '-'}</td>
                    <td class="text-center fw-bold ${retBonus > 0 ? 'text-warning' : 'text-muted'}" style="${retBonus > 0 ? 'color: #d97706 !important;' : ''}">${retBonus > 0 ? retBonus.toLocaleString() : '-'}</td>
                    <td class="text-center fw-bold ${retTotal > 0 ? 'text-danger' : 'text-muted'}">${retTotal > 0 ? retTotal.toLocaleString() : '-'}</td>

                    <!-- Net Realized In-Market -->
                    <td class="text-center fw-bold" style="color: #4338ca;">${netSold.toLocaleString()}</td>
                    <td class="text-center fw-bold" style="color: #059669;">${netBonus > 0 ? netBonus.toLocaleString() : (netBonus < 0 ? netBonus.toLocaleString() : '<span class="text-muted">-</span>')}</td>
                    <td class="text-center fw-bold" style="color: #15803d; background-color: #f0fdf4;">${netDeliv.toLocaleString()}</td>
                    <td class="small fw-semibold text-secondary"><i class="fa-solid fa-box text-muted me-1"></i>${p.carton_display}</td>

                    <!-- Financials -->
                    <td class="text-end text-muted">Rs ${parseFloat(p.avg_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-end fw-semibold text-dark">Rs ${parseFloat(p.gross_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-end text-danger">${p.discount_amount > 0 ? 'Rs ' + parseFloat(p.discount_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '<span class="text-muted">-</span>'}</td>
                    <td class="text-end fw-bold text-dark">Rs ${parseFloat(p.net_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
        tbody.html(bodyHtml);

        // Render Grand Total Foot
        tfoot.html(`
            <tr>
                <td colspan="4" class="text-uppercase text-dark fw-bold">Grand Total (${summary.total_products} Products)</td>

                <!-- Dispatched Totals -->
                <td class="text-center text-dark fw-bold">${summary.total_sold_qty}</td>
                <td class="text-center text-info fw-bold" style="color: #0891b2 !important;">${summary.total_bonus_qty}</td>
                <td class="text-center text-primary fw-bold">${summary.total_dispatched_qty}</td>

                <!-- Return Totals -->
                <td class="text-center text-danger fw-bold">${summary.total_return_sold_qty}</td>
                <td class="text-center fw-bold" style="color: #d97706 !important;">${summary.total_return_bonus_qty}</td>
                <td class="text-center text-danger fw-bold">${summary.total_return_qty}</td>

                <!-- Net Totals -->
                <td class="text-center fw-bold" style="color: #4338ca !important;">${summary.net_sold_qty}</td>
                <td class="text-center fw-bold" style="color: #059669 !important;">${summary.net_bonus_qty}</td>
                <td class="text-center fw-bold text-success" style="background-color: #dcfce7 !important;">${summary.net_delivered_qty}</td>
                <td class="text-muted small fw-bold">Combined Packing</td>

                <!-- Financial Totals -->
                <td class="text-end text-muted">-</td>
                <td class="text-end fw-bold">${summary.grand_gross_amount}</td>
                <td class="text-end text-danger fw-bold">${summary.grand_discount_amount}</td>
                <td class="text-end text-dark fw-bold">${summary.grand_net_revenue}</td>
            </tr>
        `);
    }

    function filterTableRows(query) {
        $('.product-row').each(function() {
            const txt = $(this).data('search') || '';
            $(this).toggle(txt.includes(query));
        });
    }

    function exportToCSV() {
        if (!reportData || !reportData.products || reportData.products.length === 0) {
            alert('No data available to export.');
            return;
        }

        const headers = [
            '#', 'Code', 'Product Name', 'Brand', 'Category', 'Pieces/Box',
            'Sold Qty', 'Bonus Qty', 'Total Dispatched',
            'Return Sold Qty', 'Return Bonus Qty', 'Total Return Qty',
            'Net Sold Qty', 'Net Bonus Qty', 'Net Delivered Qty', 'Delivered Packing',
            'Avg TP', 'Gross Amount', 'Discount Amount', 'Net Revenue', 'Invoices Count'
        ];

        let csvContent = "data:text/csv;charset=utf-8," + headers.join(',') + "\n";

        reportData.products.forEach((p, idx) => {
            const row = [
                idx + 1,
                `"${p.item_code}"`,
                `"${p.product_name}"`,
                `"${p.brand_name}"`,
                `"${p.category_name}"`,
                p.pieces_per_box,
                p.sold_qty,
                p.bonus_qty,
                p.total_dispatched,
                p.return_sold_qty,
                p.return_bonus_qty,
                p.total_return_qty,
                p.net_sold_qty,
                p.net_bonus_qty,
                p.net_delivered_qty,
                `"${p.carton_display}"`,
                p.avg_price,
                p.gross_amount,
                p.discount_amount,
                p.net_revenue,
                p.invoice_count
            ];
            csvContent += row.join(',') + "\n";
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        const fromDate = $('#from_date').val() || 'start';
        const toDate = $('#to_date').val() || 'end';
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `Product_Movement_Report_${fromDate}_to_${toDate}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endsection
