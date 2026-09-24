@extends('admin_panel.layout.app')

@section('content')
<style>
    /* ── ERP Item Stock & Movement Ledger System ── */
    :root {
        --rpt-primary:    #4f46e5;
        --rpt-primary-lt: #eef2ff;
        --rpt-success:    #059669;
        --rpt-success-lt: #ecfdf5;
        --rpt-warning:    #d97706;
        --rpt-warning-lt: #fffbeb;
        --rpt-danger:     #dc2626;
        --rpt-danger-lt:  #fef2f2;
        --rpt-info:       #0284c7;
        --rpt-info-lt:    #e0f2fe;
        --rpt-border:     #e2e8f0;
        --rpt-bg:         #f8fafc;
        --rpt-card-bg:    #ffffff;
        --rpt-text:       #1e293b;
        --rpt-muted:      #64748b;
        --rpt-radius:     12px;
        --rpt-shadow:     0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    }

    .rpt-page { background: var(--rpt-bg); min-height: calc(100vh - 80px); padding: 20px 0; }

    /* Report Mode Selector Pills */
    .mode-pills { display: flex; gap: 8px; margin-bottom: 16px; }
    .mode-pill {
        padding: 8px 18px; border-radius: 20px; border: 1px solid var(--rpt-border);
        background: #fff; font-size: .82rem; font-weight: 700; color: var(--rpt-muted);
        cursor: pointer; transition: all .15s ease; display: inline-flex; align-items: center; gap: 6px;
    }
    .mode-pill.active, .mode-pill:hover { background: var(--rpt-primary); color: #fff; border-color: var(--rpt-primary); box-shadow: 0 4px 12px rgba(79,70,229,.2); }

    /* Filter Card */
    .rpt-filter-card {
        background: #ffffff; border-radius: var(--rpt-radius); border: 1px solid var(--rpt-border);
        box-shadow: var(--rpt-shadow); padding: 16px 20px; margin-bottom: 20px;
    }
    .rpt-flabel { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--rpt-muted); margin-bottom: 4px; display: block; }
    .rpt-finput { width: 100%; height: 38px; border: 1px solid var(--rpt-border); border-radius: 8px; font-size: .84rem; padding: 0 10px; color: var(--rpt-text); outline: none; background: #fff; }

    /* KPI Summary Cards */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
    .kpi-card {
        border-radius: var(--rpt-radius); padding: 16px 20px; color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        box-shadow: var(--rpt-shadow); transition: transform .2s ease;
    }
    .kpi-card:hover { transform: translateY(-2px); }

    /* Badges & Tables */
    .badge-adj-plus  { background: var(--rpt-success-lt); color: var(--rpt-success); border: 1px solid #a7f3d0; border-radius: 12px; padding: 2px 8px; font-weight: 700; font-size: .74rem; }
    .badge-adj-minus { background: var(--rpt-danger-lt);  color: var(--rpt-danger);  border: 1px solid #fecaca; border-radius: 12px; padding: 2px 8px; font-weight: 700; font-size: .74rem; }

    .status-healthy { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; border-radius: 12px; padding: 2px 8px; font-size: .7rem; font-weight: 700; }
    .status-low     { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 12px; padding: 2px 8px; font-size: .7rem; font-weight: 700; }
    .status-out     { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; border-radius: 12px; padding: 2px 8px; font-size: .7rem; font-weight: 700; }

    #stockTable th { background: #f8fafc !important; color: #475569 !important; font-size: .68rem; text-transform: uppercase; font-weight: 700; letter-spacing: .5px; padding: 10px 12px; }
    #stockTable td { font-size: .8rem; vertical-align: middle; padding: 9px 12px; }

    /* Select2 styling matching ERP form controls */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid var(--rpt-border) !important;
        border-radius: 8px !important;
        display: flex !important;
        align-items: center !important;
        background: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 10px !important;
        color: var(--rpt-text) !important;
        font-size: .84rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #f1f5f9 !important;
        cursor: not-allowed !important;
        opacity: 0.8 !important;
    }

    @media (max-width: 768px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .mode-pills { flex-wrap: wrap; }
    }
</style>

<div class="rpt-page">
<div class="container-fluid px-3">

    {{-- Page Header & Mode Switcher --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold text-dark mb-0"><i class="fas fa-boxes-stacked text-primary me-2"></i>Item Stock &amp; Ledger Report</h4>
            <small class="text-muted">Detailed inventory movements, explicit Stock Adjustments (+/-), and Valuation Summary</small>
        </div>

        {{-- Mode Pills --}}
        <div class="mode-pills">
            <div class="mode-pill active" data-mode="summary">
                <i class="fas fa-chart-pie"></i> Valuation &amp; Stock Summary
            </div>
            <div class="mode-pill" data-mode="ledger">
                <i class="fas fa-file-invoice-dollar"></i> Detailed Movement Ledger
            </div>
        </div>
    </div>

    {{-- KPI Summary Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
            <div>
                <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; opacity:.85;">Grand Stock Value</div>
                <div class="fs-4 fw-bold mt-1" id="kpiGrandStockValue">Rs 0.00</div>
            </div>
            <div style="font-size:24px; opacity:.8;"><i class="fas fa-coins"></i></div>
        </div>

        <div class="kpi-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Current Total Stock</div>
                <div class="fs-4 fw-bold mt-1" id="kpiTotalStock">0 Units</div>
            </div>
            <div style="font-size:24px; opacity:.8;"><i class="fas fa-cubes"></i></div>
        </div>

        <div class="kpi-card" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
            <div>
                <div class="text-uppercase fw-bold text-muted" style="font-size: 0.75rem; letter-spacing: 0.5px;">Net Adjustments</div>
                <div class="fs-4 fw-bold mt-1" id="kpiNetAdjustments">0 Units</div>
            </div>
            <div style="font-size:24px; opacity:.8;"><i class="fas fa-sliders-h"></i></div>
        </div>

        <div class="kpi-card" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div>
                <div style="font-size:.7rem; font-weight:700; text-transform:uppercase; opacity:.85;">Total Sold Amount</div>
                <div class="fs-4 fw-bold mt-1" id="kpiSoldAmount">Rs 0.00</div>
            </div>
            <div style="font-size:24px; opacity:.8;"><i class="fas fa-shopping-cart"></i></div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="rpt-filter-card">
        <form id="stockFilterForm" class="row g-2 align-items-end">
            <input type="hidden" name="report_mode" id="report_mode" value="summary">

            {{-- Category --}}
            <div class="col-md-2">
                <label class="rpt-flabel">Category</label>
                <select name="category_id" id="category_id" class="rpt-finput">
                    <option value="all">-- All Categories --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Warehouse --}}
            <div class="col-md-2">
                <label class="rpt-flabel">Warehouse</label>
                <select name="warehouse_id" id="warehouse_id" class="rpt-finput">
                    @if(isset($warehouses) && count($warehouses) > 0)
                        <option value="all">-- All Warehouses --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name ?? $wh->warehouse_name }}</option>
                        @endforeach
                    @else
                        <option value="all">-- Main Stock --</option>
                    @endif
                </select>
            </div>

            {{-- Unit Mode Filter --}}
            <div class="col-md-2">
                <label class="rpt-flabel">Unit / Size Type</label>
                <select name="unit_type" id="unit_type" class="rpt-finput">
                    <option value="all">All Units (Pcs / Kg / M²)</option>
                    <option value="cartons_pcs">Pieces &amp; Cartons</option>
                    <option value="weight_kg">Weight Units (Kg / Gm)</option>
                    <option value="area_m2">Area Units (M² / Feet)</option>
                </select>
            </div>

            {{-- Product --}}
            <div class="col-md-2">
                <label class="rpt-flabel">Search Product</label>
                <select name="product_id" id="product_id" class="form-control select2">
                    <option value="all">-- All Products --</option>
                </select>
            </div>

            {{-- Product Variant --}}
            <div class="col-md-2">
                <label class="rpt-flabel">Product Variant</label>
                <select name="variant_key" id="variant_key" class="form-control select2" disabled>
                    <option value="all">-- All Variants --</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="col-md-2 text-end d-flex gap-2">
                <button type="button" id="btnSearch" class="btn btn-primary btn-sm flex-fill fw-bold" style="height:38px; border-radius:8px;">
                    <i class="fas fa-search me-1"></i> Apply Filter
                </button>
                <button type="button" id="btnExportCsv" class="btn btn-outline-secondary btn-sm fw-bold" style="height:38px; border-radius:8px;">
                    <i class="fas fa-file-excel me-1"></i> Export
                </button>
            </div>
        </form>
    </div>

    {{-- Data Table Card --}}
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            
            <div id="loader" style="display:none; text-align:center; padding:30px;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted small">Generating detailed stock report...</p>
            </div>

            <div class="table-responsive">
                <table id="stockTable" class="table table-hover align-middle mb-0 nowrap" style="width:100%;">
                    <thead id="tableHeader">
                        {{-- Rendered dynamically based on Summary vs Ledger mode --}}
                    </thead>
                    <tbody id="reportBody">
                        {{-- Filled by AJAX --}}
                    </tbody>
                    <tfoot id="tableFooter">
                        {{-- Grand Totals --}}
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

</div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PRODUCT MOVEMENT HISTORY TIMELINE MODAL
══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="productHistoryModal" tabindex="-1" aria-labelledby="productHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            
            <div class="modal-header border-bottom bg-white px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="productHistoryModalLabel">
                        <i class="fas fa-history text-primary me-2"></i>Product Movement Timeline
                    </h5>
                    <small class="text-muted" id="historyModalSub">Chronological audit log of all inward, sales, returns &amp; stock adjustments</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0" style="max-height:420px; overflow-y:auto; background:#f8fafc;">
                <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size:.82rem;">
                    <thead class="bg-white sticky-top">
                        <tr>
                            <th class="ps-4">Date &amp; Time</th>
                            <th>Movement Type</th>
                            <th>Reference</th>
                            <th class="text-center">Quantity (Units)</th>
                            <th>Note / Reason</th>
                        </tr>
                    </thead>
                    <tbody id="historyModalBody">
                        {{-- Dynamically populated --}}
                    </tbody>
                </table>
            </div>

            <div class="modal-footer bg-white py-2 px-4 border-top">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
$(document).ready(function() {

    let currentReportData = [];

    // Select2 Product Search
    $('#product_id').select2({
        placeholder: "-- All Products --",
        allowClear: true,
        width: '100%',
        ajax: {
            url: "{{ route('products.search') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id,
                            text: item.item_code + ' - ' + item.item_name
                        }
                    })
                };
            },
            cache: true
        }
    });

    // Select2 Variant Filter
    $('#variant_key').select2({
        placeholder: "-- All Variants --",
        allowClear: true,
        width: '100%'
    });

    $('#product_id').on('select2:unselect', function () {
        $(this).val('all').trigger('change');
    });

    $('#variant_key').on('select2:unselect', function () {
        $(this).val('all').trigger('change');
    });

    // When Product changes, fetch variants dynamically
    $('#product_id').on('change', function () {
        let productId = $(this).val();
        let variantSelect = $('#variant_key');

        variantSelect.empty();
        variantSelect.append('<option value="all">-- All Variants --</option>');

        if (productId && productId !== 'all') {
            variantSelect.prop('disabled', true);
            $.ajax({
                url: "/report/product-variants/" + productId,
                type: "GET",
                success: function (res) {
                    if (res.success && res.has_variants && res.variants && res.variants.length > 0) {
                        $.each(res.variants, function (i, v) {
                            variantSelect.append(new Option(v.text, v.key, false, false));
                        });
                        variantSelect.prop('disabled', false);
                    } else {
                        variantSelect.empty();
                        variantSelect.append('<option value="all">-- No Variants (Standard Item) --</option>');
                        variantSelect.prop('disabled', true);
                    }
                    variantSelect.val('all').trigger('change.select2');
                },
                error: function () {
                    variantSelect.prop('disabled', true);
                }
            });
        } else {
            variantSelect.prop('disabled', true);
            variantSelect.val('all').trigger('change.select2');
        }
    });

    // Report Mode Pill Toggle
    $('.mode-pill').on('click', function () {
        $('.mode-pill').removeClass('active');
        $(this).addClass('active');
        $('#report_mode').val($(this).data('mode'));
        fetchStockReport();
    });

    // Search Click
    $('#btnSearch').on('click', function () {
        fetchStockReport();
    });

    // Initial Load
    fetchStockReport();

    function fetchStockReport() {
        $('#loader').show();
        $('#reportBody').empty();

        let mode = $('#report_mode').val();
        renderTableHeader(mode);

        $.ajax({
            url: "{{ route('report.item_stock.fetch') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                category_id: $('#category_id').val(),
                product_id:  $('#product_id').val(),
                variant_key: $('#variant_key').val(),
                warehouse_id: $('#warehouse_id').val(),
                unit_type:   $('#unit_type').val(),
                report_mode: mode
            },
            success: function (res) {
                $('#loader').hide();
                currentReportData = res.data || [];

                // Update KPIs
                $('#kpiGrandStockValue').text('Rs ' + (res.grand_total || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));
                
                let totalStock = res.total_current_stock || 0;
                $('#kpiTotalStock').text(totalStock.toLocaleString() + ' Units');
                
                let netAdj = res.total_adjustments_qty || 0;
                let netAdjStr = (netAdj >= 0 ? '+' : '') + netAdj.toLocaleString() + ' Units';
                $('#kpiNetAdjustments').text(netAdjStr);
                
                $('#kpiSoldAmount').text('Rs ' + (res.total_sold_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2}));

                renderTableBody(res.data, mode);
            },
            error: function () {
                $('#loader').hide();
                alert('Error loading stock report data.');
            }
        });
    }

    function renderTableHeader(mode) {
        let thead = $('#tableHeader');
        thead.empty();

        if (mode === 'summary') {
            // Valuation & Stock Summary Columns
            thead.append(`
                <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:100px;">Item Code</th>
                    <th>Item / Variant Name</th>
                    <th>Category</th>
                    <th style="width:100px;">Unit Type</th>
                    <th class="text-center" style="width:120px;">Stock Balance</th>
                    <th class="text-center" style="width:110px;">Cartons / Loose</th>
                    <th class="text-end" style="width:110px;">Avg Cost (Rs)</th>
                    <th class="text-end" style="width:130px;">Stock Value (Rs)</th>
                    <th class="text-center" style="width:100px;">Status</th>
                    <th class="text-center" style="width:80px;">History</th>
                </tr>
            `);
        } else {
            // Detailed Movement Ledger Columns
            thead.append(`
                <tr>
                    <th style="width:30px;">#</th>
                    <th style="width:90px;">Item Code</th>
                    <th>Item / Variant Name</th>
                    <th class="text-end">Opening</th>
                    <th class="text-end">Purchased (+)</th>
                    <th class="text-end">Sold (-)</th>
                    <th class="text-end">Sale Return (+)</th>
                    <th class="text-end">Purch Return (-)</th>
                    <th class="text-center" style="background:#fffbeb !important; color:#b45309 !important;">Adjustments (+/-)</th>
                    <th class="text-end fw-bold" style="background:#eef2ff !important; color:#4f46e5 !important;">Closing Stock</th>
                    <th class="text-end">Stock Value (Rs)</th>
                    <th class="text-center" style="width:70px;">History</th>
                </tr>
            `);
        }
    }

    function renderTableBody(data, mode) {
        let tbody  = $('#reportBody');
        let tfooter= $('#tableFooter');
        tbody.empty();
        tfooter.empty();

        if (!data || data.length === 0) {
            tbody.append(`<tr><td colspan="12" class="text-center py-5 text-muted">No stock data found for the selected filters.</td></tr>`);
            return;
        }

        let totalValue = 0;

        $.each(data, function (i, row) {
            totalValue += parseFloat(row.stock_value) || 0;

            let historyBtn = `
                <button type="button" class="btn btn-outline-primary btn-sm view-history-btn" data-id="${row.id}" data-name="${row.item_name}" style="padding:2px 7px; font-size:.75rem;">
                    <i class="fas fa-history"></i>
                </button>
            `;

            let isNegative = (parseFloat(row.balance) < 0);
            let stockColorClass = isNegative ? 'text-danger' : 'text-primary';
            let cartonColorClass = isNegative ? 'text-danger' : 'text-secondary';

            if (mode === 'summary') {
                let statusBadge = '<span class="status-healthy"><i class="fas fa-check-circle me-1"></i> Healthy</span>';
                if (row.status === 'out_of_stock') {
                    statusBadge = '<span class="status-out"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>';
                } else if (row.status === 'low_stock') {
                    statusBadge = '<span class="status-low"><i class="fas fa-exclamation-triangle me-1"></i> Low Stock</span>';
                }

                let rowHtml = `
                <tr>
                    <td class="text-muted" style="font-size:.72rem;">${i + 1}</td>
                    <td><code style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:1px 5px; font-size:.72rem;">${row.item_code || ''}</code></td>
                    <td class="fw-semibold">${row.item_name}</td>
                    <td>${row.category_name}</td>
                    <td><span class="badge bg-light text-dark border">${row.unit_name}</span></td>
                    <td class="text-center fw-bold ${stockColorClass}">${row.formatted_stock}</td>
                    <td class="text-center fw-semibold ${cartonColorClass}">${row.carton_display || '—'}</td>
                    <td class="text-end">Rs ${parseFloat(row.average_price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-end fw-bold text-dark">Rs ${parseFloat(row.stock_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${historyBtn}</td>
                </tr>`;
                tbody.append(rowHtml);

            } else {
                // Detailed Movement Ledger Row
                let adjVal = parseFloat(row.adjustments) || 0;
                let adjBadge = '<span class="text-muted">—</span>';
                if (adjVal > 0) {
                    adjBadge = `<span class="badge-adj-plus">+${adjVal} ${row.unit_name}</span>`;
                } else if (adjVal < 0) {
                    adjBadge = `<span class="badge-adj-minus">${adjVal} ${row.unit_name}</span>`;
                }

                let rowHtml = `
                <tr>
                    <td class="text-muted" style="font-size:.72rem;">${i + 1}</td>
                    <td><code style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:1px 5px; font-size:.72rem;">${row.item_code || ''}</code></td>
                    <td class="fw-semibold">${row.item_name}</td>
                    <td class="text-end text-muted">${parseFloat(row.initial_stock).toLocaleString()}</td>
                    <td class="text-end text-success">+${parseFloat(row.purchased).toLocaleString()}</td>
                    <td class="text-end text-danger">-${parseFloat(row.sold).toLocaleString()}</td>
                    <td class="text-end text-success">+${parseFloat(row.returned_qty).toLocaleString()}</td>
                    <td class="text-end text-danger">-${parseFloat(row.purch_returned_qty).toLocaleString()}</td>
                    <td class="text-center" style="background:#fffbeb !important;">${adjBadge}</td>
                    <td class="text-end fw-bold ${stockColorClass}" style="${isNegative ? 'background:#fef2f2 !important; color:#dc2626 !important;' : 'background:#eef2ff !important; color:#4f46e5 !important;'}">${row.formatted_stock}</td>
                    <td class="text-end fw-bold">Rs ${parseFloat(row.stock_value).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td class="text-center">${historyBtn}</td>
                </tr>`;
                tbody.append(rowHtml);
            }
        });

        // Table Footer Summary
        if (mode === 'summary') {
            tfooter.append(`
                <tr class="bg-light fw-bold">
                    <td colspan="8" class="text-end">Grand Stock Valuation Total:</td>
                    <td class="text-end text-primary fs-6">Rs ${totalValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td colspan="2"></td>
                </tr>
            `);
        } else {
            tfooter.append(`
                <tr class="bg-light fw-bold">
                    <td colspan="10" class="text-end">Grand Stock Valuation Total:</td>
                    <td class="text-end text-primary fs-6">Rs ${totalValue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td></td>
                </tr>
            `);
        }
    }

    // View Product History Timeline Modal
    $(document).on('click', '.view-history-btn', function () {
        let productId = $(this).data('id');
        let productName = $(this).data('name');

        $('#productHistoryModalLabel').html('<i class="fas fa-history text-primary me-2"></i>Movement Timeline: ' + productName);
        let tbody = $('#historyModalBody');
        tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading movement history...</td></tr>');
        $('#productHistoryModal').modal('show');

        $.ajax({
            url: "/report/item-stock-history/" + productId,
            type: "GET",
            success: function (res) {
                tbody.empty();
                if (res.success && res.history.length > 0) {
                    $.each(res.history, function (i, m) {
                        let row = `
                        <tr>
                            <td class="ps-4 text-muted" style="font-size:.78rem;">${m.date}</td>
                            <td><span class="badge bg-${m.type_badge} px-2 py-1">${m.type}</span></td>
                            <td><code class="text-dark">${m.ref_type}</code></td>
                            <td class="text-center fw-bold fs-6">${m.qty > 0 ? '+' : ''}${m.qty}</td>
                            <td style="font-size:.78rem; color:#475569;">${m.note}</td>
                        </tr>`;
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted">No stock movements recorded for this item yet.</td></tr>');
                }
            }
        });
    });

    // Export CSV
    $('#btnExportCsv').on('click', function () {
        if (!currentReportData || currentReportData.length === 0) {
            alert('No report data to export.');
            return;
        }

        let csv = 'Item Code,Item Name,Category,Unit,Current Stock,Cartons,Avg Price (Rs),Stock Value (Rs)\n';
        $.each(currentReportData, function (i, r) {
            csv += `"${r.item_code || ''}","${r.item_name}","${r.category_name}","${r.unit_name}","${r.balance}","${r.cartons}","${r.average_price}","${r.stock_value}"\n`;
        });

        let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download = "Item_Stock_Report_" + new Date().toISOString().slice(0,10) + ".csv";
        link.click();
    });

});
</script>
@endsection
