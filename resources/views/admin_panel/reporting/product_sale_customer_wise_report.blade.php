@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Exact Sale Report Pattern Styling for Web View */
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
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        overflow-x: auto;
        white-space: nowrap;
    }
    .stat-pill {
        flex: 1 1 0px;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        text-align: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .stat-pill .stat-label {
        font-size: .60rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 1px;
    }
    .stat-pill .stat-val {
        font-size: .88rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .sale-table-wrap {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        overflow-x: auto;
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
    .select2-container--default .select2-selection--multiple {
        border-color: #cbd5e1;
        min-height: 32px;
        font-size: .78rem;
        border-radius: 6px;
        padding: 1px 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        font-size: 11px;
        padding: 0 5px;
        margin-top: 2px;
    }
    
    /* Mobile-Specific Cards Styling (Matching Reference Screenshot) */
    .mob-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .mob-metric-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 12px 6px;
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

    @media print {
        body { background: #ffffff !important; font-size: 11px; }
        .no-print, header, .sidebar, .navbar, footer { display: none !important; }
        .sale-report-container { padding: 0 !important; background: #fff !important; }
        .card { border: 1px solid #dee2e6 !important; box-shadow: none !important; margin-bottom: 10px !important; }
    }
</style>

<div class="sale-report-container">

    {{-- DESKTOP WEB VIEW FILTER CARD (d-none d-md-block) --}}
    <div class="card border-0 shadow-sm mb-2 no-print d-none d-md-block" style="border-radius: 10px;">
        <div class="card-body py-2 px-3">
            <form id="reportFilterFormDesktop">
                
                {{-- Top Section: Left Title, Mid Dates, Last Buttons --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-2 pb-2 border-bottom">
                    
                    {{-- 1. LEFT: Title Badge --}}
                    <div class="d-flex align-items-center me-3">
                        <span class="fw-bold text-dark fs-6 text-nowrap" style="letter-spacing: -0.2px;">
                            <i class="fas fa-users text-primary me-2"></i>Product Wise Customer Sale Report
                        </span>
                    </div>

                    {{-- 2. MID: Date Range Inputs & Return Mode Toggle --}}
                    <div class="d-flex align-items-center gap-2 me-auto flex-wrap">
                        <label for="filterFromDateDesk" class="sale-filter-label mb-0 ms-1 me-1">From:</label>
                        <input type="date" name="from_date" id="filterFromDateDesk" class="form-control form-control-sm fw-bold" value="{{ date('Y-m-01') }}" style="height: 32px; width: 130px; font-size: .78rem; border-radius: 6px;">
                        
                        <label for="filterToDateDesk" class="sale-filter-label mb-0 ms-2 me-1">To:</label>
                        <input type="date" name="to_date" id="filterToDateDesk" class="form-control form-control-sm fw-bold" value="{{ date('Y-m-d') }}" style="height: 32px; width: 130px; font-size: .78rem; border-radius: 6px;">

                        <label for="filterReturnModeDesk" class="sale-filter-label mb-0 ms-2 me-1 text-primary"><i class="fas fa-sync-alt me-1"></i>Returns:</label>
                        <select name="return_mode" id="filterReturnModeDesk" class="form-select form-select-sm fw-bold returnModeSelect" style="height: 32px; width: 175px; font-size: .78rem; border-radius: 6px; border-color: #93c5fd; background-color: #eff6ff;">
                            <option value="sale_date" selected>By Sale Date (Audit)</option>
                            <option value="transaction_date">By Return Date (Accounts)</option>
                        </select>
                    </div>

                    {{-- 3. LAST: Action Buttons with X-Axis Gap --}}
                    <div class="d-flex align-items-center ms-auto" style="gap: 10px !important;">
                        <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold d-inline-flex align-items-center btnFilterTrigger" style="height: 32px; border-radius: 6px; font-size: .78rem; margin-right: 8px !important;">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <button type="button" class="btn btn-light border btn-sm px-3 fw-bold text-secondary d-inline-flex align-items-center btnResetTrigger" style="height: 32px; border-radius: 6px; font-size: .78rem; margin-right: 8px !important;">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm px-3 fw-bold d-inline-flex align-items-center btnExportExcel" style="height: 32px; border-radius: 6px; font-size: .78rem; margin-right: 8px !important;">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold d-inline-flex align-items-center btnPrintReport" style="height: 32px; border-radius: 6px; font-size: .78rem;">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>

                {{-- Bottom Section: Zone, Customer, Product, Category, Brand Dropdowns --}}
                <div class="row g-2">
                    <div class="col-md-2">
                        <label for="filterZoneDesk" class="sale-filter-label mb-1">Zone (Multi):</label>
                        <select name="zone_id[]" id="filterZoneDesk" class="form-select form-select-sm select2-multi filterZoneSelect" multiple data-placeholder="All Zones">
                            @if(isset($zones))
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->zone }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterCustomerDesk" class="sale-filter-label mb-1">Customer (Multi):</label>
                        <select name="customer_id[]" id="filterCustomerDesk" class="form-select form-select-sm select2-multi filterCustomerSelect" multiple data-placeholder="All Customers">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_id ?? 'CUST' }} - {{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filterProductDesk" class="sale-filter-label mb-1">Product (Multi):</label>
                        <select name="product_id[]" id="filterProductDesk" class="form-select form-select-sm select2-multi filterProductSelect" multiple data-placeholder="All Products">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->item_code }} - {{ $p->item_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterCategoryDesk" class="sale-filter-label mb-1">Category (Multi):</label>
                        <select name="category_id[]" id="filterCategoryDesk" class="form-select form-select-sm select2-multi filterCategorySelect" multiple data-placeholder="All Categories">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filterBrandDesk" class="sale-filter-label mb-1">Brand (Multi):</label>
                        <select name="brand_id[]" id="filterBrandDesk" class="form-select form-select-sm select2-multi filterBrandSelect" multiple data-placeholder="All Brands">
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- MOBILE VIEW FILTER CARD (d-md-none With Top Margin to Prevent Navbar Overlap) --}}
    <div class="card border-0 shadow-sm mb-3 no-print d-md-none mt-2" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form id="reportFilterFormMobile">
                <div class="row g-2">
                    {{-- 0. Zone --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Zone</label>
                        <select name="zone_id[]" class="form-select form-select-sm select2-multi filterZoneSelect" multiple data-placeholder="-- All Zones --">
                            @if(isset($zones))
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->zone }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- 1. Customer --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Customer</label>
                        <select name="customer_id[]" class="form-select form-select-sm select2-multi filterCustomerSelect" multiple data-placeholder="-- All Customers --">
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->customer_id ?? 'CUST' }} - {{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2. Product --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Product</label>
                        <select name="product_id[]" class="form-select form-select-sm select2-multi filterProductSelect" multiple data-placeholder="-- All Products --">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->item_code }} - {{ $p->item_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. From Date & To Date --}}
                    <div class="col-6 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">From Date</label>
                        <input type="date" name="from_date" id="filterFromDateMob" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" style="font-size: 11px;">
                    </div>
                    <div class="col-6 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">To Date</label>
                        <input type="date" name="to_date" id="filterToDateMob" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="font-size: 11px;">
                    </div>

                    {{-- Return Mode Toggle --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-primary" style="font-size: 11px;"><i class="fas fa-sync-alt me-1"></i>Returns Filter Mode</label>
                        <select name="return_mode" id="filterReturnModeMob" class="form-select form-select-sm fw-bold returnModeSelect" style="font-size: 11px; border-color: #93c5fd; background-color: #eff6ff;">
                            <option value="sale_date" selected>By Sale Date (Audit Mode)</option>
                            <option value="transaction_date">By Return Date (Accounts Mode)</option>
                        </select>
                    </div>

                    {{-- 4. Category --}}
                    <div class="col-12 mb-1">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Category</label>
                        <select name="category_id[]" class="form-select form-select-sm select2-multi filterCategorySelect" multiple data-placeholder="-- All Categories --">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Brand --}}
                    <div class="col-12 mb-2">
                        <label class="form-label mb-1 fw-bold text-secondary" style="font-size: 11px;">Brand</label>
                        <select name="brand_id[]" class="form-select form-select-sm select2-multi filterBrandSelect" multiple data-placeholder="-- All Brands --">
                            @foreach($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 6. Blue Full-Width Filter Button --}}
                    <div class="col-12 my-2">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm btnFilterTrigger" style="background-color: #3b82f6; border-color: #3b82f6; font-size: 13px;">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                    </div>

                    {{-- 7. Centralized Reset, Excel, Print Actions Row --}}
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-center gap-2 pt-1">
                            <button type="button" class="btn btn-light border btn-sm flex-fill fw-bold text-secondary btnResetTrigger" style="font-size: 11px; margin-right: 4px;">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm flex-fill fw-bold btnExportExcel" style="font-size: 11px; margin-right: 4px;">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill fw-bold btnPrintReport" style="font-size: 11px;">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- DESKTOP SUMMARY METRIC BAR (d-none d-md-block) --}}
    <div class="card border-0 shadow-sm mb-2 d-none d-md-block" style="border-radius: 10px; background: #ffffff;">
        <div class="card-body p-2">
            <div class="summary-pill-bar">
                
                <div class="stat-pill" style="background: #f8fafc; border-color: #cbd5e1;">
                    <div class="stat-label text-muted">Customers</div>
                    <div class="stat-val text-dark" id="pillCustomers">0</div>
                </div>

                <div class="stat-pill" style="background: #f8fafc; border-color: #cbd5e1;">
                    <div class="stat-label text-muted">Invoices</div>
                    <div class="stat-val text-dark" id="pillInvoices">0</div>
                </div>

                <div class="stat-pill" style="background: #f0f9ff; border-color: #bae6fd;">
                    <div class="stat-label text-info">Sold Qty</div>
                    <div class="stat-val text-primary" id="pillSoldQty">0</div>
                </div>

                <div class="stat-pill" style="background: #fdf4ff; border-color: #f0abfc;">
                    <div class="stat-label" style="color: #a21caf;">Bonus Qty</div>
                    <div class="stat-val" style="color: #86198f;" id="pillBonusQty">0</div>
                </div>

                <div class="stat-pill" style="background: #f8fafc; border-color: #cbd5e1;">
                    <div class="stat-label text-secondary">Gross (TP)</div>
                    <div class="stat-val text-dark" id="pillGrossSales">Rs 0.00</div>
                </div>

                <div class="stat-pill" style="background: #fff7ed; border-color: #fdba74;">
                    <div class="stat-label" style="color: #c2410c;">Discounts</div>
                    <div class="stat-val" style="color: #ea580c;" id="pillDiscounts">Rs 0.00</div>
                </div>

                <div class="stat-pill" style="background: #fffbeb; border-color: #fde047;">
                    <div class="stat-label" style="color: #b45309;">Ret Qty</div>
                    <div class="stat-val text-warning" id="pillReturnQty">0</div>
                </div>

                <div class="stat-pill" style="background: #fef2f2; border-color: #fca5a5;">
                    <div class="stat-label text-danger">Ret Sales</div>
                    <div class="stat-val text-danger" id="pillReturnSales">Rs 0.00</div>
                </div>

                <div class="stat-pill" style="background: #f0fdf4; border-color: #86efac;">
                    <div class="stat-label text-success">Net Sales</div>
                    <div class="stat-val text-success" id="pillNetSales">Rs 0.00</div>
                </div>

            </div>
        </div>
    </div>

    {{-- MOBILE SUMMARY METRIC GRID (2 Columns col-6 d-md-none With Y-Axis Spacing) --}}
    <div class="row g-2 mb-3 d-md-none no-print px-1">
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-users text-primary me-1"></i>Customers</span>
                <div class="mob-metric-val text-dark mt-1" id="mobMetricCustomers">0</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-file-invoice text-info me-1"></i>Invoices</span>
                <div class="mob-metric-val text-dark mt-1" id="mobMetricInvoices">0</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-box text-primary me-1"></i>Sold Qty</span>
                <div class="mob-metric-val text-primary mt-1" id="mobMetricSoldQty">0.00</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-gift me-1" style="color: #a21caf;"></i>Bonus Qty</span>
                <div class="mob-metric-val mt-1" style="color: #86198f;" id="mobMetricBonusQty">0.00</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-wallet text-secondary me-1"></i>Gross (TP)</span>
                <div class="mob-metric-val text-dark mt-1" id="mobMetricGrossSales">Rs 0.00</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-tags text-warning me-1"></i>Discounts</span>
                <div class="mob-metric-val mt-1" style="color: #ea580c;" id="mobMetricDiscounts">Rs 0.00</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-undo text-warning me-1"></i>Return Qty</span>
                <div class="mob-metric-val text-warning mt-1" id="mobMetricReturnQty">0.00</div>
            </div>
        </div>
        <div class="col-6 mb-1">
            <div class="mob-metric-card">
                <span class="mob-metric-label"><i class="fas fa-minus-circle text-danger me-1"></i>Return Sales</span>
                <div class="mob-metric-val text-danger mt-1" id="mobMetricReturnSales">Rs 0.00</div>
            </div>
        </div>
        <div class="col-12 mb-1">
            <div class="mob-metric-card" style="background: #f0fdf4; border-color: #86efac;">
                <span class="mob-metric-label text-success"><i class="fas fa-check-circle text-success me-1"></i>Net Sales (Final Revenue)</span>
                <div class="mob-metric-val text-success mt-1 fs-5" id="mobMetricNetSales">Rs 0.00</div>
            </div>
        </div>
    </div>

    {{-- Dynamic Customer Content Container (Renders Desktop Tables or Mobile Cards) --}}
    <div id="reportDataContainer">
        <div class="text-center py-4 text-muted card border-0 shadow-sm rounded-3 bg-white">
            <div class="card-body py-4">
                <i class="fas fa-spinner fa-spin fa-2x mb-2 text-secondary"></i>
                <p class="mb-0 small fw-bold">Loading Customer Sales Data…</p>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2 Multi-Select
            if ($.fn.select2) {
                $('.select2-multi').select2({
                    placeholder: "Select (All)",
                    allowClear: true,
                    width: '100%'
                });
            }

            // Sync Mobile & Desktop Date and Filter Inputs
            $('#filterFromDateDesk').on('change', function() { $('#filterFromDateMob').val($(this).val()); });
            $('#filterFromDateMob').on('change', function() { $('#filterFromDateDesk').val($(this).val()); });
            $('#filterToDateDesk').on('change', function() { $('#filterToDateMob').val($(this).val()); });
            $('#filterToDateMob').on('change', function() { $('#filterToDateDesk').val($(this).val()); });
            $('#filterReturnModeDesk').on('change', function() { $('#filterReturnModeMob').val($(this).val()); });
            $('#filterReturnModeMob').on('change', function() { $('#filterReturnModeDesk').val($(this).val()); });

            function loadReportData() {
                $('#reportDataContainer').html(`
                    <div class="text-center py-4 text-muted card border-0 shadow-sm rounded-3 bg-white">
                        <div class="card-body py-4">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0 small fw-bold">Fetching Customer Sales Data…</p>
                        </div>
                    </div>
                `);

                // Determine active form data based on screen size
                let formData = window.innerWidth < 768 ? $('#reportFilterFormMobile').serialize() : $('#reportFilterFormDesktop').serialize();

                $.ajax({
                    url: "{{ route('report.product_sale_customer_wise.fetch') }}",
                    type: "GET",
                    data: formData,
                    success: function(res) {
                        // Update Stat Pills (Desktop & Mobile)
                        if (res.summary) {
                            let custVal = res.summary.total_customers || 0;
                            let invVal  = res.summary.total_invoices || 0;
                            let soldVal = res.summary.total_qty || '0.00';
                            let bonusVal = res.summary.total_bonus_qty || '0.00';
                            let retQtyVal = res.summary.total_return_qty || '0.00';
                            let netQtyVal = res.summary.net_qty || '0.00';
                            let grossVal = res.summary.gross_amount || 'Rs 0.00';
                            let discVal = res.summary.discount_amount || 'Rs 0.00';
                            let retSalesVal = res.summary.return_amount || 'Rs 0.00';
                            let netSalesVal = res.summary.net_sale_amount || 'Rs 0.00';

                            // Desktop Pills
                            $('#pillCustomers').text(custVal);
                            $('#pillInvoices').text(invVal);
                            $('#pillSoldQty').text(soldVal);
                            $('#pillBonusQty').text(bonusVal);
                            $('#pillGrossSales').text(grossVal);
                            $('#pillDiscounts').text(discVal);
                            $('#pillReturnQty').text(retQtyVal);
                            $('#pillReturnSales').text(retSalesVal);
                            $('#pillNetSales').text(netSalesVal);

                            // Mobile Cards
                            $('#mobMetricCustomers').text(custVal);
                            $('#mobMetricInvoices').text(invVal);
                            $('#mobMetricSoldQty').text(soldVal);
                            $('#mobMetricBonusQty').text(bonusVal);
                            $('#mobMetricGrossSales').text(grossVal);
                            $('#mobMetricDiscounts').text(discVal);
                            $('#mobMetricReturnQty').text(retQtyVal);
                            $('#mobMetricReturnSales').text(retSalesVal);
                            $('#mobMetricNetSales').text(netSalesVal);
                        }

                        if (res.customers && res.customers.length) {
                            let html = '';
                            res.customers.forEach((cust) => {
                                html += `
                                    <div class="card border-0 shadow-sm rounded-3 mb-3 overflow-hidden bg-white" style="border-radius: 10px;">
                                        
                                        {{-- Customer Header --}}
                                        <div class="card-header bg-light border-bottom px-3 py-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <div>
                                                <span class="badge bg-primary text-white font-monospace me-1" style="font-size: 11px;">${cust.customer_code}</span>
                                                <strong class="text-dark" style="font-size: 13px;">${cust.customer_name}</strong>
                                                <small class="text-muted ms-2 d-block d-sm-inline" style="font-size: 11px;"><i class="fas fa-phone-alt me-1"></i>${cust.customer_mobile} | <i class="fas fa-map-marker-alt me-1"></i>${cust.customer_city}</small>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted me-1" style="font-size: 11px;">Customer Total Net:</small>
                                                <strong class="text-success" style="font-size: 13px;">Rs ${parseFloat(cust.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                                            </div>
                                        </div>

                                        {{-- DESKTOP TABLE VIEW (d-none d-md-block) --}}
                                        <div class="card-body p-0 d-none d-md-block">
                                            <div class="sale-table-wrap border-0 rounded-0">
                                                <table class="table table-bordered table-hover align-middle mb-0 report-table" style="font-size: 12px;">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center" style="width: 35px;">#</th>
                                                            <th style="width: 85px;">CODE</th>
                                                            <th>PRODUCT NAME</th>
                                                            <th style="width: 125px;">BRAND / CAT</th>
                                                            <th class="text-center" style="width: 65px;">SOLD</th>
                                                            <th class="text-center" style="width: 65px;">BONUS</th>
                                                            <th class="text-end" style="width: 85px;">AVG TP</th>
                                                            <th class="text-end" style="width: 95px;">GROSS (TP)</th>
                                                            <th class="text-end" style="width: 85px;">DISC</th>
                                                            <th class="text-center" style="width: 65px;">RET QTY</th>
                                                            <th class="text-end" style="width: 85px;">RET AMT</th>
                                                            <th class="text-center" style="width: 65px;">NET QTY</th>
                                                            <th class="text-end" style="width: 105px;">NET VALUE</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>`;
                                cust.products.forEach((p, idx) => {
                                    html += `
                                        <tr>
                                            <td class="text-center text-muted fw-bold">${idx + 1}</td>
                                            <td><span class="font-monospace text-dark">${p.product_code}</span></td>
                                            <td class="fw-semibold text-dark">${p.product_name}</td>
                                            <td class="text-muted small">${p.brand_name} / ${p.category_name}</td>
                                            <td class="text-center fw-bold text-dark">${parseFloat(p.sold_qty).toLocaleString()}</td>
                                            <td class="text-center text-info fw-bold">${p.bonus_qty > 0 ? parseFloat(p.bonus_qty).toLocaleString() : '-'}</td>
                                            <td class="text-end text-muted">Rs ${parseFloat(p.avg_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            <td class="text-end fw-semibold">Rs ${parseFloat(p.gross_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            <td class="text-end text-danger">${p.discount_amount > 0 ? 'Rs ' + parseFloat(p.discount_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</td>
                                            <td class="text-center text-secondary">${p.return_qty > 0 ? parseFloat(p.return_qty).toLocaleString() : '-'}</td>
                                            <td class="text-end text-danger">${p.return_amount > 0 ? 'Rs ' + parseFloat(p.return_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</td>
                                            <td class="text-center fw-bold text-dark">${parseFloat(p.net_qty).toLocaleString()}</td>
                                            <td class="text-end fw-bold text-success">Rs ${parseFloat(p.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                        </tr>`;
                                });
                                html += `
                                                    </tbody>
                                                    <tfoot class="bg-light fw-bold" style="font-size: 12px;">
                                                        <tr>
                                                            <td colspan="4" class="text-dark">SUBTOTAL (${cust.customer_name})</td>
                                                            <td class="text-center text-dark">${parseFloat(cust.sold_qty).toLocaleString()}</td>
                                                            <td class="text-center text-info">${parseFloat(cust.bonus_qty || 0).toLocaleString()}</td>
                                                            <td></td>
                                                            <td class="text-end text-dark">Rs ${parseFloat(cust.gross_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            <td class="text-end text-danger">Rs ${parseFloat(cust.discount_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            <td class="text-center text-secondary">${parseFloat(cust.return_qty).toLocaleString()}</td>
                                                            <td class="text-end text-danger">Rs ${parseFloat(cust.return_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            <td class="text-center text-dark">${parseFloat(cust.net_qty).toLocaleString()}</td>
                                                            <td class="text-end text-success">Rs ${parseFloat(cust.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- MOBILE ITEM CARDS VIEW (d-md-none Matching Reference Screenshot 100%) --}}
                                        <div class="card-body p-2 d-md-none bg-light">`;
                                cust.products.forEach((p, idx) => {
                                    html += `
                                            <div class="mob-card p-2.5 p-2 mb-2">
                                                <div class="mb-1">
                                                    <div class="d-flex align-items-center gap-1 mb-1">
                                                        <span class="badge bg-light text-muted border" style="font-size: 10px;">#${idx + 1}</span>
                                                        <span class="badge bg-light text-primary border font-monospace" style="font-size: 11px;">${p.product_code}</span>
                                                    </div>
                                                    <div class="fw-bold text-dark" style="font-size: 13px; line-height: 1.2;">${p.product_name}</div>
                                                    <small class="text-muted" style="font-size: 11px;">${p.brand_name} / ${p.category_name}</small>
                                                </div>
                                                <div class="border-top pt-2 mt-1">
                                                    <div class="row g-1 text-center" style="font-size: 11px;">
                                                        <div class="col-4 border-end">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Sold Qty</span>
                                                            <strong class="text-dark">${parseFloat(p.sold_qty).toLocaleString()}</strong>
                                                        </div>
                                                        <div class="col-4 border-end">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Bonus Qty</span>
                                                            <strong class="text-info">${p.bonus_qty > 0 ? parseFloat(p.bonus_qty).toLocaleString() : '0'}</strong>
                                                        </div>
                                                        <div class="col-4">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Avg TP</span>
                                                            <strong class="text-dark">Rs ${parseFloat(p.avg_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                                                        </div>
                                                        <div class="col-4 border-end pt-1 border-top mt-1">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Gross (TP)</span>
                                                            <strong class="text-dark">Rs ${parseFloat(p.gross_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                                                        </div>
                                                        <div class="col-4 border-end pt-1 border-top mt-1">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Discount</span>
                                                            <strong class="text-danger">${p.discount_amount > 0 ? 'Rs ' + parseFloat(p.discount_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</strong>
                                                        </div>
                                                        <div class="col-4 pt-1 border-top mt-1">
                                                            <span class="text-muted d-block" style="font-size: 10px;">Ret Qty / Amt</span>
                                                            <strong class="text-warning">${p.return_qty > 0 ? parseFloat(p.return_qty).toLocaleString() + ' / Rs ' + parseFloat(p.return_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</strong>
                                                        </div>
                                                        <div class="col-12 pt-1 border-top mt-1 text-center">
                                                            <span class="text-muted me-2" style="font-size: 10.5px;">Net Qty: <strong class="text-dark">${parseFloat(p.net_qty).toLocaleString()}</strong></span>
                                                            <span class="text-muted" style="font-size: 10.5px;">Net Value: <strong class="text-success">Rs ${parseFloat(p.net_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>`;
                                });
                                html += `
                                        </div>
                                    </div>`;
                            });
                            $('#reportDataContainer').html(html);
                        } else {
                            $('#reportDataContainer').html(`
                                <div class="card border-0 shadow-sm rounded-3 text-center py-4 bg-white">
                                    <div class="card-body py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 text-secondary"></i>
                                        <p class="small fw-bold mb-0">No Sales Data Found</p>
                                    </div>
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $('#reportDataContainer').html(`
                            <div class="card border-0 shadow-sm rounded-3 text-center py-4 bg-white">
                                <div class="card-body py-4 text-danger">
                                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                                    <p class="small fw-bold mb-0">Error loading report data. Please try again.</p>
                                </div>
                            </div>
                        `);
                    }
                });
            }

            // Initial Load
            loadReportData();

            // Form Submit Triggers
            $('#reportFilterFormDesktop, #reportFilterFormMobile').on('submit', function(e) {
                e.preventDefault();
                loadReportData();
            });

            $('.btnFilterTrigger').on('click', function(e) {
                e.preventDefault();
                loadReportData();
            });

            // Reset Filters
            $('.btnResetTrigger').on('click', function() {
                $('#filterFromDateDesk, #filterFromDateMob').val('{{ date("Y-m-01") }}');
                $('#filterToDateDesk, #filterToDateMob').val('{{ date("Y-m-d") }}');
                $('#filterReturnModeDesk, #filterReturnModeMob').val('sale_date');
                $('.select2-multi').val(null).trigger('change');
                loadReportData();
            });

            // Print & Export Buttons
            $('.btnPrintReport').on('click', () => window.print());
            $('.btnExportExcel').on('click', function() {
                let csv = [];
                csv.push(['Customer Code','Customer Name','Product Code','Product Name','Brand / Category','Sold Qty','Bonus Qty','Avg TP','Gross TP','Discount','Return Qty','Return Amount','Net Qty','Net Value'].join(','));
                $('#reportDataContainer .card').each(function(){
                    let custCode = $(this).find('.badge').first().text().trim();
                    let custName = $(this).find('.card-header strong').first().text().trim();
                    $(this).find('table tbody tr').each(function(){
                        let row = [custCode,custName];
                        $(this).find('td').each(function(idx){
                            if(idx===0) return; // skip index column
                            let txt = $(this).text().trim().replace(/,/g,'').replace(/Rs\s?/g,'');
                            row.push('"' + txt + '"');
                        });
                        csv.push(row.join(','));
                    });
                });
                let blob = new Blob([csv.join('\n')], {type:'text/csv;charset=utf-8;'});
                let link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'Product_Wise_Customer_Sale_Report.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
@endsection
