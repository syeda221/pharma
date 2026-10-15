@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Consistent Typography with Navbar & Admin Dashboard */
    .closing-container,
    .closing-container input,
    .closing-container select,
    .closing-container button,
    .closing-container textarea,
    .closing-container table {
        font-family: 'Poppins', 'Montserrat', 'Outfit', sans-serif !important;
    }

    .closing-container {
        padding: 20px 24px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    /* Cards with Dark Crisp Borders */
    .closing-card-dark {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    /* Metric Pills with Richer Colors & Dark Borders */
    .closing-pill-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow-x: auto;
        white-space: nowrap;
    }
    .closing-pill {
        flex: 1 1 0px;
        min-width: 160px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #ffffff;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .closing-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .closing-pill .pill-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }
    .closing-pill .pill-val {
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Table & Inputs with Dark Defined Borders */
    .closing-table-wrap {
        max-height: calc(100vh - 380px);
        min-height: 350px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        position: relative;
    }
    .closing-table {
        font-size: .82rem;
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
        min-width: 1050px;
    }
    .closing-table th, 
    .closing-table td {
        border: 1px solid #cbd5e1 !important;
    }
    .closing-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #0f172a !important;
        color: #f8fafc !important;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 11px 10px;
        white-space: nowrap;
    }
    .closing-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Fixed Checkbox Styling: overrides Bootstrap 4 position:absolute bug on .form-check-input */
    .check-adjust {
        position: static !important;
        display: inline-block !important;
        margin: 0 auto !important;
        width: 18px !important;
        height: 18px !important;
        cursor: pointer !important;
        accent-color: #2563eb !important;
        vertical-align: middle !important;
    }

    .closing-input {
        height: 32px;
        font-size: .84rem;
        font-weight: 700;
        text-align: center;
        border-radius: 6px;
        border: 1.5px solid #64748b !important;
        color: #0f172a;
        background: #ffffff;
    }
    .closing-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
        outline: none;
    }

    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        opacity: 0.6;
    }

    /* Adjustment Box with Dark Accent */
    .adjustment-box {
        border: 1.5px solid #334155 !important;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .adjustment-box .card-header {
        background: #0f172a !important;
        color: #ffffff !important;
        border-bottom: 1.5px solid #1e293b !important;
    }

    /* Action radio card */
    .adj-radio-card {
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .adj-radio-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .adj-radio-card.active-mode {
        border-color: #2563eb !important;
        background: #eff6ff !important;
    }
</style>

<div class="closing-container">

    {{-- TOP FILTER & CONTROLS CARD --}}
    <div class="card closing-card-dark mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom" style="border-bottom: 1.5px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold" style="font-size: 11px; border-radius: 6px;">
                        <i class="fas fa-edit me-1"></i>EDIT AUDIT
                    </span>
                    <span class="fw-bold text-dark fs-6 font-monospace">
                        {{ $closing->closing_code }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 11.5px; border-radius: 6px;">
                        <i class="fas fa-warehouse me-1"></i>{{ $closing->warehouse->warehouse_name ?? ($mainWarehouse->warehouse_name ?? 'Main Store') }}
                    </span>
                    <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 11px;">
                        <i class="fas fa-calendar-alt me-1 text-primary"></i>Month: {{ \Carbon\Carbon::parse($closing->closing_month . '-01')->format('F Y') }}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('stock-closing.show', $closing->id) }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold" style="border-radius: 6px; font-size: .82rem; border: 1.5px solid #cbd5e1;">
                        <i class="fas fa-eye me-1"></i> View Audit Details
                    </a>
                    <a href="{{ route('stock-closing.index') }}" class="btn btn-light border btn-sm px-3 fw-semibold text-secondary" style="border-radius: 6px; font-size: .82rem; border: 1.5px solid #cbd5e1 !important;">
                        <i class="fas fa-history me-1"></i> Audit History
                    </a>
                </div>
            </div>

            {{-- Audit Edit Notice --}}
            <div class="alert alert-warning border border-warning-subtle d-flex align-items-center gap-2 py-2 px-3 mb-2" style="border-radius: 8px;">
                <i class="fas fa-info-circle text-warning fs-5"></i>
                <div class="small">
                    <strong>Editing Mode:</strong> You can correct physical stock counts below. When you click <strong>Update & Re-apply Monthly Closing</strong>, previous stock movements for this audit will be safely reversed and re-applied with the new counts.
                </div>
            </div>

            {{-- Filter & Meta Fields --}}
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-bold text-secondary small">Closing Month (Locked)</label>
                    <input type="month" id="closingMonthInput" class="form-control form-control-sm fw-bold bg-light" value="{{ $closing->closing_month }}" readonly style="height: 34px; border-radius: 6px; border: 1.5px solid #64748b; cursor: not-allowed;">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-bold text-secondary small">Audit Date <span class="text-danger">*</span></label>
                    <input type="date" id="closingDateInput" class="form-control form-control-sm fw-bold" value="{{ $closing->closing_date }}" style="height: 34px; border-radius: 6px; border: 1.5px solid #64748b;">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-bold text-secondary small">Category Filter</label>
                    <select id="categoryFilter" class="form-select form-select-sm select2" style="border: 1.5px solid #64748b;">
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-bold text-secondary small">Search Item / Code</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted" style="border: 1.5px solid #64748b; border-right: none;"><i class="fas fa-search"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search item name or code..." style="height: 34px; border-radius: 0 6px 6px 0; border: 1.5px solid #64748b;">
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- METRIC SUMMARY PILL BAR --}}
    <div class="card closing-card-dark mb-3">
        <div class="card-body p-2.5">
            <div class="closing-pill-bar">
                
                <div class="closing-pill" style="background: #ffffff; border: 1.5px solid #64748b;">
                    <div class="pill-label text-secondary"><i class="fas fa-boxes-stacked me-1"></i>Total Products</div>
                    <div class="pill-val text-dark" id="pillTotalItems">{{ $closing->total_items_count }}</div>
                </div>

                <div class="closing-pill" style="background: #ecfdf5; border: 1.5px solid #10b981;">
                    <div class="pill-label text-success"><i class="fas fa-check-circle me-1"></i>Matched Stock</div>
                    <div class="pill-val text-success" id="pillMatchedItems">{{ $closing->matched_count }}</div>
                </div>

                <div class="closing-pill" style="background: #fff1f2; border: 1.5px solid #f43f5e;">
                    <div class="pill-label" style="color: #be123c;"><i class="fas fa-triangle-exclamation me-1"></i>Discrepancies</div>
                    <div class="pill-val" style="color: #be123c;" id="pillDiscrepancies">{{ $closing->discrepant_count }}</div>
                </div>

                <div class="closing-pill" style="background: #fef2f2; border: 1.5px solid #ef4444;">
                    <div class="pill-label text-danger"><i class="fas fa-arrow-down me-1"></i>Total Shortage</div>
                    <div class="pill-val text-danger" id="pillTotalShortage">0 Pcs</div>
                </div>

                <div class="closing-pill" style="background: #eff6ff; border: 1.5px solid #3b82f6;">
                    <div class="pill-label text-primary"><i class="fas fa-arrow-up me-1"></i>Total Surplus</div>
                    <div class="pill-val text-primary" id="pillTotalSurplus">0 Pcs</div>
                </div>

            </div>
        </div>
    </div>

    {{-- GRID TABLE CARD --}}
    <div class="card closing-card-dark mb-3">
        <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom" style="border-radius: 12px 12px 0 0; border-bottom: 1.5px solid #cbd5e1 !important;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark fs-6">
                    <i class="fas fa-list-ol text-primary me-1.5"></i>Editable Physical Stock Grid
                </span>
                <span class="badge bg-light text-secondary border small" id="tableCountBadge">{{ count($closing->items) }} Items</span>
            </div>

            {{-- Quick Table Action Buttons --}}
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-xs btn-outline-primary py-1.5 px-3 fw-bold" id="btnFillAll" style="font-size: 11.5px; border-radius: 6px; border: 1.5px solid #3b82f6;">
                    <i class="fas fa-magic me-1"></i> Fill All with System Stock
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-1.5 px-3 fw-bold" id="btnClearAll" style="font-size: 11.5px; border-radius: 6px; border: 1.5px solid #64748b;">
                    <i class="fas fa-eraser me-1"></i> Clear Physical Counts
                </button>
                <button type="button" class="btn btn-xs btn-outline-danger py-1.5 px-3 fw-bold" id="btnFilterDiscrepancies" style="font-size: 11.5px; border-radius: 6px; border: 1.5px solid #ef4444;">
                    <i class="fas fa-filter me-1"></i> Show Discrepancies Only
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="closing-table-wrap">
                <table class="table align-middle closing-table" id="closingTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th style="min-width: 220px;">PRODUCT NAME & CODE</th>
                            <th class="text-center" style="width: 75px;">PPB</th>
                            <th class="text-center" style="width: 170px; background-color: #1e293b !important; color:#ffffff !important;">SYSTEM STOCK (BOOK)</th>
                            <th class="text-center" style="width: 210px; background-color: #1e3a8a !important; color:#ffffff !important;">PHYSICAL STOCK (GODOWN)</th>
                            <th class="text-center" style="width: 170px; background-color: #1e293b !important; color:#ffffff !important;">VARIANCE (DIFFERENCE)</th>
                            <th class="text-center" style="width: 95px;">STATUS</th>
                            <th class="text-center" style="width: 80px;" title="Tick to apply adjustment">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <span style="font-size: 11px; letter-spacing: 0.5px;">ADJUST?</span>
                                    <input type="checkbox" id="checkAdjustAll" class="check-adjust mt-1" title="Select / Deselect All">
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="closingTableBody">
                        {{-- Rendered via Javascript --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ADJUSTMENT SUMMARY BOX --}}
    <div class="card adjustment-box mb-3" id="adjustmentSummaryCard">
        <div class="card-header py-2.5 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark rounded-circle p-1.5">
                    <i class="fas fa-sliders-h fa-sm"></i>
                </span>
                <span class="fw-bold fs-6" style="color: #ffffff;">Update Stock Adjustment & Reconciliation Settings</span>
            </div>
            <div id="discrepancyAlertBadge">
                <span class="badge bg-danger px-2.5 py-1 text-white fw-bold" style="font-size: 11px;">
                    <i class="fas fa-exclamation-circle me-1"></i>Discrepancies Found
                </span>
            </div>
        </div>

        <div class="card-body p-3">
            
            {{-- Quick Summary Message --}}
            <div class="alert alert-light border d-flex align-items-center justify-content-between mb-3 py-2 px-3" style="border-radius: 8px; border: 1.5px solid #cbd5e1 !important; background: #f8fafc;">
                <div class="small">
                    <i class="fas fa-info-circle text-primary me-1.5"></i>
                    <span id="discrepancySummaryText" class="fw-semibold text-dark">
                        System Book Stock and Physical Count comparison ready.
                    </span>
                </div>
                <div class="small text-muted font-monospace fw-bold" id="summaryNetStats">
                    Shortage: 0 Pcs | Surplus: 0 Pcs
                </div>
            </div>

            <div class="row g-3">
                {{-- Adjustment Action Mode --}}
                <div class="col-md-5 border-end pe-md-3">
                    <label class="form-label fw-bold text-dark small mb-2">
                        <i class="fas fa-check-double text-primary me-1"></i>Action Mode (Adjustment Method):
                    </label>

                    <div class="form-check p-2.5 rounded mb-2 adj-radio-card {{ $closing->adjustment_mode === 'equal' ? 'active-mode' : '' }}" id="cardModeEqual">
                        <input class="form-check-input ms-1 me-2" type="radio" name="adjustment_mode" id="modeEqual" value="equal" {{ $closing->adjustment_mode === 'equal' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-primary" for="modeEqual" style="font-size: .84rem; cursor: pointer;">
                            <i class="fas fa-equals me-1"></i> Sync System to Physical (Set System = Physical)
                        </label>
                        <div class="text-muted small ps-4 mt-0.5" style="font-size: 11.5px;">
                            Automatically adjusts warehouse system stock to match your edited physical audit count.
                        </div>
                    </div>

                    <div class="form-check p-2.5 rounded mb-2 adj-radio-card {{ $closing->adjustment_mode === 'selective' ? 'active-mode' : '' }}" id="cardModeSelective">
                        <input class="form-check-input ms-1 me-2" type="radio" name="adjustment_mode" id="modeSelective" value="selective" {{ $closing->adjustment_mode === 'selective' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark" for="modeSelective" style="font-size: .84rem; cursor: pointer;">
                            <i class="fas fa-list-check me-1 text-secondary"></i> Selective Adjustment (Adjust Checked Items Only)
                        </label>
                        <div class="text-muted small ps-4 mt-0.5" style="font-size: 11.5px;">
                            Only updates stock for items that have the [Adjust?] checkbox checked in the grid.
                        </div>
                    </div>

                    <div class="form-check p-2.5 rounded adj-radio-card {{ $closing->adjustment_mode === 'none' ? 'active-mode' : '' }}" id="cardModeNone">
                        <input class="form-check-input ms-1 me-2" type="radio" name="adjustment_mode" id="modeNone" value="none" {{ $closing->adjustment_mode === 'none' ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-secondary" for="modeNone" style="font-size: .84rem; cursor: pointer;">
                            <i class="fas fa-eye me-1"></i> No Stock Adjustment (Save Audit Report Only)
                        </label>
                        <div class="text-muted small ps-4 mt-0.5" style="font-size: 11.5px;">
                            Saves edited physical counts and variance history without altering warehouse stock balances.
                        </div>
                    </div>
                </div>

                {{-- Reason & Remarks --}}
                <div class="col-md-7 ps-md-3">
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="fas fa-question-circle text-primary me-1"></i>Variance Reason / Category: <span class="text-danger">*</span>
                            </label>
                            <select id="reasonTypeSelect" class="form-select form-select-sm fw-semibold select2" style="border: 1.5px solid #64748b;">
                                @php
                                    $reasons = [
                                        'Physical Count Correction' => 'Physical Count Correction (Count Reconciliation)',
                                        'Damage / Leakage' => 'Damage / Leakage',
                                        'Expired Stock' => 'Expired Stock',
                                        'Transit Loss / Shortage' => 'Transit Loss / Shortage',
                                        'Theft / Unaccounted Shrinkage' => 'Theft / Unaccounted Shrinkage',
                                        'Unrecorded Sample / Promo' => 'Unrecorded Sample / Promo',
                                        'Other' => 'Other / Custom Reason'
                                    ];
                                @endphp
                                @foreach($reasons as $val => $lbl)
                                    <option value="{{ $val }}" {{ ($closing->reason_type == $val) ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-2">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="fas fa-comment-alt text-secondary me-1"></i>Detailed Remarks / Explanation:
                            </label>
                            <textarea id="remarksText" class="form-control form-control-sm" rows="3" placeholder="Enter detailed audit notes, investigation details, or authorized person remarks..." style="font-size: .82rem; border-radius: 6px; border: 1.5px solid #64748b;">{{ $closing->remarks }}</textarea>
                        </div>
                    </div>

                    {{-- Action Trigger Buttons --}}
                    <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top mt-3" style="border-top: 1.5px solid #e2e8f0 !important;">
                        <a href="{{ route('stock-closing.show', $closing->id) }}" class="btn btn-light border btn-sm px-3.5 py-2 fw-semibold text-secondary" style="border-radius: 8px; border: 1.5px solid #cbd5e1 !important; font-size: .82rem;">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn btn-warning btn-sm px-4 py-2 fw-bold text-dark d-inline-flex align-items-center shadow-sm" id="btnUpdateClosing" style="border-radius: 8px; font-size: .86rem;">
                            <i class="fas fa-sync-alt me-1.5"></i> Update & Re-apply Monthly Closing
                        </button>
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
    // Initialize select2
    if ($('.select2').length > 0) {
        $('.select2').select2({ width: '100%' });
    }

    // Radio mode card active state toggle
    $('input[name="adjustment_mode"]').on('change', function() {
        $('.adj-radio-card').removeClass('active-mode');
        $(this).closest('.adj-radio-card').addClass('active-mode');
    });

    // Pre-populate items from server closing record
    let allItems = {!! json_encode($itemsData) !!};

    let showFarqOnly = false;

    // Render table and summary on load
    filterAndRenderTable();
    recalculateSummary();

    // Category Filter Event
    $('#categoryFilter').on('change', function() {
        filterAndRenderTable();
    });

    // Search Input Event
    $('#searchInput').on('input', function() {
        filterAndRenderTable();
    });

    // Toggle Discrepancies Only Button
    $('#btnFilterDiscrepancies').on('click', function() {
        showFarqOnly = !showFarqOnly;
        if (showFarqOnly) {
            $(this).removeClass('btn-outline-danger').addClass('btn-danger text-white');
            $(this).html('<i class="fas fa-check me-1"></i> Showing Discrepancies Only');
        } else {
            $(this).removeClass('btn-danger text-white').addClass('btn-outline-danger');
            $(this).html('<i class="fas fa-filter me-1"></i> Show Discrepancies Only');
        }
        filterAndRenderTable();
    });

    // Fill All with System Stock Button
    $('#btnFillAll').on('click', function() {
        if (!confirm('Are you sure you want to reset all physical counts to match system book stock?')) return;
        allItems.forEach(item => {
            item.physical_cartons = item.system_cartons;
            item.physical_pieces  = item.system_pieces;
            item.physical_total_pieces = item.system_total_pieces;
            item.variance_pieces = 0;
            item.status = 'matched';
            item.is_adjusted = false;
        });
        filterAndRenderTable();
        recalculateSummary();
    });

    // Clear All Physical Inputs
    $('#btnClearAll').on('click', function() {
        if (!confirm('Are you sure you want to clear all physical count inputs?')) return;
        allItems.forEach(item => {
            item.physical_cartons = 0;
            item.physical_pieces  = 0;
            item.physical_total_pieces = 0;
            item.variance_pieces = 0 - item.system_total_pieces;
            item.status = (item.variance_pieces == 0) ? 'matched' : (item.variance_pieces < 0 ? 'shortage' : 'surplus');
            item.is_adjusted = (item.variance_pieces != 0);
        });
        filterAndRenderTable();
        recalculateSummary();
    });

    // Filter and Render Table
    function filterAndRenderTable() {
        let catId = $('#categoryFilter').val();
        let query = ($('#searchInput').val() || '').toLowerCase().trim();

        let filtered = allItems.filter(item => {
            if (catId && catId !== 'all') {
                if (item.category_id && item.category_id != catId) return false;
            }
            if (query !== '') {
                let matchName = (item.item_name || '').toLowerCase().includes(query);
                let matchCode = (item.item_code || '').toLowerCase().includes(query);
                let matchVar  = (item.variant_name || '').toLowerCase().includes(query);
                if (!matchName && !matchCode && !matchVar) return false;
            }
            if (showFarqOnly && item.status === 'matched') {
                return false;
            }
            return true;
        });

        $('#tableCountBadge').text(filtered.length + ' / ' + allItems.length + ' Items');

        if (filtered.length === 0) {
            $('#closingTableBody').html('<tr><td colspan="8" class="text-center py-4 text-muted small">No items match the current filters.</td></tr>');
            return;
        }

        let html = '';
        filtered.forEach((item, idx) => {
            let statusBadge = '';
            let varianceDisplay = '';

            let vAbs = Math.abs(item.variance_pieces);
            let ppb = item.pieces_per_box || 1;
            let vCtns = Math.floor(vAbs / ppb);
            let vPcs  = vAbs - (vCtns * ppb);
            let vSign = item.variance_pieces < 0 ? '-' : (item.variance_pieces > 0 ? '+' : '');

            if (item.status === 'matched') {
                statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-check me-0.5"></i>Matched</span>';
                varianceDisplay = '<span class="text-success fw-bold">0 Pcs</span>';
            } else if (item.status === 'shortage') {
                statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-arrow-down me-0.5"></i>Shortage</span>';
                varianceDisplay = `<span class="text-danger fw-bold">${vSign}${vCtns} Ctn ${vPcs > 0 ? vPcs + ' Pcs' : ''} (${vSign}${vAbs} pcs)</span>`;
            } else if (item.status === 'surplus') {
                statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-arrow-up me-0.5"></i>Surplus</span>';
                varianceDisplay = `<span class="text-primary fw-bold">${vSign}${vCtns} Ctn ${vPcs > 0 ? vPcs + ' Pcs' : ''} (${vSign}${vAbs} pcs)</span>`;
            }

            let isChecked = item.is_adjusted ? 'checked' : '';

            html += `
                <tr data-uid="${item.uid}" data-product-id="${item.product_id}">
                    <td class="text-center text-muted fw-bold" style="font-size:11px;">${idx + 1}</td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size:12.5px;">${item.item_name}</div>
                        <div class="d-flex align-items-center flex-wrap gap-1 mt-0.5">
                            <span class="badge bg-light text-primary border font-monospace" style="font-size:10px;">${item.item_code}</span>
                            ${item.variant_key ? `<span class="badge border" style="font-size:10px; background:#e0e7ff; color:#3730a3; border-color:#c7d2fe !important;"><i class="fas fa-tag me-0.5"></i>Variant</span>` : ''}
                            <span class="badge bg-light text-secondary border" style="font-size:10px;">${item.category_name}</span>
                            <span class="badge bg-light text-muted border" style="font-size:10px;">Unit: ${item.unit_name}</span>
                        </div>
                    </td>
                    <td class="text-center fw-semibold text-secondary">${ppb}</td>
                    
                    {{-- System Stock Column --}}
                    <td class="text-center bg-light">
                        <div class="fw-bold text-dark font-monospace" style="font-size:12px;">${item.system_formatted}</div>
                        <div class="text-muted" style="font-size:10px;">Total: ${item.system_total_pieces} Pcs</div>
                    </td>

                    {{-- Physical Input Column (Dual inputs: Cartons + Pcs) --}}
                    <td class="p-1.5" style="background: #f0f7ff; border-left: 2px solid #3b82f6 !important; border-right: 2px solid #3b82f6 !important;">
                        <div class="d-flex align-items-center gap-1.5">
                            <div class="input-group input-group-sm flex-fill">
                                <span class="input-group-text px-1.5 fw-bold" style="font-size:11px; background:#e2e8f0; color:#0f172a; border: 1.5px solid #64748b; border-right:none;">Ctn</span>
                                <input type="number" min="0" step="1" class="form-control closing-input input-cartons" value="${item.physical_cartons}">
                            </div>
                            <div class="input-group input-group-sm flex-fill">
                                <span class="input-group-text px-1.5 fw-bold" style="font-size:11px; background:#e2e8f0; color:#0f172a; border: 1.5px solid #64748b; border-right:none;">Pcs</span>
                                <input type="number" min="0" step="1" class="form-control closing-input input-pieces" value="${item.physical_pieces}">
                            </div>
                        </div>
                        <div class="text-center mt-1" style="font-size:11px;">
                            Total: <strong class="text-primary font-monospace physical-total-display" style="font-size: 13px;">${item.physical_total_pieces}</strong> Pcs
                        </div>
                    </td>

                    {{-- Variance Column --}}
                    <td class="text-center variance-col">
                        ${varianceDisplay}
                    </td>

                    {{-- Status Badge --}}
                    <td class="text-center status-col">
                        ${statusBadge}
                    </td>

                    {{-- Apply Adjust Checkbox --}}
                    <td class="text-center align-middle" style="position: static !important;">
                        <input type="checkbox" class="check-adjust" ${isChecked}>
                    </td>
                </tr>
            `;
        });

        $('#closingTableBody').html(html);

        // Update header check-all checkbox state
        let allChecked = filtered.length > 0 && filtered.every(item => item.is_adjusted);
        $('#checkAdjustAll').prop('checked', allChecked);
    }

    // Input change handler (Cartons / Pieces)
    $(document).on('input change', '.input-cartons, .input-pieces', function() {
        let tr = $(this).closest('tr');
        let uid = tr.data('uid');
        let item = allItems.find(p => p.uid === uid);
        if (!item) return;

        let ppb = item.pieces_per_box || 1;
        let ctn = parseFloat(tr.find('.input-cartons').val()) || 0;
        let pcs = parseFloat(tr.find('.input-pieces').val()) || 0;
        if (ctn < 0) { ctn = 0; tr.find('.input-cartons').val(0); }
        if (pcs < 0) { pcs = 0; tr.find('.input-pieces').val(0); }

        let physicalTotal = (ctn * ppb) + pcs;
        item.physical_cartons = ctn;
        item.physical_pieces = pcs;
        item.physical_total_pieces = physicalTotal;

        let diff = physicalTotal - item.system_total_pieces;
        item.variance_pieces = diff;

        if (diff === 0) {
            item.status = 'matched';
            item.is_adjusted = false;
        } else if (diff < 0) {
            item.status = 'shortage';
            item.is_adjusted = true;
        } else {
            item.status = 'surplus';
            item.is_adjusted = true;
        }

        // Live update row UI
        tr.find('.physical-total-display').text(physicalTotal);
        tr.find('.check-adjust').prop('checked', item.is_adjusted);

        let vAbs = Math.abs(diff);
        let vCtns = Math.floor(vAbs / ppb);
        let vPcs  = vAbs - (vCtns * ppb);
        let vSign = diff < 0 ? '-' : (diff > 0 ? '+' : '');

        let statusBadge = '';
        let varianceDisplay = '';
        if (item.status === 'matched') {
            statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-check me-0.5"></i>Matched</span>';
            varianceDisplay = '<span class="text-success fw-bold">0 Pcs</span>';
        } else if (item.status === 'shortage') {
            statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-arrow-down me-0.5"></i>Shortage</span>';
            varianceDisplay = `<span class="text-danger fw-bold">${vSign}${vCtns} Ctn ${vPcs > 0 ? vPcs + ' Pcs' : ''} (${vSign}${vAbs} pcs)</span>`;
        } else {
            statusBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle py-1 px-2" style="font-size:10.5px;"><i class="fas fa-arrow-up me-0.5"></i>Surplus</span>';
            varianceDisplay = `<span class="text-primary fw-bold">${vSign}${vCtns} Ctn ${vPcs > 0 ? vPcs + ' Pcs' : ''} (${vSign}${vAbs} pcs)</span>`;
        }

        tr.find('.variance-col').html(varianceDisplay);
        tr.find('.status-col').html(statusBadge);

        recalculateSummary();
    });

    // Checkbox toggle handler
    $(document).on('change', '.check-adjust:not(#checkAdjustAll)', function() {
        let tr = $(this).closest('tr');
        let uid = tr.data('uid');
        let item = allItems.find(p => p.uid === uid);
        if (item) {
            item.is_adjusted = $(this).is(':checked');
        }
        let allChecked = allItems.length > 0 && allItems.every(p => p.is_adjusted);
        $('#checkAdjustAll').prop('checked', allChecked);
    });

    // Select / Deselect All Checkboxes
    $(document).on('change', '#checkAdjustAll', function() {
        let isChecked = $(this).is(':checked');
        allItems.forEach(item => {
            item.is_adjusted = isChecked;
        });
        $('.check-adjust').prop('checked', isChecked);
    });

    // Prevent mouse wheel from incrementing number inputs and blocking container scroll
    $(document).on('wheel', 'input[type=number]', function(e) {
        $(this).blur();
    });

    // Recalculate summary pills & Adjustment Box stats
    function recalculateSummary() {
        let totalCount = allItems.length;
        let matched = 0;
        let discrepancies = 0;
        let shortage = 0;
        let surplus = 0;

        allItems.forEach(item => {
            if (item.status === 'matched') {
                matched++;
            } else if (item.status === 'shortage') {
                discrepancies++;
                shortage += Math.abs(item.variance_pieces);
            } else if (item.status === 'surplus') {
                discrepancies++;
                surplus += Math.abs(item.variance_pieces);
            }
        });

        $('#pillTotalItems').text(totalCount);
        $('#pillMatchedItems').text(matched);
        $('#pillDiscrepancies').text(discrepancies);
        $('#pillTotalShortage').text('-' + shortage + ' Pcs');
        $('#pillTotalSurplus').text('+' + surplus + ' Pcs');

        if (discrepancies > 0) {
            $('#discrepancyAlertBadge').show();
            $('#discrepancySummaryText').html(`
                Found <strong>${discrepancies}</strong> item(s) with stock discrepancies out of <strong>${totalCount}</strong> total items.
            `);
            $('#summaryNetStats').html(`Shortage: <strong class="text-danger">-${shortage} Pcs</strong> | Surplus: <strong class="text-primary">+${surplus} Pcs</strong>`);
        } else {
            $('#discrepancyAlertBadge').hide();
            $('#discrepancySummaryText').html('All products match system book stock perfectly (0 Discrepancy).');
            $('#summaryNetStats').html('Stock 100% Reconciled');
        }
    }

    // Update and Re-apply Monthly Closing
    $('#btnUpdateClosing').on('click', function() {
        let btn = $(this);
        let closingDate = $('#closingDateInput').val();
        let adjMode = $('input[name="adjustment_mode"]:checked').val();
        let reasonType = $('#reasonTypeSelect').val();
        let remarks = $('#remarksText').val();

        if (!closingDate) {
            alert('Please select audit date');
            return;
        }

        let discrepancies = allItems.filter(p => p.status !== 'matched').length;
        let confirmMsg = `Confirm updating Monthly Stock Closing ({{ $closing->closing_code }})?`;
        if (adjMode === 'equal' && discrepancies > 0) {
            confirmMsg += `\n\nNote: ${discrepancies} discrepant products will be re-adjusted in system stock to match your new physical counts!`;
        }

        if (!confirm(confirmMsg)) return;

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1.5"></i> Updating Closing...');

        let payload = {
            _token: "{{ csrf_token() }}",
            _method: "PUT",
            closing_date: closingDate,
            adjustment_mode: adjMode,
            reason_type: reasonType,
            remarks: remarks,
            items: allItems.map(p => ({
                product_id: p.product_id,
                variant_key: p.variant_key,
                variant_name: p.variant_name,
                pieces_per_box: p.pieces_per_box,
                physical_cartons: p.physical_cartons,
                physical_pieces: p.physical_pieces,
                is_adjusted: p.is_adjusted ? 1 : 0
            }))
        };

        $.ajax({
            url: "{{ route('stock-closing.update', $closing->id) }}",
            type: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(res) {
                if (res.success) {
                    alert(res.message);
                    window.location.href = res.redirect_url;
                } else {
                    alert(res.message || 'Error occurred while updating closing');
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1.5"></i> Update & Re-apply Monthly Closing');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-sync-alt me-1.5"></i> Update & Re-apply Monthly Closing');
                let err = xhr.responseJSON ? xhr.responseJSON.message : 'Server error occurred.';
                alert('Error: ' + err);
            }
        });
    });

});
</script>
@endsection
