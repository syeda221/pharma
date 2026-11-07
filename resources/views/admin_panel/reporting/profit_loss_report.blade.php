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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-pill .stat-val {
        font-size: .92rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
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
        min-width: 0;
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
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .overview-card .ov-val {
        font-size: .95rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
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
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2px;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
    }
    .btn-expense-mode {
        border: 1px solid transparent;
        outline: none;
        background: transparent;
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.18s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        user-select: none;
    }
    .btn-expense-mode:hover {
        color: #1e293b;
    }
    .btn-expense-mode.active-with {
        background: #ffe4e6;
        color: #e11d48 !important;
        border-color: #fecdd3;
        box-shadow: 0 1px 2px rgba(225, 29, 72, 0.08);
    }
    .btn-expense-mode.active-without {
        background: #dcfce7;
        color: #059669 !important;
        border-color: #bbf7d0;
        box-shadow: 0 1px 2px rgba(5, 150, 105, 0.08);
    }

    /* Executive & Modern Equity Ledger Styling */
    .equity-main-card {
        border-radius: 14px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 12px;
    }

    /* Row 1: Waterfall KPI Mini Cards Grid */
    .waterfall-kpi-grid {
        display: grid;
        grid-template-columns: repeat(9, minmax(0, 1fr));
        gap: 6px;
        margin-bottom: 12px;
    }
    @media (max-width: 1400px) {
        .waterfall-kpi-grid {
            grid-template-columns: repeat(9, minmax(0, 1fr));
            gap: 5px;
        }
    }
    @media (max-width: 1199px) {
        .waterfall-kpi-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 6px;
        }
    }
    @media (max-width: 768px) {
        .waterfall-kpi-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .waterfall-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .kpi-mini-card {
        border-radius: 11px;
        border: 1px solid;
        padding: 7px 8px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-width: 0;
        min-height: 64px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .kpi-mini-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .kpi-mini-label {
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: 0.2px;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
        display: flex;
        align-items: center;
        gap: 3px;
    }
    .kpi-mini-val {
        font-size: clamp(12.5px, 0.95vw, 14px);
        font-weight: 800;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
    }
    .kpi-mini-sub {
        font-size: 9px;
        font-weight: 600;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Row 2: Executive Summary Cards Grid */
    .executive-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    @media (max-width: 1199px) {
        .executive-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .executive-cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .executive-card {
        position: relative;
        overflow: hidden;
        border-radius: 16px !important;
        border: 1px solid;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 112px;
        min-width: 0;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    }
    .executive-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .executive-watermark {
        position: absolute;
        right: 12px;
        bottom: 6px;
        font-size: 60px;
        opacity: 0.11;
        line-height: 1;
        pointer-events: none;
        z-index: 1;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .executive-card:hover .executive-watermark {
        opacity: 0.18;
        transform: scale(1.05);
    }
    .executive-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        margin-bottom: 6px;
    }
    .executive-icon-bubble {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .executive-action-btn {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        flex-shrink: 0;
    }
    .executive-card-body {
        position: relative;
        z-index: 2;
    }
    .executive-card-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .executive-card-val {
        font-size: clamp(18px, 1.4vw, 22px);
        font-weight: 800;
        line-height: 1.25;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
    }
    .executive-card-sub {
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .executive-card-expenses {
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
        border-color: #fecdd3;
    }
    .executive-card-netprofit {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-color: #bae6fd;
    }
    .executive-card-partner1 {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border-color: #e9d5ff;
    }
    .executive-card-partner2 {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-color: #fed7aa;
    }

    /* Row 3: Discounts Breakdown Grid */
    .discounts-cards-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }
    @media (max-width: 1199px) {
        .discounts-cards-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 768px) {
        .discounts-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 480px) {
        .discounts-cards-grid {
            grid-template-columns: 1fr;
        }
    }

    .discount-tier-card {
        position: relative;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid;
        padding: 12px 14px;
        min-height: 96px;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .discount-tier-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.05);
    }
    .discount-watermark {
        position: absolute;
        right: 8px;
        bottom: 4px;
        font-size: 50px;
        opacity: 0.12;
        line-height: 1;
        pointer-events: none;
        z-index: 1;
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .discount-tier-card:hover .discount-watermark {
        opacity: 0.20;
        transform: scale(1.05);
    }
    .discount-tier-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        margin-bottom: 4px;
    }
    .discount-tier-label {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .discount-tier-val {
        font-size: clamp(16px, 1.25vw, 19px);
        font-weight: 800;
        line-height: 1.25;
        position: relative;
        z-index: 2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-variant-numeric: tabular-nums;
    }
    .discount-tier-sub {
        font-size: 10px;
        font-weight: 600;
        position: relative;
        z-index: 2;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .discount-tier-total {
        background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
        border-color: #fecdd3;
    }
    .discount-tier-medical {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-color: #bbf7d0;
    }
    .discount-tier-dist {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #bfdbfe;
    }
    .discount-tier-doctor {
        background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
        border-color: #e9d5ff;
    }
    .discount-tier-other {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fde68a;
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
    <div class="card border-0 shadow-sm mb-2 no-print equity-main-card">
        <div class="card-body p-2.5 p-md-3">
            <div class="d-flex align-items-center justify-content-between mb-2 px-1 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark" style="font-size: .82rem; letter-spacing: -0.1px;">
                        <i class="fas fa-chart-line text-primary me-1.5"></i>Financial Performance
                    </span>
                    <span class="badge border px-2.5 py-1" style="background: #ecfdf5; color: #059669; border-color: #a7f3d0 !important; border-radius: 20px; font-size: 11px; font-weight: 700;">
                        Margin: <strong id="cardProfitMarginBadge">0.0%</strong>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-lg-inline fw-semibold" style="font-size: 11px;">
                        <i class="fas fa-trophy text-warning me-1"></i>Revenue, Cost &amp; Profit
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
            <div class="waterfall-kpi-grid">
                {{-- 1. Gross Sales (TP) --}}
                <div class="kpi-mini-card" style="background: #eff6ff; border-color: #bfdbfe;">
                    <div class="kpi-mini-label" style="color: #2563eb;"><i class="fas fa-chart-column"></i>Gross Sales (TP)</div>
                    <div class="kpi-mini-val text-dark" id="cardRawGross">Rs 0</div>
                    <div class="kpi-mini-sub text-muted" id="cardRawGrossSub">Billed Trade Price</div>
                </div>

                {{-- 2. Sales Return --}}
                <div class="kpi-mini-card" style="background: #fff1f2; border-color: #fecdd3;">
                    <div class="kpi-mini-label" style="color: #e11d48;"><i class="fas fa-rotate-left"></i>Sales Return</div>
                    <div class="kpi-mini-val" style="color: #e11d48;" id="cardSalesReturn">-Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #f43f5e;" id="cardSalesReturnSub">Returns Deducted</div>
                </div>

                {{-- 3. Sale After Return (Audit Net) --}}
                <div class="kpi-mini-card" style="background: #f0f9ff; border-color: #bae6fd;">
                    <div class="kpi-mini-label" style="color: #0284c7;"><i class="fas fa-arrow-rotate-left"></i>Sale After Return</div>
                    <div class="kpi-mini-val" style="color: #0369a1;" id="cardAuditNet">Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #0284c7;" id="cardAuditNetSub">Audit Ref · Gross - Return</div>
                </div>

                {{-- 4. Total Discount --}}
                <div class="kpi-mini-card" style="background: #fff1f2; border-color: #fecdd3;">
                    <div class="kpi-mini-label" style="color: #e11d48;"><i class="fas fa-percent"></i>Total Discount</div>
                    <div class="kpi-mini-val" style="color: #e11d48;" id="cardTotalDiscountTop">-Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #f43f5e;" id="cardTotalDiscountTopSub">All Deductions</div>
                </div>

                {{-- 5. Net Revenue (Realized) --}}
                <div class="kpi-mini-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="kpi-mini-label" style="color: #059669;"><i class="fas fa-chart-line"></i>Net Revenue</div>
                    <div class="kpi-mini-val" style="color: #059669;" id="cardNetRevenue">Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #059669;" id="cardNetRevenueSub">Realized (Net. Return)</div>
                </div>

                {{-- 6. Total COGS --}}
                <div class="kpi-mini-card" style="background: #fffbeb; border-color: #fde68a;">
                    <div class="kpi-mini-label" style="color: #d97706;"><i class="fas fa-boxes-stacked"></i>Total COGS</div>
                    <div class="kpi-mini-val" style="color: #b45309;" id="cardCOGS">Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #d97706;">Product Cost</div>
                </div>

                {{-- 7. Bonus Amount --}}
                <div class="kpi-mini-card" style="background: #fff7ed; border-color: #fed7aa;">
                    <div class="kpi-mini-label" style="color: #ea580c;"><i class="fas fa-gift"></i>Bonus Amount</div>
                    <div class="kpi-mini-val" style="color: #c2410c;" id="cardBonusAmount">Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #ea580c;" id="cardBonusAmountSub">At Base Price</div>
                </div>

                {{-- 8. WOS (Worth of Samples) --}}
                <div class="kpi-mini-card" style="background: #faf5ff; border-color: #e9d5ff;" id="cardWosBox">
                    <div class="kpi-mini-label" style="color: #7c3aed;"><i class="fas fa-arrow-trend-up"></i>WOS (Samples)</div>
                    <div class="kpi-mini-val" style="color: #6d28d9;" id="cardWosAmount">Rs 0</div>
                    <div class="kpi-mini-sub" style="color: #7c3aed;" id="cardWosAmountSub">0 Pcs (At Cost)</div>
                </div>

                {{-- 9. Gross Profit --}}
                <div class="kpi-mini-card" style="background: #f0fdf4; border-color: #86efac;" id="cardGrossProfitBox">
                    <div class="kpi-mini-label text-success" id="cardGrossProfitLabel"><i class="fas fa-arrow-trend-up"></i>Gross Profit</div>
                    <div class="kpi-mini-val text-success" id="cardGrossProfit">Rs 0</div>
                    <div class="kpi-mini-sub text-success" id="cardGrossProfitSub">Revenue - COGS - Bonus</div>
                </div>
            </div>

            {{-- ROW 2: 4 Executive Summary Cards --}}
            <div class="executive-cards-grid pt-1 border-top" style="border-color: #f1f5f9 !important;">
                {{-- 9. Total Expenses --}}
                <div class="executive-card executive-card-expenses" id="cardExpensesBox">
                    <i class="fas fa-sack-dollar executive-watermark" style="color: #e11d48;"></i>
                    <div class="executive-card-top">
                        <div class="executive-icon-bubble" style="background: #fee2e2; color: #dc2626;">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                        <div class="executive-action-btn" style="color: #f43f5e;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="executive-card-body">
                        <div class="executive-card-label" id="cardExpensesLabel" style="color: #e11d48;">EXPENSES</div>
                        <div class="executive-card-val" id="cardExpenses" style="color: #dc2626;">Rs 0</div>
                        <div class="executive-card-sub" id="cardExpensesSub" style="color: #f43f5e;">Operating Costs</div>
                    </div>
                </div>

                {{-- 10. Net Profit / Loss --}}
                <div class="executive-card executive-card-netprofit" id="cardNetProfitBox">
                    <i class="fas fa-chart-column executive-watermark" style="color: #0284c7;"></i>
                    <div class="executive-card-top">
                        <div class="executive-icon-bubble" style="background: #dbeafe; color: #0284c7;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="executive-action-btn" style="color: #0284c7;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="executive-card-body">
                        <div class="executive-card-label" id="cardNetProfitLabel" style="color: #0284c7;">NET PROFIT / LOSS</div>
                        <div class="executive-card-val" id="cardNetProfit" style="color: #0284c7;">Rs 0</div>
                        <div class="executive-card-sub" id="cardNetProfitSub" style="color: #0284c7;">Bottom Line</div>
                    </div>
                </div>

                {{-- 11. Partner 1 Equity Share --}}
                <div class="executive-card executive-card-partner1" id="cardPartner1Box">
                    <i class="fas fa-users executive-watermark" style="color: #7c3aed;"></i>
                    <div class="executive-card-top">
                        <div class="d-flex align-items-center gap-2">
                            <div class="executive-icon-bubble" style="background: #ede9fe; color: #7c3aed;">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="executive-card-label" style="color: #7c3aed;">PARTNER</span>
                            <span class="d-inline-flex align-items-center bg-white px-2 py-0.5 rounded-pill border shadow-2xs" style="border-color: #d8b4fe !important; height: 22px;">
                                <input type="number" class="partner1-pct-input border-0 text-center fw-bold p-0" value="50" min="0" max="100" step="any" style="width: 26px; font-size: 11px; outline: none; color: #7c3aed; background: transparent;" title="Click to adjust %">
                                <span style="font-size: 10px; font-weight: 700; color: #7c3aed;">%</span>
                            </span>
                        </div>
                        <div class="executive-action-btn" style="color: #7c3aed;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="executive-card-body">
                        <div class="executive-card-val" id="cardPartner1Profit" style="color: #7c3aed;">Rs 0</div>
                        <div class="executive-card-sub" id="cardPartner1ProfitSub" style="color: #7c3aed;">Equity Share</div>
                    </div>
                </div>

                {{-- 12. Partner 2 Equity Share --}}
                <div class="executive-card executive-card-partner2" id="cardPartner2Box">
                    <i class="fas fa-handshake executive-watermark" style="color: #ea580c;"></i>
                    <div class="executive-card-top">
                        <div class="d-flex align-items-center gap-2">
                            <div class="executive-icon-bubble" style="background: #ffedd5; color: #ea580c;">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <span class="executive-card-label" style="color: #ea580c;">PARTNER</span>
                            <span class="d-inline-flex align-items-center bg-white px-2 py-0.5 rounded-pill border shadow-2xs" style="border-color: #fed7aa !important; height: 22px;">
                                <input type="number" class="partner2-pct-input border-0 text-center fw-bold p-0" value="50" min="0" max="100" step="any" style="width: 26px; font-size: 11px; outline: none; color: #ea580c; background: transparent;" title="Click to adjust %">
                                <span style="font-size: 10px; font-weight: 700; color: #ea580c;">%</span>
                            </span>
                        </div>
                        <div class="executive-action-btn" style="color: #ea580c;">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    <div class="executive-card-body">
                        <div class="executive-card-val" id="cardPartner2Profit" style="color: #ea580c;">Rs 0</div>
                        <div class="executive-card-sub" id="cardPartner2ProfitSub" style="color: #ea580c;">Equity Share</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- SECTION 2: DISCOUNTS & TIER SUBSIDIES BREAKDOWN --}}
    @can('profit.loss.discounts.view')
    <div class="card border-0 shadow-sm mb-2 no-print equity-main-card">
        <div class="card-body p-2.5 p-md-3">
            <div class="d-flex align-items-center justify-content-between mb-2 px-1 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark" style="font-size: .82rem; letter-spacing: -0.1px;">
                        <i class="fas fa-percent text-danger me-1.5"></i>Discounts &amp; Tier Subsidies Breakdown
                    </span>
                    <span class="badge border px-2.5 py-1" style="background: #ffe4e6; color: #e11d48; border-color: #fecdd3 !important; border-radius: 20px; font-size: 11px; font-weight: 700;">
                        Total Discounts: <strong id="cardTotalDiscountHeader">Rs 0</strong>
                    </span>
                </div>
                <span class="text-primary small d-none d-md-inline fw-semibold" style="font-size: 11px;">
                    <i class="fas fa-filter text-primary me-1"></i>Recalculates
                </span>
            </div>

            <div class="discounts-cards-grid">
                {{-- 1. Total Discount --}}
                <div class="discount-tier-card discount-tier-total">
                    <i class="fas fa-tags discount-watermark" style="color: #e11d48;"></i>
                    <div class="discount-tier-header">
                        <span class="discount-tier-label" style="color: #e11d48;"><i class="fas fa-tags"></i> TOTAL DISCOUNT</span>
                        <i class="fas fa-chevron-right" style="color: #f43f5e; font-size: 11px; opacity: 0.7;"></i>
                    </div>
                    <div class="discount-tier-val" style="color: #e11d48;" id="cardTotalDiscount">Rs 0</div>
                    <div class="discount-tier-sub" style="color: #e11d48;" id="cardTotalDiscountSub">All Deductions</div>
                </div>

                {{-- 2. Medical Store Discount --}}
                <div class="discount-tier-card discount-tier-medical">
                    <i class="fas fa-capsules discount-watermark" style="color: #059669;"></i>
                    <div class="discount-tier-header">
                        <span class="discount-tier-label" style="color: #059669;"><i class="fas fa-capsules"></i> MEDICAL STORE</span>
                    </div>
                    <div class="discount-tier-val" style="color: #059669;" id="cardMedicalDiscount">Rs 0</div>
                    <div class="discount-tier-sub" style="color: #059669;">Pharmacy Tier Subsidies</div>
                </div>

                {{-- 3. Distribution Discount --}}
                <div class="discount-tier-card discount-tier-dist">
                    <i class="fas fa-truck-fast discount-watermark" style="color: #2563eb;"></i>
                    <div class="discount-tier-header">
                        <span class="discount-tier-label" style="color: #2563eb;"><i class="fas fa-truck-fast"></i> DISTRIBUTION</span>
                    </div>
                    <div class="discount-tier-val" style="color: #2563eb;" id="cardDistDiscount">Rs 0</div>
                    <div class="discount-tier-sub" style="color: #2563eb;">Wholesale Tier Subsidies</div>
                </div>

                {{-- 4. Doctor Discount --}}
                <div class="discount-tier-card discount-tier-doctor">
                    <i class="fas fa-user-doctor discount-watermark" style="color: #7c3aed;"></i>
                    <div class="discount-tier-header">
                        <span class="discount-tier-label" style="color: #7c3aed;"><i class="fas fa-user-doctor"></i> DOCTOR / CLINIC</span>
                    </div>
                    <div class="discount-tier-val" style="color: #7c3aed;" id="cardDoctorDiscount">Rs 0</div>
                    <div class="discount-tier-sub" style="color: #7c3aed;">Practitioner Concessions</div>
                </div>

                {{-- 5. Other / Extra Discount --}}
                <div class="discount-tier-card discount-tier-other">
                    <i class="fas fa-gift discount-watermark" style="color: #b45309;"></i>
                    <div class="discount-tier-header">
                        <span class="discount-tier-label" style="color: #b45309;"><i class="fas fa-gift"></i> EXTRA / OTHER</span>
                    </div>
                    <div class="discount-tier-val" style="color: #b45309;" id="cardOtherDiscount">Rs 0</div>
                    <div class="discount-tier-sub" style="color: #b45309;">Invoice &amp; Manual</div>
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
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small d-none d-md-inline" style="font-size: 11px;">
                        <i class="fas fa-cubes text-secondary me-1"></i>Carton, Loose & Net Delivered Units
                    </span>
                    <a href="{{ route('report.product_movement') }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 11px; border-radius: 6px;">
                        <i class="fa-solid fa-boxes-packing me-1"></i> Detailed Product Movement
                    </a>
                </div>
            </div>

            <div class="row g-2">
                {{-- Total Sold Units --}}
                <div class="col-6 col-md-3">
                    <div class="overview-card" style="background: #f8fafc; border-color: #cbd5e1;">
                        <div class="ov-header">
                            <span class="ov-label text-dark"><i class="fas fa-box-open text-primary me-1"></i>Products Sold / Bonus</span>
                        </div>
                        <div class="ov-val text-dark" id="cardSoldQty">0 Pcs / 0 Pcs</div>
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

        let wosAmtVal     = parseFloat(s.wos_amount || 0);
        let wosQtyVal     = parseFloat(s.total_sample_pieces || 0);

        $('#cardCOGS').text('Rs ' + fmt(cogsVal));
        $('#cardBonusAmount').text('Rs ' + fmt(bonusAmtVal));
        $('#cardBonusAmountSub').text('At Cost · ' + fmt(bonusQtyVal) + ' Pcs');

        $('#cardWosAmount').text('Rs ' + fmt(wosAmtVal));
        $('#cardWosAmountSub').text(fmt(wosQtyVal) + ' Pcs (At Cost)');

        let grossProfColor = grossProf >= 0 ? '#059669' : '#dc2626';
        let grossSign = grossProf < 0 ? '-' : '';
        $('#cardGrossProfit, #mobCardGrossProfit').text(grossSign + 'Rs ' + fmt(Math.abs(grossProf))).css('color', grossProfColor);
        $('#cardGrossProfitSub')
            .text('Revenue - COGS - Bonus - WOS')
            .attr('title', 'Net Revenue: Rs ' + fmt(netRevVal) + ' | COGS: -Rs ' + fmt(cogsVal) + ' | Bonus: -Rs ' + fmt(bonusAmtVal) + ' | WOS: -Rs ' + fmt(wosAmtVal));

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

        $('#cardTotalDiscount').text('Rs ' + fmt(rawDiscVal));
        $('#cardTotalDiscountHeader').text('Rs ' + fmt(totalDiscVal));
        if (rawDiscVal > totalDiscVal) {
            $('#cardTotalDiscountSub').html('All Deductions <span title="Net after returns" class="d-block" style="font-size: 8.5px; opacity: 0.85;">(Net of Returns: Rs ' + fmt(totalDiscVal) + ')</span>');
        } else {
            $('#cardTotalDiscountSub').text('All Deductions');
        }
        $('#cardMedicalDiscount').text('Rs ' + fmt(medDiscVal));
        $('#cardDistDiscount').text('Rs ' + fmt(distDiscVal));
        $('#cardDoctorDiscount').text('Rs ' + fmt(docDiscVal));
        $('#cardOtherDiscount').text('Rs ' + fmt(otherDiscVal));

        // 3. Product Sales Quantities & Volume Overview Cards
        let bonusQtyPieces = parseFloat(s.bonus_qty || 0);
        let soldDisplay = s.sold_qty_display || '0 Pcs';
        $('#cardSoldQty').html(soldDisplay + ' / <span style="color: #b45309;">' + fmt(bonusQtyPieces) + ' Pcs</span>');
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
