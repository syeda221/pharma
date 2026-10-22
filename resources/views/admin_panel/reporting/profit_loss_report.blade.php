@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Standardized Sale Report Pattern Styling */
    .sale-report-container {
        padding: 10px 14px;
        background: #f1f5f9;
        min-height: calc(100vh - 75px);
    }
    .sale-filter-label {
        margin-right: 4px !important;
        margin-bottom: 0 !important;
        white-space: nowrap;
        font-weight: 700;
        font-size: .78rem;
        color: #475569;
    }
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
        height: 4px;
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
        min-width: 95px;
        padding: 6px 8px;
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
        padding: 8px 10px;
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

    /* Modern Overview Card for Discounts & Quantities */
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

    /* Expenses Toggle Group */
    .expense-toggle-group {
        display: inline-flex;
        align-items: center;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 20px;
        padding: 2px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
    }
    .btn-expense-mode {
        border: none;
        outline: none;
        background: transparent;
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
        user-select: none;
    }
    .btn-expense-mode:hover {
        color: #1e293b;
    }
    .btn-expense-mode.active-with {
        background: #ffffff;
        color: #dc2626 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }
    .btn-expense-mode.active-without {
        background: #ffffff;
        color: #059669 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    /* Mobile Cards Styling */
    .mob-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .mob-metric-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 10px 6px;
        text-align: center;
        height: 100%;
    }
    .mob-metric-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
    }
    .mob-metric-val {
        font-size: 14px;
        font-weight: 800;
    }

    /* Table Styling with Sticky Header */
    .sale-table-wrap {
        height: calc(100vh - 250px);
        max-height: calc(100vh - 250px);
        min-height: 380px;
        overflow-y: auto;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
    }
    .report-table {
        font-size: .78rem;
        margin-bottom: 0;
    }
    .report-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #1e293b !important;
        color: #ffffff !important;
        font-size: .75rem;
        font-weight: 700;
        padding: 9px 10px;
        border-bottom: 2px solid #334155;
        white-space: nowrap;
    }

    .partner1-pct-input::-webkit-inner-spin-button,
    .partner1-pct-input::-webkit-outer-spin-button,
    .partner2-pct-input::-webkit-inner-spin-button,
    .partner2-pct-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .partner1-pct-input, .partner2-pct-input {
        -moz-appearance: textfield;
    }

    /* Zone Pill Bar & Cards */
    .zone-pill-bar {
        display: flex;
        align-items: stretch;
        gap: 8px;
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 2px;
    }
    .zone-pill-bar::-webkit-scrollbar {
        height: 5px;
    }
    .zone-pill-bar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .zone-pill-bar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .zone-pill-bar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    .zone-pill {
        flex: 1 1 0px;
        min-width: 180px;
        max-width: 260px;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        cursor: pointer;
        transition: all 0.18s ease;
        position: relative;
        user-select: none;
    }
    .zone-pill:hover {
        border-color: #3b82f6 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.12);
        background: #fafbfc;
    }
    .zone-pill.active-zone {
        border: 1.5px solid #2563eb !important;
        background: #eff6ff !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.18) !important;
    }
    .zone-pill .zone-name {
        font-size: .75rem;
        font-weight: 700;
        color: #1e293b;
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .zone-pill .zone-badge {
        font-size: .62rem;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .zone-pill .zone-profit-label {
        font-size: .60rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #64748b;
        margin-bottom: 1px;
    }
    .zone-pill .zone-profit-val {
        font-size: .92rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .zone-pill .zone-rev-val {
        font-size: .75rem;
        font-weight: 700;
        color: #334155;
    }

    @media print {
        body { background: #ffffff !important; font-size: 11px; }
        .no-print, header, .sidebar, .navbar, footer { display: none !important; }
        .sale-report-container { padding: 0 !important; background: #fff !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; margin-bottom: 10px !important; }
    }
</style>

<div class="sale-report-container">

    {{-- DESKTOP FILTER HEADER CARD (d-none d-md-block Standard Pattern) --}}
    @can('profit.loss.filters.view')
    <div class="card border-0 shadow-sm mb-2 no-print d-none d-md-block" style="border-radius: 10px;">
        <div class="card-body py-2 px-3">
            <form id="profitLossFormDesk">
                
                {{-- Top Section: Left Title, Mid Dates with Gap, Last Buttons --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2 pb-2 border-bottom">
                    
                    {{-- Left Title --}}
                    <div class="d-flex align-items-center me-3">
                        <span class="fw-bold text-dark fs-6 text-nowrap" style="letter-spacing: -0.2px;">
                            <i class="fas fa-chart-pie text-primary me-2"></i>Equity Ledger
                        </span>
                    </div>

                    {{-- Mid Dates with Explicit Spacing Gap --}}
                    <div class="d-flex align-items-center me-auto flex-wrap" style="gap: 16px !important;">
                        <div class="d-flex align-items-center gap-1">
                            <label for="start_date_desk" class="sale-filter-label mb-0 ms-1 me-1">From:</label>
                            <input type="datetime-local" id="start_date_desk" class="form-control form-control-sm fw-bold startDateInput" value="{{ date('Y-m-d\T00:00') }}" style="height: 32px; width: 175px; font-size: .78rem; border-radius: 6px;">
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <label for="end_date_desk" class="sale-filter-label mb-0 ms-2 me-1">To:</label>
                            <input type="datetime-local" id="end_date_desk" class="form-control form-control-sm fw-bold endDateInput" value="{{ date('Y-m-d\T23:59') }}" style="height: 32px; width: 175px; font-size: .78rem; border-radius: 6px;">
                        </div>
                    </div>

                    {{-- Last Buttons with X-Axis Gap --}}
                    <div class="d-flex align-items-center ms-auto" style="gap: 10px !important;">
                        <button type="button" class="btn btn-primary btn-sm px-3 fw-bold d-inline-flex align-items-center btnSearchTrigger" style="height: 32px; border-radius: 6px; font-size: .78rem; margin-right: 8px !important;">
                            <i class="fas fa-search me-1"></i> Search
                        </button>
                        <button type="button" class="btn btn-light border btn-sm px-3 fw-bold text-secondary d-inline-flex align-items-center btnResetTrigger" style="height: 32px; border-radius: 6px; font-size: .78rem; margin-right: 8px !important;">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        @can('profit.loss.export')
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold d-inline-flex align-items-center btnPrintReport" style="height: 32px; border-radius: 6px; font-size: .78rem;">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                        @endcan
                    </div>
                </div>

                {{-- Bottom Section: Category, Customer, Product, Zone --}}
                <div class="row g-2">
                    <div class="col-md-3">
                        <label for="zone_id_desk" class="sale-filter-label mb-1">Zone:</label>
                        <select id="zone_id_desk" class="form-select form-select-sm select2 zoneSelect">
                            <option value="all">All Zones</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->zone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id_desk" class="sale-filter-label mb-1">Customer:</label>
                        <select id="customer_id_desk" class="form-select form-select-sm select2 customerSelect">
                            <option value="all" data-zone="">All Customers</option>
                            @foreach($customers as $cus)
                                <option value="{{ $cus->id }}" data-zone="{{ $cus->zone }}">{{ $cus->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category_id_desk" class="sale-filter-label mb-1">Category:</label>
                        <select id="category_id_desk" class="form-select form-select-sm select2 categorySelect">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="product_id_desk" class="sale-filter-label mb-1">Product:</label>
                        <select id="product_id_desk" class="form-select form-select-sm select2 productSelect">
                            <option value="all">All Products</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </form>
        </div>
    </div>
    @endcan

    {{-- MOBILE FILTER HEADER CARD (d-md-none With Top Margin) --}}
    @can('profit.loss.filters.view')
    <div class="card border-0 shadow-sm mb-3 no-print d-md-none mt-2" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form id="profitLossFormMob">
                <div class="row g-2">
                    <div class="col-12 mb-1">
                        <span class="fw-bold text-dark fs-6">
                            <i class="fas fa-chart-pie text-primary me-2"></i>Equity Ledger
                        </span>
                    </div>

                    {{-- 1. Start & End Date --}}
                    <div class="col-6 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">From</label>
                        <input type="datetime-local" id="start_date_mob" class="form-control form-control-sm startDateInput" value="{{ date('Y-m-d\T00:00') }}" style="font-size: 11px;">
                    </div>
                    <div class="col-6 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">To</label>
                        <input type="datetime-local" id="end_date_mob" class="form-control form-control-sm endDateInput" value="{{ date('Y-m-d\T23:59') }}" style="font-size: 11px;">
                    </div>

                    {{-- 2. Zone --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Zone</label>
                        <select class="form-select form-select-sm select2 zoneSelect">
                            <option value="all">All Zones</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->zone }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Customer --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Customer</label>
                        <select class="form-select form-select-sm select2 customerSelect">
                            <option value="all" data-zone="">All Customers</option>
                            @foreach($customers as $cus)
                                <option value="{{ $cus->id }}" data-zone="{{ $cus->zone }}">{{ $cus->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Category --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Category</label>
                        <select class="form-select form-select-sm select2 categorySelect">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Product --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Product</label>
                        <select class="form-select form-select-sm select2 productSelect">
                            <option value="all">All Products</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->item_code }} - {{ $prod->item_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Full Width Search Button --}}
                    <div class="col-12 my-2">
                        <button type="button" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm btnSearchTrigger" style="background-color: #3b82f6; border-color: #3b82f6; font-size: 13px;">
                            <i class="fas fa-search me-1"></i> Search Report
                        </button>
                    </div>

                    {{-- 6. Centralized Reset & Print Actions With Horizontal Gap --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-center gap-2 pt-1" style="gap: 10px !important;">
                            <button type="button" class="btn btn-light border btn-sm flex-fill fw-bold text-secondary btnResetTrigger" style="font-size: 11px; margin-right: 8px !important;">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            @can('profit.loss.export')
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill fw-bold btnPrintReport" style="font-size: 11px;">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endcan

    {{-- SECTION 1: FINANCIAL PERFORMANCE & MARGINS --}}
    @can('profit.loss.cards.view')
    <div class="card border-0 shadow-sm mb-2 no-print" style="border-radius: 10px; background: #ffffff;">
        <div class="card-body p-2">
            <div class="d-flex align-items-center justify-content-between mb-1.5 px-1 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark" style="font-size: .76rem; letter-spacing: -0.1px;">
                        <i class="fas fa-chart-line text-primary me-1.5"></i>Financial Performance
                    </span>
                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10px;">
                        Margin: <strong class="text-success" id="cardProfitMarginBadge">0.0%</strong>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-lg-inline" style="font-size: 11px;">
                        <i class="fas fa-coins text-warning me-1"></i>Revenue, Cost & Profit
                    </span>
                    {{-- Toggle: With Expenses vs Without Expenses --}}
                    <div class="expense-toggle-group" id="expenseToggleGroup" title="Toggle expenses deduction from Profit & Equity Share">
                        <button type="button" class="btn-expense-mode active-with" data-mode="with" id="btnModeWithExpenses">
                            <i class="fas fa-receipt"></i>
                            <span>With Expenses</span>
                        </button>
                        <button type="button" class="btn-expense-mode" data-mode="without" id="btnModeWithoutExpenses">
                            <i class="fas fa-eye-slash"></i>
                            <span>Without Expenses</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ROW 1: 8 Waterfall Operational KPI Cards (Matches Sale Screen Sequence) --}}
            <div class="summary-pill-bar mb-2">
                {{-- 1. Gross Sales (TP) --}}
                <div class="stat-pill" style="background: #f8fafc; border-color: #cbd5e1;">
                    <div class="stat-label text-secondary"><i class="fas fa-file-invoice-dollar me-1"></i>Gross Sales (TP)</div>
                    <div class="stat-val text-dark" id="cardRawGross">Rs 0</div>
                    <div class="stat-sub text-muted" id="cardRawGrossSub">Billed Trade Price</div>
                </div>

                {{-- 2. Sales Return --}}
                <div class="stat-pill" style="background: #fef2f2; border-color: #fca5a5;">
                    <div class="stat-label text-danger"><i class="fas fa-rotate-left me-1"></i>Sales Return</div>
                    <div class="stat-val text-danger" id="cardSalesReturn">-Rs 0</div>
                    <div class="stat-sub text-danger opacity-75" id="cardSalesReturnSub">Returns Deducted</div>
                </div>

                {{-- 3. Sale After Return (Audit Net) --}}
                <div class="stat-pill" style="background: #eff6ff; border-color: #93c5fd;">
                    <div class="stat-label text-primary"><i class="fas fa-balance-scale me-1"></i>Sale After Return</div>
                    <div class="stat-val text-primary" id="cardAuditNet">Rs 0</div>
                    <div class="stat-sub text-primary opacity-75" id="cardAuditNetSub">Audit Net · Gross - Return</div>
                </div>

                {{-- 4. Total Discount --}}
                <div class="stat-pill" style="background: #fff1f2; border-color: #fecdd3;">
                    <div class="stat-label text-danger"><i class="fas fa-tags me-1"></i>Total Discount</div>
                    <div class="stat-val text-danger" id="cardTotalDiscountTop">-Rs 0</div>
                    <div class="stat-sub text-danger opacity-75" id="cardTotalDiscountTopSub">All Deductions</div>
                </div>

                {{-- 5. Net Revenue (Realized) --}}
                <div class="stat-pill" style="background: #ecfdf5; border-color: #a7f3d0;">
                    <div class="stat-label text-success"><i class="fas fa-receipt me-1"></i>Net Revenue</div>
                    <div class="stat-val text-success" id="cardNetRevenue">Rs 0</div>
                    <div class="stat-sub text-success opacity-75" id="cardNetRevenueSub">Realized (Net - Return)</div>
                </div>

                {{-- 6. Total COGS --}}
                <div class="stat-pill" style="background: #fffbeb; border-color: #fde68a;">
                    <div class="stat-label" style="color: #d97706 !important;"><i class="fas fa-boxes-stacked me-1"></i>Total COGS</div>
                    <div class="stat-val" style="color: #b45309;" id="cardCOGS">Rs 0</div>
                    <div class="stat-sub" style="color: #d97706;">Product Cost</div>
                </div>

                {{-- 7. Bonus Amount --}}
                <div class="stat-pill" style="background: #fffbeb; border-color: #f59e0b;">
                    <div class="stat-label" style="color: #d97706 !important;"><i class="fas fa-gift me-1"></i>Bonus Amount</div>
                    <div class="stat-val" style="color: #b45309;" id="cardBonusAmount">Rs 0</div>
                    <div class="stat-sub" style="color: #d97706;" id="cardBonusAmountSub">At Base Price</div>
                </div>

                {{-- 8. Gross Profit --}}
                <div class="stat-pill" style="background: #f0fdf4; border-color: #86efac;" id="cardGrossProfitBox">
                    <div class="stat-label text-success" id="cardGrossProfitLabel"><i class="fas fa-arrow-trend-up me-1"></i>Gross Profit</div>
                    <div class="stat-val text-success" id="cardGrossProfit">Rs 0</div>
                    <div class="stat-sub text-success" id="cardGrossProfitSub">Revenue - COGS - Bonus</div>
                </div>
            </div>

            {{-- ROW 2: Bottom Line & Partner Equity Distribution Strip (Eliminates horizontal scrolling) --}}
            <div class="row g-2 pt-2 border-top align-items-stretch">
                {{-- 9. Total Expenses --}}
                <div class="col-6 col-md-3">
                    <div class="stat-pill h-100 d-flex flex-column justify-content-center" style="background: #fef2f2; border-color: #fca5a5;" id="cardExpensesBox">
                        <div class="stat-label text-danger mb-1" id="cardExpensesLabel" style="font-size: .68rem;"><i class="fas fa-arrow-trend-down me-1"></i>Expenses</div>
                        <div class="stat-val text-danger fw-bold" id="cardExpenses" style="font-size: 1.08rem;">Rs 0</div>
                        <div class="stat-sub text-danger opacity-75 mt-0.5" id="cardExpensesSub">Operating Costs</div>
                    </div>
                </div>

                {{-- 10. Net Profit / Loss --}}
                <div class="col-6 col-md-3">
                    <div class="stat-pill h-100 d-flex flex-column justify-content-center" style="background: #f0f9ff; border-color: #bae6fd;" id="cardNetProfitBox">
                        <div class="stat-label text-primary mb-1" id="cardNetProfitLabel" style="font-size: .68rem;"><i class="fas fa-chart-line me-1"></i>Net Profit / Loss</div>
                        <div class="stat-val text-primary fw-bold" id="cardNetProfit" style="font-size: 1.08rem;">Rs 0</div>
                        <div class="stat-sub text-primary opacity-75 mt-0.5" id="cardNetProfitSub">Bottom Line</div>
                    </div>
                </div>

                {{-- 11. Partner 1 Equity Share --}}
                <div class="col-6 col-md-3">
                    <div class="stat-pill h-100 d-flex flex-column justify-content-center" style="background: #faf5ff; border-color: #d8b4fe;" id="cardPartner1Box">
                        <div class="stat-label d-flex align-items-center justify-content-center gap-1 mb-1" style="color: #7c3aed; font-size: .68rem;">
                            <i class="fas fa-user-tie"></i>
                            <span class="fw-bold">Partner 1</span>
                            <span class="d-inline-flex align-items-center bg-white px-1.5 py-0.5 rounded border shadow-2xs" style="border-color: #d8b4fe !important; height: 19px;">
                                <input type="number" class="partner1-pct-input border-0 text-center fw-bold p-0" value="50" min="0" max="100" step="any" style="width: 32px; font-size: 11px; outline: none; color: #7c3aed; background: transparent;" title="Click to adjust %">
                                <span style="font-size: 10px; font-weight: 700; color: #7c3aed;">%</span>
                            </span>
                        </div>
                        <div class="stat-val fw-bold" style="color: #7c3aed; font-size: 1.08rem;" id="cardPartner1Profit">Rs 0</div>
                        <div class="stat-sub mt-0.5" style="color: #7c3aed;" id="cardPartner1ProfitSub">Equity Share</div>
                    </div>
                </div>

                {{-- 12. Partner 2 Equity Share --}}
                <div class="col-6 col-md-3">
                    <div class="stat-pill h-100 d-flex flex-column justify-content-center" style="background: #fff7ed; border-color: #fed7aa;" id="cardPartner2Box">
                        <div class="stat-label d-flex align-items-center justify-content-center gap-1 mb-1" style="color: #ea580c; font-size: .68rem;">
                            <i class="fas fa-user-tie"></i>
                            <span class="fw-bold">Partner 2</span>
                            <span class="d-inline-flex align-items-center bg-white px-1.5 py-0.5 rounded border shadow-2xs" style="border-color: #fed7aa !important; height: 19px;">
                                <input type="number" class="partner2-pct-input border-0 text-center fw-bold p-0" value="50" min="0" max="100" step="any" style="width: 32px; font-size: 11px; outline: none; color: #ea580c; background: transparent;" title="Click to adjust %">
                                <span style="font-size: 10px; font-weight: 700; color: #ea580c;">%</span>
                            </span>
                        </div>
                        <div class="stat-val fw-bold" style="color: #ea580c; font-size: 1.08rem;" id="cardPartner2Profit">Rs 0</div>
                        <div class="stat-sub mt-0.5" style="color: #ea580c;" id="cardPartner2ProfitSub">Equity Share</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- SECTION 2: DISCOUNTS & TIER SUBSIDIES BREAKDOWN --}}
    @can('profit.loss.discounts.view')
    <div class="card border-0 shadow-sm mb-2 no-print" style="border-radius: 10px; background: #ffffff;">
        <div class="card-body p-2">
            <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: .76rem; letter-spacing: -0.1px;">
                        <i class="fas fa-percent text-danger me-1.5"></i>Discounts & Tier Subsidies Breakdown
                    </span>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5" style="font-size: 10px;">
                        Total Discounts: <strong id="cardTotalDiscountHeader">Rs 0</strong>
                    </span>
                </div>
                <span class="text-muted small d-none d-md-inline" style="font-size: 11px;">
                    <i class="fas fa-filter text-primary me-1"></i>Recalculates per Zone / Customer filter
                </span>
            </div>

            <div class="row g-2">
                {{-- Total Discount --}}
                <div class="col-6 col-md">
                    <div class="overview-card" style="background: #fef2f2; border-color: #fca5a5;">
                        <div class="ov-header">
                            <span class="ov-label text-danger"><i class="fas fa-tags me-1"></i>Total Discount</span>
                        </div>
                        <div class="ov-val text-danger" id="cardTotalDiscount">Rs 0</div>
                        <div class="ov-sub text-danger opacity-75" id="cardTotalDiscountSub">All Deductions</div>
                    </div>
                </div>

                {{-- Medical Store Discount --}}
                <div class="col-6 col-md">
                    <div class="overview-card" style="background: #ecfdf5; border-color: #a7f3d0;">
                        <div class="ov-header">
                            <span class="ov-label text-success"><i class="fas fa-prescription-bottle-medical me-1"></i>Medical Store</span>
                        </div>
                        <div class="ov-val text-success" id="cardMedicalDiscount">Rs 0</div>
                        <div class="ov-sub text-success opacity-75">Pharmacy Tier Subsidies</div>
                    </div>
                </div>

                {{-- Distribution Discount --}}
                <div class="col-6 col-md">
                    <div class="overview-card" style="background: #eff6ff; border-color: #bfdbfe;">
                        <div class="ov-header">
                            <span class="ov-label text-primary"><i class="fas fa-truck-fast me-1"></i>Distribution</span>
                        </div>
                        <div class="ov-val text-primary" id="cardDistDiscount">Rs 0</div>
                        <div class="ov-sub text-primary opacity-75">Wholesale Tier Subsidies</div>
                    </div>
                </div>

                {{-- Doctor Discount --}}
                <div class="col-6 col-md">
                    <div class="overview-card" style="background: #faf5ff; border-color: #e9d5ff;">
                        <div class="ov-header">
                            <span class="ov-label" style="color: #7c3aed;"><i class="fas fa-user-doctor me-1"></i>Doctor / Clinic</span>
                        </div>
                        <div class="ov-val" style="color: #7c3aed;" id="cardDoctorDiscount">Rs 0</div>
                        <div class="ov-sub" style="color: #7c3aed; opacity: 0.75;">Practitioner Concessions</div>
                    </div>
                </div>

                {{-- Other / Extra Discount --}}
                <div class="col-6 col-md">
                    <div class="overview-card" style="background: #fffbeb; border-color: #fde68a;">
                        <div class="ov-header">
                            <span class="ov-label" style="color: #b45309;"><i class="fas fa-file-invoice-dollar me-1"></i>Extra / Other</span>
                        </div>
                        <div class="ov-val" style="color: #b45309;" id="cardOtherDiscount">Rs 0</div>
                        <div class="ov-sub" style="color: #b45309; opacity: 0.75;">Invoice & Manual Extra</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- SECTION 3: PRODUCT SALES QUANTITIES & VOLUME OVERVIEW --}}
    @can('profit.loss.quantities.view')
    <div class="card border-0 shadow-sm mb-2 no-print" style="border-radius: 10px; background: #ffffff;">
        <div class="card-body p-2">
            <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: .76rem; letter-spacing: -0.1px;">
                        <i class="fas fa-boxes-stacked text-primary me-1.5"></i>Product Sales Volume & Delivery Quantities
                    </span>
                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10px;">
                        <span id="cardSalesCount">0 Sales</span> &bull; <span id="cardProductsCount">0 Products</span>
                    </span>
                </div>
                <span class="text-muted small d-none d-md-inline" style="font-size: 11px;">
                    <i class="fas fa-cubes text-secondary me-1"></i>Carton, Loose & Net Delivered Units
                </span>
            </div>

            <div class="row g-2">
                {{-- Total Sold Units --}}
                <div class="col-6 col-md-3">
                    <div class="overview-card" style="background: #f8fafc; border-color: #cbd5e1;">
                        <div class="ov-header">
                            <span class="ov-label text-dark"><i class="fas fa-box-open text-primary me-1"></i>Products Sold</span>
                        </div>
                        <div class="ov-val text-dark" id="cardSoldQty">0 Pcs</div>
                        <div class="ov-sub text-muted" id="cardSoldPieces">0 Total Pieces Sold</div>
                    </div>
                </div>

                {{-- Returns --}}
                <div class="col-6 col-md-3">
                    <div class="overview-card" style="background: #fef2f2; border-color: #fca5a5;">
                        <div class="ov-header">
                            <span class="ov-label text-danger"><i class="fas fa-rotate-left me-1"></i>Sales Returns</span>
                        </div>
                        <div class="ov-val text-danger" id="cardReturnQty">0 Pcs</div>
                        <div class="ov-sub text-danger" id="cardReturnAmount">Rs 0 Total Value</div>
                    </div>
                </div>

                {{-- Net Delivered --}}
                <div class="col-6 col-md-3">
                    <div class="overview-card" style="background: #f0fdf4; border-color: #86efac;">
                        <div class="ov-header">
                            <span class="ov-label text-success"><i class="fas fa-truck-ramp-box me-1"></i>Net Delivered</span>
                        </div>
                        <div class="ov-val text-success" id="cardNetQty">0 Pcs</div>
                        <div class="ov-sub text-success" id="cardNetPieces">0 Net Delivered Pieces</div>
                    </div>
                </div>

                {{-- Net Revenue --}}
                <div class="col-6 col-md-3">
                    <div class="overview-card" style="background: #f0f9ff; border-color: #bae6fd;">
                        <div class="ov-header">
                            <span class="ov-label text-primary"><i class="fas fa-wallet me-1"></i>Net Revenue</span>
                        </div>
                        <div class="ov-val text-primary" id="cardSec3NetRevenue">Rs 0</div>
                        <div class="ov-sub text-primary opacity-75">Net Realized Revenue</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- ZONE-WISE PROFIT SUMMARY CARDS (UPPER CARDS) --}}
    @can('profit.loss.zones.view')
    <div class="card border-0 shadow-sm mb-2 no-print" style="border-radius: 10px; background: #ffffff;" id="zoneProfitSection">
        <div class="card-body p-2">
            <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-dark" style="font-size: .76rem; letter-spacing: -0.1px;">
                        <i class="fas fa-map-marked-alt text-primary me-1.5"></i>Zone-wise Profit
                    </span>
                    <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10px;" id="zoneCountBadge">0 Zones</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted d-none d-md-inline" style="font-size: 11px;">
                        <i class="fas fa-hand-pointer me-1 text-primary opacity-75"></i>Click to filter
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" id="btnClearZoneFilter" style="font-size: 10.5px; border-radius: 12px; display: none;">
                        <i class="fas fa-times me-1"></i>All Zones
                    </button>
                </div>
            </div>
            <div class="zone-pill-bar" id="zoneCardsContainer">
                <div class="text-center text-muted py-2 small w-100">Loading zone data…</div>
            </div>
        </div>
    </div>
    @endcan

    <div id="loader" style="display:none; text-align:center; padding: 20px;">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="small text-muted mt-2">Calculating Equity Ledger Data…</div>
    </div>

    {{-- Content Layout --}}
    <div class="row g-3 mb-3">
        
        {{-- Item Profitability Table --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6"><i class="fas fa-table me-2 text-primary"></i>Item Profitability</span>
                </div>
                <div class="card-body p-0">
                    
                    {{-- DESKTOP TABLE VIEW (d-none d-md-block) --}}
                    <div class="d-none d-md-block">
                        <div class="sale-table-wrap border-0 rounded-0">
                            <table id="profitTable" class="table table-bordered table-hover align-middle mb-0 report-table" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">CODE</th>
                                        <th>PRODUCT</th>
                                        <th class="text-center" style="width: 75px;">QTY</th>
                                        <th class="text-center" style="width: 80px;">RET</th>
                                        <th class="text-end" style="width: 110px;">REVENUE</th>
                                        <th class="text-end" style="width: 110px;">COGS</th>
                                        <th class="text-end" style="width: 120px;">PROFIT</th>
                                    </tr>
                                </thead>
                                <tbody id="profitBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- MOBILE CARDS CONTAINER (d-md-none) --}}
                    <div class="d-md-none p-2" id="mobProfitContainer">
                    </div>

                </div>
            </div>
        </div>

        {{-- Top 5 Profit Share Chart --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-light py-2 px-3">
                    <span class="fw-bold text-dark fs-6"><i class="fas fa-chart-pie me-2 text-purple" style="color: #8b5cf6;"></i>Top 5 Profit Share</span>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center p-3">
                    <div id="noChartData" class="text-muted small py-4">Fetch data to see chart</div>
                    <canvas id="profitChart" style="max-height: 250px; width:100%; display:none;"></canvas>
                </div>
            </div>
        </div>

    </div>

    {{-- Top 10 Customers --}}
    <div class="card border-0 shadow-sm mb-3 rounded-3 bg-white">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark fs-6"><i class="fas fa-trophy me-2 text-warning"></i>Top 10 Customers by Profit</span>
            <span class="badge bg-secondary text-white small" id="customerCount">0 customers</span>
        </div>
        <div class="card-body p-0">
            
            {{-- DESKTOP TABLE VIEW (d-none d-md-block) --}}
            <div class="d-none d-md-block">
                <div class="sale-table-wrap border-0 rounded-0" style="height: auto; max-height: 350px;">
                    <table class="table table-bordered table-hover align-middle mb-0 report-table" id="topCustomersTableContent">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 40px;">#</th>
                                <th>CUSTOMER NAME</th>
                                <th class="text-end" style="width: 140px;">REVENUE</th>
                                <th class="text-end" style="width: 150px;">LEDGER BALANCE</th>
                                <th class="text-end" style="width: 150px;">NET PROFIT</th>
                            </tr>
                        </thead>
                        <tbody id="topCustomersBody">
                            <tr><td colspan="5" class="text-center text-muted py-4 small">Fetch data to see top customers</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- MOBILE CARDS CONTAINER (d-md-none) --}}
            <div class="d-md-none p-2" id="mobTopCustomersContainer">
            </div>

        </div>
    </div>

</div>
@endsection

@section('js')
<script src="{{ asset('assets/vendors/chartjs/chart.umd.min.js') }}"></script>
<script>
$(document).ready(function() {
    if ($('.select2').length > 0) {
        $('.select2').select2({ width: '100%' });
    }

    // Sync Inputs between Desktop & Mobile
    $('.startDateInput').on('change', function() { $('.startDateInput').val($(this).val()); });
    $('.endDateInput').on('change', function() { $('.endDateInput').val($(this).val()); });
    $('.categorySelect').on('change', function() { $('.categorySelect').val($(this).val()); });
    $('.productSelect').on('change', function() { $('.productSelect').val($(this).val()); });
    $('.customerSelect').on('change', function() { $('.customerSelect').val($(this).val()); });

    // Zone Filter Sync & Dynamic Customer Dropdown Filter
    $('.zoneSelect').on('change', function() {
        let selectedZone = $(this).val();
        $('.zoneSelect').val(selectedZone);

        $('.customerSelect').each(function() {
            let $select = $(this);
            let currentVal = $select.val();
            $select.find('option').each(function() {
                let optZone = $(this).attr('data-zone');
                if ($(this).val() === 'all' || selectedZone === 'all' || optZone == selectedZone) {
                    $(this).show().removeAttr('disabled');
                } else {
                    $(this).hide().attr('disabled', 'disabled');
                }
            });
            // If current customer doesn't match selected zone, reset to 'all'
            let selectedOpt = $select.find('option[value="' + currentVal + '"]');
            if (selectedOpt.length && selectedOpt.is(':disabled')) {
                $select.val('all').trigger('change');
            } else {
                $select.trigger('change.select2');
            }
        });
    });

    var profitTable = $('#profitTable').DataTable({
        paging: true,
        pageLength: 15,
        searching: true,
        ordering: true,
        order: [[6, 'desc']],
        language: { search: '', searchPlaceholder: 'Search items...' },
        columns: [
            { data: 'item_code' },
            { data: 'item_name' },
            { data: 'sold_qty', className: 'text-center' },
            { data: 'returned_qty', className: 'text-center' },
            { data: 'revenue', className: 'text-end' },
            { data: 'cogs', className: 'text-end' },
            { data: 'profit', className: 'text-end' }
        ]
    });

    var myChart = null;
    var currentNetProfit = 0;
    var lastReportResponse = null;
    var currentExpenseMode = localStorage.getItem('profit_loss_expense_mode') || 'with';

    function applyToggleUI(mode) {
        if (mode === 'without') {
            $('#btnModeWithoutExpenses').addClass('active-without');
            $('#btnModeWithExpenses').removeClass('active-with');
        } else {
            $('#btnModeWithExpenses').addClass('active-with');
            $('#btnModeWithoutExpenses').removeClass('active-without');
        }
    }
    applyToggleUI(currentExpenseMode);

    $(document).on('click', '#btnModeWithExpenses', function(e) {
        e.preventDefault();
        setExpenseMode('with');
    });

    $(document).on('click', '#btnModeWithoutExpenses', function(e) {
        e.preventDefault();
        setExpenseMode('without');
    });

    function setExpenseMode(mode) {
        currentExpenseMode = mode;
        localStorage.setItem('profit_loss_expense_mode', mode);
        applyToggleUI(mode);
        if (lastReportResponse) {
            renderFinancialCards(lastReportResponse);
        }
    }

    function fmt(n) {
        let num = parseFloat(n) || 0;
        let decimals = (num % 1 !== 0) ? 2 : 0;
        return num.toLocaleString(undefined, {minimumFractionDigits: decimals, maximumFractionDigits: 2});
    }

    // Load saved partner percentages if available
    var savedP1 = localStorage.getItem('partner1_profit_pct');
    var savedP2 = localStorage.getItem('partner2_profit_pct');
    if (savedP1 !== null && savedP2 !== null) {
        $('.partner1-pct-input').val(savedP1);
        $('.partner2-pct-input').val(savedP2);
    }

    function updatePartnerShares() {
        var p1Pct = parseFloat($('.partner1-pct-input').first().val());
        if (isNaN(p1Pct)) p1Pct = 0;
        var p2Pct = parseFloat($('.partner2-pct-input').first().val());
        if (isNaN(p2Pct)) p2Pct = 0;

        var p1Share = currentNetProfit * (p1Pct / 100);
        var p2Share = currentNetProfit * (p2Pct / 100);

        var p1Color = p1Share >= 0 ? '#7c3aed' : '#dc2626';
        var p2Color = p2Share >= 0 ? '#ea580c' : '#dc2626';

        $('#cardPartner1Profit, #mobCardPartner1Profit').text('Rs ' + fmt(p1Share)).css('color', p1Color);
        $('#cardPartner2Profit, #mobCardPartner2Profit').text('Rs ' + fmt(p2Share)).css('color', p2Color);
    }

    // Dynamic Partner Percentage Handlers (Desktop & Mobile synced + Complementary calculation)
    $(document).on('input change', '.partner1-pct-input', function() {
        var rawVal = $(this).val();
        if (rawVal === '') return;
        var val = parseFloat(rawVal);
        if (isNaN(val)) val = 0;
        val = Math.max(0, Math.min(100, Math.round(val * 10) / 10));
        $('.partner1-pct-input').val(val);
        var other = Math.max(0, Math.min(100, Math.round((100 - val) * 10) / 10));
        $('.partner2-pct-input').val(other);
        localStorage.setItem('partner1_profit_pct', val);
        localStorage.setItem('partner2_profit_pct', other);
        updatePartnerShares();
    });

    $(document).on('input change', '.partner2-pct-input', function() {
        var rawVal = $(this).val();
        if (rawVal === '') return;
        var val = parseFloat(rawVal);
        if (isNaN(val)) val = 0;
        val = Math.max(0, Math.min(100, Math.round(val * 10) / 10));
        $('.partner2-pct-input').val(val);
        var other = Math.max(0, Math.min(100, Math.round((100 - val) * 10) / 10));
        $('.partner1-pct-input').val(other);
        localStorage.setItem('partner1_profit_pct', other);
        localStorage.setItem('partner2_profit_pct', val);
        updatePartnerShares();
    });

    $('.btnSearchTrigger').click(fetchReport);

    $('.btnResetTrigger').on('click', function() {
        $('.startDateInput').val('{{ date("Y-m-d\T00:00") }}');
        $('.endDateInput').val('{{ date("Y-m-d\T23:59") }}');
        $('.zoneSelect, .categorySelect, .customerSelect, .productSelect').val('all').trigger('change');
        fetchReport();
    });

    $('.btnPrintReport').on('click', () => window.print());


    function renderFinancialCards(data) {
        if (!data) return;
        let s = data.summary || {};
        let rawGrossVal   = parseFloat(s.raw_gross_revenue || 0);
        let rawDiscVal    = parseFloat(s.raw_total_discount !== undefined ? s.raw_total_discount : (s.total_discount || 0));
        let invoicedNet   = parseFloat(s.invoiced_net !== undefined ? s.invoiced_net : (rawGrossVal - rawDiscVal));
        let effGrossVal   = parseFloat(s.effective_gross || s.gross_revenue || 0);
        let returnGrossVal= parseFloat(s.return_gross || (rawGrossVal - effGrossVal) || 0);
        let retNetVal     = parseFloat(s.return_amount || 0);
        let auditNetVal   = parseFloat(s.audit_net !== undefined ? s.audit_net : (effGrossVal > 0 ? effGrossVal : Math.max(0, rawGrossVal - (returnGrossVal > 0 ? returnGrossVal : retNetVal))));
        let discVal       = parseFloat(s.total_discount || 0);
        let netRevVal     = parseFloat(s.net_revenue || 0);
        let cogsVal       = parseFloat(s.cogs || 0);
        let bonusAmtVal   = parseFloat(s.bonus_amount || 0);
        let bonusQtyVal   = parseFloat(s.bonus_qty || 0);
        let grossProf     = parseFloat(data.total_gross_profit || s.gross_profit || 0);
        let rawExpenses   = parseFloat(data.total_expenses || s.expenses || 0);

        let isWithout = (currentExpenseMode === 'without');
        let netProf = isWithout ? grossProf : (grossProf - rawExpenses);
        currentNetProfit = netProf;

        let marginVal = netRevVal > 0 ? ((netProf / netRevVal) * 100).toFixed(1) : 0;

        $('#cardRawGross').text('Rs ' + fmt(rawGrossVal));
        $('#cardRawGrossSub').text('Billed Trade Price');

        let displayReturnVal = returnGrossVal > 0 ? returnGrossVal : retNetVal;
        $('#cardSalesReturn').text((displayReturnVal > 0 ? '-Rs ' : 'Rs ') + fmt(displayReturnVal));
        $('#cardSalesReturnSub').text((returnGrossVal > 0 && retNetVal > 0 && returnGrossVal !== retNetVal) ? ('Net Refund: Rs ' + fmt(retNetVal)) : 'Returns Deducted');

        $('#cardAuditNet').text('Rs ' + fmt(auditNetVal));
        $('#cardAuditNetSub').text('Audit Net · Gross - Return');

        $('#cardTotalDiscountTop').text((rawDiscVal > 0 ? '-Rs ' : 'Rs ') + fmt(rawDiscVal));
        $('#cardTotalDiscountTopSub').text('All Deductions');

        if ($('#cardInvoicedNet').length) {
            $('#cardInvoicedNet').text('Rs ' + fmt(invoicedNet));
            $('#cardInvoicedNetSub').text('Gross - Disc (Table Net)');
        }

        $('#cardEffectiveGross').text('Rs ' + fmt(effGrossVal));
        $('#cardEffectiveGrossSub').text('Pre-Discount TP');

        $('#cardNetRevenue, #cardSec3NetRevenue').text('Rs ' + fmt(netRevVal));
        $('#cardNetRevenueSub').text('Realized (Net - Return)');

        $('#cardCOGS').text('Rs ' + fmt(cogsVal));
        $('#cardBonusAmount').text('Rs ' + fmt(bonusAmtVal));
        $('#cardBonusAmountSub').text('At Cost · ' + fmt(bonusQtyVal) + ' Pcs');

        let grossProfColor = grossProf >= 0 ? '#059669' : '#dc2626';
        let grossSign = grossProf < 0 ? '-' : '';
        $('#cardGrossProfit, #mobCardGrossProfit').text(grossSign + 'Rs ' + fmt(Math.abs(grossProf))).css('color', grossProfColor);
        $('#cardGrossProfitSub')
            .text('Revenue - COGS - Bonus')
            .attr('title', 'Net Revenue: Rs ' + fmt(netRevVal) + ' | COGS: -Rs ' + fmt(cogsVal) + ' | Bonus: -Rs ' + fmt(bonusAmtVal));

        // Expenses Stat Pill
        if (isWithout) {
            $('#cardExpensesBox').css({'background': '#f8fafc', 'border-color': '#e2e8f0'});
            $('#cardExpensesLabel').html('<i class="fas fa-arrow-trend-down me-1 text-muted"></i>Expenses <span class="badge bg-secondary text-white" style="font-size: 8px; padding: 1px 4px; vertical-align: middle;">EXCLUDED</span>').removeClass('text-danger').addClass('text-secondary');
            $('#cardExpenses, #mobCardExpenses').html('<span class="text-secondary">Rs 0</span>').css('color', '#64748b');
            $('#cardExpensesSub').html('<span class="text-muted" title="Actual Expenses: Rs ' + fmt(rawExpenses) + '">Excluded (Total: Rs ' + fmt(rawExpenses) + ')</span>').removeClass('text-danger').addClass('text-muted');
        } else {
            $('#cardExpensesBox').css({'background': '#fef2f2', 'border-color': '#fca5a5'});
            $('#cardExpensesLabel').html('<i class="fas fa-arrow-trend-down me-1"></i>Expenses').removeClass('text-secondary').addClass('text-danger');
            $('#cardExpenses, #mobCardExpenses').text('Rs ' + fmt(rawExpenses)).css('color', '#dc2626');
            $('#cardExpensesSub').text('Operating Costs').removeClass('text-muted').addClass('text-danger');
        }

        // Net Profit / Loss Stat Pill
        let netProfColor = netProf >= 0 ? '#0284c7' : '#dc2626';
        let netSign = netProf < 0 ? '-' : '';
        $('#cardNetProfit, #mobCardNetProfit').text(netSign + 'Rs ' + fmt(Math.abs(netProf))).css('color', netProfColor);

        if (isWithout) {
            $('#cardNetProfitSub').html('<span class="text-success fw-bold"><i class="fas fa-bolt me-1"></i>Before Expenses (=Gross)</span>');
            $('#cardPartner1ProfitSub').text('Equity Share (No Exp.)');
            $('#cardPartner2ProfitSub').text('Equity Share (No Exp.)');
        } else {
            $('#cardNetProfitSub').text('Bottom Line');
            $('#cardPartner1ProfitSub').text('Equity Share');
            $('#cardPartner2ProfitSub').text('Equity Share');
        }

        let marginClass = marginVal >= 0 ? 'text-success' : 'text-danger';
        $('#cardProfitMarginBadge').text(marginVal + '% Margin' + (isWithout ? ' (Gross)' : '')).removeClass('text-success text-danger').addClass(marginClass);

        updatePartnerShares();
    }

    function fetchReport() {
        $('#loader').show();

        let startDate = $('.startDateInput').val();
        let endDate   = $('.endDateInput').val();
        let zoneId    = $('.zoneSelect').val() || 'all';
        let catId     = $('.categorySelect').val();
        let custId    = $('.customerSelect').val();
        let prodId    = $('.productSelect').val();

        $.ajax({
            url: "{{ route('report.profit_loss.fetch') }}",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}",
                start_date: startDate,
                end_date: endDate,
                zone_id: zoneId,
                product_id: prodId,
                category_id: catId,
                customer_id: custId,
                without_expenses: currentExpenseMode === 'without' ? 1 : 0
            },
            success: function(response) {
                $('#loader').hide();
                lastReportResponse = response;
                renderReport(response);
            },
            error: function(xhr) {
                $('#loader').hide();
                if (xhr.status === 419) {
                    alert('Session expire ho gaya hai (CSRF Expired). Page refresh kiya ja raha hai...');
                    window.location.reload();
                    return;
                }
                if (xhr.status === 401 || xhr.status === 403) {
                    alert('Aapka session khatam ho chuka hai ya permission issue hai. Page refresh karein.');
                    window.location.reload();
                    return;
                }
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not fetch report data';
                alert(msg);
            }
        });
    }

    function renderReport(data) {
        // Table
        profitTable.clear();
        let chartLabels = [];
        let chartData = [];
        let mobHtml = '';

        data.products.forEach(function(r, idx) {
            let profitColor = parseFloat(r.profit) >= 0 ? '#059669' : '#dc2626';
            
            let itemNameHtml = `
                <div>
                    <div class="fw-bold text-dark" style="font-size:12.5px;">${r.item_name}</div>
                    ${r.unit_badge ? `<span class="badge bg-light text-secondary border px-1.5 py-0.5 mt-1" style="font-size:10px; font-weight:600;"><i class="fas fa-tag me-1 text-primary"></i>${r.unit_badge}</span>` : ''}
                </div>
            `;

            profitTable.row.add({
                item_code: '<span class="fw-semibold text-primary font-monospace small">' + r.item_code + '</span>',
                item_name: itemNameHtml,
                sold_qty: '<span class="fw-bold text-dark">' + r.sold_qty + '</span>',
                returned_qty: '<span class="text-danger fw-bold">' + (r.returned_qty || 0) + '</span>',
                revenue: 'Rs ' + fmt(r.revenue),
                cogs: 'Rs ' + fmt(r.cogs),
                profit: '<span style="color:' + profitColor + '; font-weight:700;">Rs ' + fmt(r.profit) + '</span>'
            });

            if (chartLabels.length < 5 && parseFloat(r.profit) > 0) {
                chartLabels.push(r.item_name.length > 18 ? r.item_name.substring(0, 18) + '…' : r.item_name);
                chartData.push(r.profit);
            }

            // Mobile Card
            mobHtml += `
                <div class="mob-card p-2.5 p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-light text-muted border" style="font-size: 10px;">#${idx+1}</span>
                            <span class="badge bg-light text-primary border font-monospace fw-bold" style="font-size: 11px;">${r.item_code}</span>
                            ${r.unit_badge ? `<span class="badge bg-light text-secondary border px-1" style="font-size: 9.5px;">${r.unit_badge}</span>` : ''}
                        </div>
                        <strong style="color:${profitColor}; font-size:12px;">Profit: Rs ${fmt(r.profit)}</strong>
                    </div>
                    <div class="fw-bold text-dark mb-1" style="font-size:12.5px;">${r.item_name}</div>
                    <div class="border-top pt-2 mt-1">
                        <div class="row g-1 text-center" style="font-size: 10.5px;">
                            <div class="col-3 border-end"><span class="text-muted d-block">Qty</span><strong>${r.sold_qty}</strong></div>
                            <div class="col-3 border-end"><span class="text-muted d-block">Ret</span><strong class="text-danger">${r.returned_qty||0}</strong></div>
                            <div class="col-3 border-end"><span class="text-muted d-block">Revenue</span><strong>Rs ${fmt(r.revenue)}</strong></div>
                            <div class="col-3"><span class="text-muted d-block">COGS</span><strong>Rs ${fmt(r.cogs)}</strong></div>
                        </div>
                    </div>
                </div>`;
        });
        profitTable.draw();
        $('#mobProfitContainer').html(mobHtml || '<div class="text-center text-muted py-3 small">No item profitability data</div>');

        // 1. Financial Performance Cards (Respects With/Without Expenses Toggle)
        let s = data.summary || {};
        renderFinancialCards(data);


        // 2. Customer Tier Discounts Breakdown Cards
        let totalDiscVal = parseFloat(s.total_discount || 0);
        let rawDiscVal   = parseFloat(s.raw_total_discount || totalDiscVal);
        let medDiscVal   = parseFloat(s.medical_discount || 0);
        let distDiscVal  = parseFloat(s.distribution_discount || 0);
        let docDiscVal   = parseFloat(s.doctor_discount || 0);
        let otherDiscVal = parseFloat(s.other_discount || 0);

        $('#cardTotalDiscount, #cardTotalDiscountHeader').text('Rs ' + fmt(totalDiscVal));
        if (rawDiscVal > totalDiscVal) {
            $('#cardTotalDiscountSub').html('Net of Returns <span title="Gross before returns" class="d-block" style="font-size: 8.5px; opacity: 0.85;">(Gross: Rs ' + fmt(rawDiscVal) + ')</span>');
        } else {
            $('#cardTotalDiscountSub').text('All Deductions');
        }
        $('#cardMedicalDiscount').text('Rs ' + fmt(medDiscVal));
        $('#cardDistDiscount').text('Rs ' + fmt(distDiscVal));
        $('#cardDoctorDiscount').text('Rs ' + fmt(docDiscVal));
        $('#cardOtherDiscount').text('Rs ' + fmt(otherDiscVal));

        // 3. Product Sales Quantities & Volume Overview Cards
        $('#cardSoldQty').text(s.sold_qty_display || '0 Pcs');
        $('#cardSoldPieces').text(fmt(s.total_sold_pieces || 0) + ' Total Pieces Sold');
        $('#cardReturnQty').text(s.return_qty_display || '0 Pcs');
        $('#cardReturnAmount').text('Rs ' + fmt(s.return_amount || 0) + ' Total Value');
        $('#cardNetQty').text(s.net_qty_display || '0 Pcs');
        $('#cardNetPieces').text(fmt(s.net_sold_pieces || 0) + ' Net Delivered Pieces');
        $('#cardSalesCount').text((s.sales_count || 0) + ' Sales');
        $('#cardProductsCount').text((s.products_count || data.products.length || 0) + ' Products');

        updatePartnerShares();

        // Chart
        updateChart(chartLabels, chartData);

        // Top 10 Customers
        renderTopCustomers(data.top_customers || []);

        // Zone-wise Profit Summary Cards
        renderZoneCards(data.zone_stats || []);
    }

    function renderZoneCards(zones) {
        let currentSelectedZone = $('.zoneSelect').first().val() || 'all';
        $('#zoneCountBadge').text(zones.length + ' ' + (zones.length === 1 ? 'Zone' : 'Zones'));

        if (currentSelectedZone !== 'all') {
            $('#btnClearZoneFilter').show();
        } else {
            $('#btnClearZoneFilter').hide();
        }

        if (!zones || zones.length === 0) {
            $('#zoneCardsContainer').html('<div class="text-center text-muted py-2 small w-100">No zone records found</div>');
            return;
        }

        // Detect duplicate zone names to disambiguate clearly
        let nameCounts = {};
        zones.forEach(function(z) {
            nameCounts[z.name] = (nameCounts[z.name] || 0) + 1;
        });

        // Vibrant pastel accent colors for map icons
        const accentColors = ['#2563eb', '#059669', '#7c3aed', '#ea580c', '#db2777', '#0891b2', '#4f46e5'];

        let html = '';
        zones.forEach(function(z, idx) {
            let profitVal = parseFloat(z.profit || 0);
            let profitColor = profitVal > 0 ? '#059669' : (profitVal < 0 ? '#dc2626' : '#475569');
            let profitSign = profitVal < 0 ? '-' : '';
            let isActive = (currentSelectedZone == z.id && currentSelectedZone !== 'all');
            let cardClass = isActive ? 'zone-pill active-zone' : 'zone-pill';
            let accent = accentColors[idx % accentColors.length];

            let displayName = (nameCounts[z.name] > 1 && z.id !== 'unassigned') ? `${z.name} (#${z.id})` : z.name;
            let isUnassigned = (z.id === 'unassigned');
            let iconClass = isUnassigned ? 'fa-globe' : 'fa-location-dot';

            html += `
                <div class="${cardClass}" data-zone-id="${z.id}" title="Click to filter by ${displayName}">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-1.5" style="min-width: 0;">
                            <i class="fas ${iconClass}" style="font-size: 11px; color: ${accent};"></i>
                            <span class="zone-name" title="${displayName}">${displayName}</span>
                        </div>
                        ${isActive 
                            ? '<span class="badge bg-primary text-white" style="font-size: 9.5px; padding: 2px 6px; font-weight: 700;"><i class="fas fa-check me-0.5"></i>Filtered</span>'
                            : `<span class="zone-badge" title="${z.sales_count} sales"><i class="fas fa-receipt me-1 opacity-75"></i>${z.sales_count}</span>`
                        }
                    </div>
                    <div class="d-flex align-items-end justify-content-between pt-1 border-top" style="border-color: #f1f5f9 !important;">
                        <div>
                            <div class="zone-profit-label">Net Profit</div>
                            <div class="zone-profit-val" style="color: ${profitColor};">
                                ${profitSign}Rs ${fmt(Math.abs(profitVal))}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="zone-profit-label">Revenue</div>
                            <div class="zone-rev-val">Rs ${fmt(z.revenue)}</div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#zoneCardsContainer').html(html);
    }

    // Click handler for Zone Pills to filter
    $(document).on('click', '.zone-pill', function() {
        let zoneId = $(this).attr('data-zone-id');
        let currentVal = $('.zoneSelect').first().val();

        if (currentVal == zoneId) {
            $('.zoneSelect').val('all').trigger('change');
        } else {
            $('.zoneSelect').val(zoneId).trigger('change');
        }
        fetchReport();
    });

    $('#btnClearZoneFilter').on('click', function(e) {
        e.preventDefault();
        $('.zoneSelect').val('all').trigger('change');
        fetchReport();
    });

    function renderTopCustomers(customers) {
        var html = '';
        var mobCustHtml = '';
        $('#customerCount').text(customers.length + ' customers');

        if (customers.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted py-4 small">No customer data for this period</td></tr>';
            $('#topCustomersBody').html(html);
            $('#mobTopCustomersContainer').html('<div class="text-center text-muted py-3 small">No customer data</div>');
            return;
        }

        customers.forEach(function(c, idx) {
            var profitColor = c.profit >= 0 ? '#059669' : '#dc2626';
            var profitSign = c.profit < 0 ? '-' : '';

            html += '<tr>' +
                '<td class="text-center fw-bold text-muted" style="font-size:12px;">' + (idx + 1) + '</td>' +
                '<td>' +
                    '<div class="fw-bold text-dark" style="font-size:13px;">' + c.name + '</div>' +
                    '<div class="text-muted small" style="font-size:10px;">ID: CUST-' + c.id.toString().padStart(4, '0') + '</div>' +
                '</td>' +
                '<td class="text-end fw-semibold" style="font-size:12px;">Rs ' + fmt(c.revenue) + '</td>' +
                '<td class="text-end text-danger fw-semibold" style="font-size:12px;">Rs ' + fmt(c.balance) + '</td>' +
                '<td class="text-end">' +
                    '<span class="badge p-1.5 px-2" style="background: ' + (c.profit >= 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)') + '; color: ' + profitColor + '; font-size:12px; font-weight:700;">' + 
                        profitSign + 'Rs ' + fmt(Math.abs(c.profit)) + 
                    '</span>' +
                '</td>' +
            '</tr>';

            mobCustHtml += `
                <div class="mob-card p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-light text-muted border" style="font-size:10px;">#${idx+1}</span>
                            <strong class="text-dark" style="font-size:12.5px;">${c.name}</strong>
                        </div>
                        <span class="badge p-1 px-2" style="background:${c.profit >= 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color:${profitColor}; font-size:11px; font-weight:700;">
                            ${profitSign}Rs ${fmt(Math.abs(c.profit))}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size:11px;">
                        <span>Revenue: Rs ${fmt(c.revenue)}</span>
                        <span class="text-danger">Balance: Rs ${fmt(c.balance)}</span>
                    </div>
                </div>`;
        });

        $('#topCustomersBody').html(html);
        $('#mobTopCustomersContainer').html(mobCustHtml);
    }

    function updateChart(labels, data) {
        if (myChart) myChart.destroy();

        if (labels.length === 0) {
            $('#profitChart').hide();
            $('#noChartData').show().text('No profit data available');
            return;
        }

        $('#noChartData').hide();
        $('#profitChart').show();

        var ctx = document.getElementById('profitChart');
        myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ['#059669', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 }, padding: 12 }
                    }
                }
            }
        });
    }

    // Auto load report
    fetchReport();
});
</script>
@endsection
