@extends('admin_panel.layout.app')

@section('content')
<style>
    .bulk-kpi-strip {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .bulk-kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px;
        flex: 1 1 0;
        min-width: 150px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        position: relative;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .bulk-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    }
    .bulk-kpi-card .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .bulk-kpi-card .kpi-value {
        font-size: 20px;
        font-weight: 800;
        line-height: 1.2;
    }
    .bulk-kpi-card .kpi-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 3px;
    }
    .bulk-kpi-card.kpi-count { border-top: 3px solid #2563eb; }
    .bulk-kpi-card.kpi-orig  { border-top: 3px solid #64748b; }
    .bulk-kpi-card.kpi-disc  { border-top: 3px solid #dc2626; background: #fff5f5; }
    .bulk-kpi-card.kpi-new   { border-top: 3px solid #10b981; background: #f0fdf4; }

    /* Interactive Multi-Tier Badges */
    .tier-badge-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        font-size: 11.5px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        transition: all 0.15s ease;
        margin: 2px 3px 2px 0;
        user-select: none;
    }
    .tier-badge-label:hover {
        background: #e2e8f0;
    }
    .tier-badge-label.checked-doc {
        background: #2563eb !important;
        border-color: #1d4ed8 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(37,99,235,0.3);
    }
    .tier-badge-label.checked-med {
        background: #059669 !important;
        border-color: #047857 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(5,150,105,0.3);
    }
    .tier-badge-label.checked-dist {
        background: #7c3aed !important;
        border-color: #6d28d9 !important;
        color: #ffffff !important;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(124,58,237,0.3);
    }
    .tier-badge-label.checked-custom {
        background: #0284c7 !important;
        border-color: #0369a1 !important;
        color: #ffffff !important;
        font-weight: 700;
    }
    .tier-badge-label input[type="checkbox"] {
        accent-color: currentColor;
        cursor: pointer;
        margin: 0;
    }

    .table-bulk th {
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #f1f5f9;
        color: #334155;
        vertical-align: middle;
        border-top: none;
        padding: 10px 8px;
    }
    .table-bulk td {
        vertical-align: middle;
        font-size: 12.5px;
        padding: 9px 8px;
    }
    .item-dropdown-container {
        background: #ffffff;
        border-radius: 8px;
        padding: 12px 14px;
        margin: 6px 12px 14px 12px;
        border: 2px solid #3b82f6;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
    }
    .badge-no-disc {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .badge-has-disc {
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .btn-toggle-dropdown {
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 5px;
    }
    .change-pill {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        white-space: nowrap;
    }
    .combined-disc-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
        background: #fef08a;
        color: #854d0e;
        border: 1px solid #facc15;
        display: inline-block;
        margin-top: 3px;
    }
</style>

<div class="main-content-inner">
    <div class="container-fluid pt-3">

        <!-- Header & Breadcrumbs -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-percent text-primary mr-2"></i>Bulk Discount Manager & Live Multi-Tier Preview
                </h4>
                <p class="text-muted small mb-0">
                    Apply single or <strong>combined multi-discounts</strong> (Medical + Distribution + Doctor) in bulk with live product pricing breakdown.
                </p>
            </div>
            <div>
                <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Back to Sales
                </a>
            </div>
        </div>

        <!-- Inventory Stock Safety Banner -->
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3 py-2 px-3" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important; border-radius: 6px;">
            <i class="fa fa-shield-alt text-primary fa-lg mr-3"></i>
            <div class="small text-dark">
                <strong>Inventory Stock Safe Guarantee:</strong> Discounts update only invoice financials, customer ledgers, and journal vouchers. 
                <span class="text-primary font-weight-bold">Warehouse stock quantities, carton counts, loose pieces, and return quantities remain 100% untouched.</span>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('sale.bulk_discount') }}" id="filterForm">
                    <div class="form-row align-items-end">
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Invoice #</label>
                            <input type="text" name="invoice_no" value="{{ request('invoice_no') }}" class="form-control form-control-sm" placeholder="e.g. INV-0010">
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Customer</label>
                            <select name="customer_id" class="form-control form-control-sm select2">
                                <option value="">-- All Customers --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_name }} ({{ $c->customer_type ?? 'Regular' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Customer Type</label>
                            <select name="customer_type" class="form-control form-control-sm">
                                <option value="">-- All Types --</option>
                                @foreach($customerTypes as $ct)
                                    <option value="{{ $ct->name }}" {{ request('customer_type') == $ct->name ? 'selected' : '' }}>
                                        {{ $ct->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Discount Filter</label>
                            <select name="discount_filter" class="form-control form-control-sm">
                                <option value="all" {{ $discountFilter == 'all' ? 'selected' : '' }}>All Sales (Every Invoice)</option>
                                <option value="no_discount" {{ $discountFilter == 'no_discount' ? 'selected' : '' }}>No Discount / 0% Only</option>
                                <option value="has_discount" {{ $discountFilter == 'has_discount' ? 'selected' : '' }}>With Discount (>0%)</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Sale Status</label>
                            <select name="sale_status" class="form-control form-control-sm">
                                <option value="">-- All Statuses --</option>
                                <option value="posted" {{ request('sale_status') == 'posted' ? 'selected' : '' }}>Posted</option>
                                <option value="booked" {{ request('sale_status') == 'booked' ? 'selected' : '' }}>Booked</option>
                                <option value="returned" {{ request('sale_status') == 'returned' ? 'selected' : '' }}>Returned</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-12 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary btn-sm px-3 mr-2 shadow-sm font-weight-bold">
                                <i class="fa fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('sale.bulk_discount') }}" class="btn btn-light btn-sm px-2 text-muted border">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Live KPI Summary Strip -->
        <div class="bulk-kpi-strip">
            <div class="bulk-kpi-card kpi-count">
                <div class="kpi-label"><i class="fa fa-check-square mr-1 text-primary"></i> Selected Sales</div>
                <div class="kpi-value text-primary" id="kpiSelectedCount">0</div>
                <div class="kpi-sub">Out of {{ $sales->total() }} matching sales</div>
            </div>
            <div class="bulk-kpi-card kpi-orig">
                <div class="kpi-label"><i class="fa fa-file-invoice mr-1 text-secondary"></i> Original Net Total</div>
                <div class="kpi-value text-dark" id="kpiOriginalTotal">Rs. 0.00</div>
                <div class="kpi-sub">Gross bill before new discount</div>
            </div>
            <div class="bulk-kpi-card kpi-disc">
                <div class="kpi-label"><i class="fa fa-tag mr-1 text-danger"></i> Projected Discount</div>
                <div class="kpi-value text-danger" id="kpiDiscountTotal">Rs. 0.00</div>
                <div class="kpi-sub" id="kpiDiscountPercent">0.0% average deduction</div>
            </div>
            <div class="bulk-kpi-card kpi-new">
                <div class="kpi-label"><i class="fa fa-calculator mr-1 text-success"></i> Projected New Net</div>
                <div class="kpi-value text-success" id="kpiNewNetTotal">Rs. 0.00</div>
                <div class="kpi-sub" id="kpiTotalSavingsSub">Deduction: Rs. 0.00</div>
            </div>
        </div>

        <!-- Main Form & Table -->
        <form id="bulkDiscountForm">
            @csrf

            <!-- Batch Action Control Bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center bg-white rounded">
                    <div class="d-flex align-items-center flex-wrap my-1">
                        <!-- Select All Checkbox -->
                        <div class="custom-control custom-checkbox mr-3 ml-2">
                            <input type="checkbox" class="custom-control-input" id="checkAllMaster">
                            <label class="custom-control-label font-weight-bold text-dark small" for="checkAllMaster">
                                Select All (<span id="totalVisibleCount">{{ $sales->count() }}</span>)
                            </label>
                        </div>

                        <!-- Expand / Collapse All Products Button -->
                        <button type="button" class="btn btn-outline-info btn-sm mr-2 my-1" id="btnToggleAllItems">
                            <i class="fa fa-expand-arrows-alt mr-1"></i> <span id="lblToggleAll">Expand All Products</span>
                        </button>

                        <!-- Multi-Discount Presets -->
                        <div class="btn-group btn-group-sm mr-2 my-1" role="group">
                            <button type="button" class="btn btn-outline-primary" id="btnPresetDoctor" title="Select Doctor tier only">
                                <i class="fa fa-user-md mr-1"></i> Doctor Tier
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnPresetMedical" title="Select Medical tier only">
                                <i class="fa fa-clinic-medical mr-1"></i> Medical Tier
                            </button>
                            <button type="button" class="btn btn-outline-purple" style="border-color:#7c3aed; color:#7c3aed;" id="btnPresetDist" title="Select Distribution tier only">
                                <i class="fa fa-truck mr-1"></i> Distribution Tier
                            </button>
                            <button type="button" class="btn btn-warning text-dark font-weight-bold" id="btnPresetCombineMedDist" title="Combine Medical + Distribution discounts">
                                <i class="fa fa-layer-group mr-1"></i> Med + Dist (Multi)
                            </button>
                            <button type="button" class="btn btn-info text-white font-weight-bold" id="btnPresetCombineDocDist" title="Combine Doctor + Distribution discounts">
                                <i class="fa fa-layer-group mr-1"></i> Doc + Dist (Multi)
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnApplyCustomPrompt" title="Apply a uniform custom % to all selected">
                                <i class="fa fa-cog mr-1"></i> Custom %
                            </button>
                        </div>
                    </div>

                    <!-- Giant Apply Button -->
                    <div class="my-1">
                        <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold shadow-sm" id="btnSubmitBulk" disabled>
                            <i class="fa fa-check-circle mr-1"></i> Apply Discounts to Selected (<span id="btnSelectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bulk mb-0" id="bulkTable">
                            <thead>
                                <tr>
                                    <th width="35" class="text-center">#</th>
                                    <th width="140">Invoice & Date</th>
                                    <th width="160">Customer</th>
                                    <th width="280">Multi-Discount Selection (Select 1 or More)</th>
                                    <th width="110" class="text-right">Current Net</th>
                                    <th width="120" class="text-right">Projected Disc</th>
                                    <th width="120" class="text-right">New Net</th>
                                    <th width="120" class="text-center">Change</th>
                                    <th width="110" class="text-center">Products</th>
                                    <th width="110" class="text-center">Quick Links</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    @php
                                        $cust = $sale->customer_relation;
                                        $tiers = $cust ? $cust->discount_tiers : ['medical' => 0, 'doctor' => 0, 'distribution' => 0];
                                        
                                        $hasDiscount = $sale->items->sum('discount_amount') > 0 || !empty($sale->discount_tier);
                                        $currentDiscAmount = $sale->items->sum('discount_amount');
                                        
                                        // Calculate total un-discounted gross
                                        $grossTotal = 0;
                                        foreach($sale->items as $it) {
                                            $lineGross = (float)($it->total + $it->discount_amount);
                                            if ($lineGross <= 0) {
                                                $lineGross = (float)$it->qty * (float)$it->price;
                                            }
                                            $grossTotal += $lineGross;
                                        }

                                        $docPct = (float)($tiers['doctor'] ?? 0);
                                        $medPct = (float)($tiers['medical'] ?? 0);
                                        $distPct = (float)($tiers['distribution'] ?? 0);

                                        // Detect if this sale already has existing tiers applied
                                        $existingTier = strtolower((string)$sale->discount_tier);
                                        if (!empty($existingTier)) {
                                            $defaultDoc = str_contains($existingTier, 'doctor') || str_contains($existingTier, 'doc');
                                            $defaultMed = str_contains($existingTier, 'medical') || str_contains($existingTier, 'med');
                                            $defaultDist = str_contains($existingTier, 'distribution') || str_contains($existingTier, 'dist');
                                        } else {
                                            // Default selection: If doctor > 0, check doctor by default, else first positive tier
                                            $defaultDoc = ($docPct > 0);
                                            $defaultMed = (!$defaultDoc && $medPct > 0);
                                            $defaultDist = (!$defaultDoc && !$defaultMed && $distPct > 0);
                                            if (!$defaultDoc && !$defaultMed && !$defaultDist) {
                                                $defaultDoc = true; // Fallback
                                            }
                                        }
                                    @endphp
                                    <tr class="sale-row" 
                                        id="row-{{ $sale->id }}"
                                        data-sale-id="{{ $sale->id }}"
                                        data-gross="{{ $grossTotal }}"
                                        data-old-net="{{ $sale->total_net }}"
                                        data-extra-disc="{{ $sale->total_extradiscount ?? 0 }}"
                                        data-tier-doc="{{ $docPct }}"
                                        data-tier-med="{{ $medPct }}"
                                        data-tier-dist="{{ $distPct }}">
                                        
                                        <!-- Row Checkbox -->
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                       name="selected_sales[]" 
                                                       value="{{ $sale->id }}" 
                                                       class="custom-control-input sale-checkbox" 
                                                       id="chk-{{ $sale->id }}">
                                                <label class="custom-control-label" for="chk-{{ $sale->id }}"></label>
                                            </div>
                                        </td>

                                        <!-- Invoice #, Date, Status -->
                                        <td>
                                            <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="font-weight-bold text-primary" title="Click to view printable invoice">
                                                #{{ $sale->invoice_no }}
                                            </a>
                                            <div class="text-muted small">
                                                {{ $sale->created_at ? $sale->created_at->format('d M, Y') : '-' }}
                                            </div>
                                            <span class="badge {{ $sale->sale_status == 'posted' ? 'badge-success' : 'badge-warning' }} px-1 small" style="font-size: 10px;">
                                                {{ ucfirst($sale->sale_status) }}
                                            </span>
                                        </td>

                                        <!-- Customer Details -->
                                        <td>
                                            <div class="font-weight-bold text-dark text-truncate" style="max-width: 150px;" title="{{ $cust->customer_name ?? 'N/A' }}">
                                                {{ $cust->customer_name ?? 'N/A' }}
                                            </div>
                                            <span class="badge badge-light border small text-muted">
                                                {{ $cust->customer_type ?? 'Regular' }}
                                            </span>
                                        </td>

                                        <!-- Multi-Discount Interactive Checkbox Badges -->
                                        <td>
                                            <div class="d-flex flex-wrap align-items-center">
                                                <!-- Doctor Tier Checkbox Badge -->
                                                <label class="tier-badge-label tier-badge-doc {{ $defaultDoc ? 'checked-doc' : '' }}" title="Doctor Discount Tier: {{ $docPct }}%">
                                                    <input type="checkbox" 
                                                           name="selected_tiers[{{ $sale->id }}][]" 
                                                           value="doctor" 
                                                           class="tier-chk chk-doc" 
                                                           data-sale-id="{{ $sale->id }}" 
                                                           data-pct="{{ $docPct }}" 
                                                           {{ $defaultDoc ? 'checked' : '' }}>
                                                    <span>Doc: {{ $docPct }}%</span>
                                                </label>

                                                <!-- Medical Tier Checkbox Badge -->
                                                <label class="tier-badge-label tier-badge-med {{ $defaultMed ? 'checked-med' : '' }}" title="Medical Store Discount Tier: {{ $medPct }}%">
                                                    <input type="checkbox" 
                                                           name="selected_tiers[{{ $sale->id }}][]" 
                                                           value="medical" 
                                                           class="tier-chk chk-med" 
                                                           data-sale-id="{{ $sale->id }}" 
                                                           data-pct="{{ $medPct }}" 
                                                           {{ $defaultMed ? 'checked' : '' }}>
                                                    <span>Med: {{ $medPct }}%</span>
                                                </label>

                                                <!-- Distribution Tier Checkbox Badge -->
                                                <label class="tier-badge-label tier-badge-dist {{ $defaultDist ? 'checked-dist' : '' }}" title="Distribution Discount Tier: {{ $distPct }}%">
                                                    <input type="checkbox" 
                                                           name="selected_tiers[{{ $sale->id }}][]" 
                                                           value="distribution" 
                                                           class="tier-chk chk-dist" 
                                                           data-sale-id="{{ $sale->id }}" 
                                                           data-pct="{{ $distPct }}" 
                                                           {{ $defaultDist ? 'checked' : '' }}>
                                                    <span>Dist: {{ $distPct }}%</span>
                                                </label>

                                                <!-- Custom % Checkbox & Input -->
                                                <label class="tier-badge-label tier-badge-custom" title="Custom Extra Discount">
                                                    <input type="checkbox" 
                                                           name="selected_tiers[{{ $sale->id }}][]" 
                                                           value="custom" 
                                                           class="tier-chk chk-custom" 
                                                           data-sale-id="{{ $sale->id }}">
                                                    <span>Custom</span>
                                                </label>
                                                <div class="custom-pct-wrap ml-1" id="custom-wrap-{{ $sale->id }}" style="display: none; width: 65px;">
                                                    <input type="number" 
                                                           step="0.1" 
                                                           min="0" 
                                                           max="100" 
                                                           name="custom_percent[{{ $sale->id }}]" 
                                                           class="form-control form-control-sm custom-pct-input text-center p-1" 
                                                           style="height: 24px; font-size: 11px;"
                                                           data-sale-id="{{ $sale->id }}"
                                                           placeholder="%" 
                                                           value="0">
                                                </div>
                                            </div>

                                            <!-- Live Combined Formula Badge -->
                                            <div>
                                                <span class="combined-disc-badge" id="combined-label-{{ $sale->id }}">
                                                    Total: 0.0%
                                                </span>
                                                <input type="hidden" name="effective_percent[{{ $sale->id }}]" id="effective-pct-{{ $sale->id }}" value="0">
                                                <input type="hidden" name="tier_label[{{ $sale->id }}]" id="tier-label-{{ $sale->id }}" value="">
                                            </div>
                                        </td>

                                        <!-- Original Current Net -->
                                        <td class="text-right text-dark font-weight-bold">
                                            Rs. {{ number_format($sale->total_net, 2) }}
                                            @if($hasDiscount)
                                                <div class="badge-has-disc small mt-1">Has Disc</div>
                                            @else
                                                <div class="badge-no-disc small mt-1">0% / None</div>
                                            @endif
                                        </td>

                                        <!-- Projected Discount -->
                                        <td class="text-right text-danger font-weight-bold" id="proj-disc-{{ $sale->id }}">
                                            Rs. 0.00
                                        </td>

                                        <!-- Projected New Net -->
                                        <td class="text-right text-success font-weight-bold" id="proj-net-{{ $sale->id }}">
                                            Rs. {{ number_format($sale->total_net, 2) }}
                                        </td>

                                        <!-- Change Badge -->
                                        <td class="text-center" id="change-cell-{{ $sale->id }}">
                                            <span class="badge badge-light border change-pill text-muted">0.00</span>
                                        </td>

                                        <!-- Prominent View Products Dropdown Button -->
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-info btn-toggle-dropdown toggle-items" 
                                                    data-target="#items-{{ $sale->id }}" 
                                                    title="Click to view/hide product quantities & live prices after discount">
                                                <i class="fa fa-boxes mr-1"></i> <span class="d-none d-lg-inline">Products</span> ({{ $sale->items->count() }}) <i class="fa fa-chevron-down ml-1"></i>
                                            </button>
                                        </td>

                                        <!-- Quick Edit & View Links -->
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('sales.edit', $sale->id) }}" 
                                                   target="_blank" 
                                                   class="btn btn-xs btn-outline-warning py-1 px-2" 
                                                   title="Open Sale in Edit Mode (New Tab)">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <a href="{{ route('sales.invoice', $sale->id) }}" 
                                                   target="_blank" 
                                                   class="btn btn-xs btn-outline-secondary py-1 px-2" 
                                                   title="View Printable Invoice">
                                                    <i class="fa fa-receipt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- DROPDOWN CONTAINER: Product Items Breakdown Right Below the Sale -->
                                    <tr id="items-{{ $sale->id }}" class="items-detail-row" style="display: none;">
                                        <td colspan="10" class="p-0 border-0">
                                            <div class="item-dropdown-container">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                                    <div>
                                                        <strong class="text-primary text-uppercase" style="font-size: 12.5px;">
                                                            <i class="fa fa-list mr-1"></i> Products & Quantity Breakdown for Invoice #{{ $sale->invoice_no }}
                                                        </strong>
                                                        <span class="text-muted small ml-2">({{ $cust->customer_name ?? 'N/A' }})</span>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fa fa-info-circle mr-1"></i> Prices after discount update dynamically as you select discount tiers above.
                                                    </div>
                                                </div>

                                                <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 12px;">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Product Description</th>
                                                            <th class="text-center" width="120">Quantity</th>
                                                            <th class="text-right" width="120">Unit Price</th>
                                                            <th class="text-right" width="130">Gross Total</th>
                                                            <th class="text-center" width="130">Discount %</th>
                                                            <th class="text-right" width="130">Discount Amount</th>
                                                            <th class="text-right" width="150" style="background:#f0fdf4;">Amount After Discount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sale->items as $idx => $item)
                                                            @php
                                                                $itemGross = (float)($item->total + $item->discount_amount);
                                                                if ($itemGross <= 0) {
                                                                    $itemGross = (float)$item->qty * (float)$item->price;
                                                                }

                                                                // Decode variant name / color if stored
                                                                $varName = '';
                                                                if (!empty($item->color)) {
                                                                    $b64 = base64_decode($item->color, true);
                                                                    $vData = ($b64 !== false) ? json_decode($b64, true) : json_decode($item->color, true);
                                                                    if (is_array($vData) && !empty($vData['size']) && $vData['size'] !== '-') {
                                                                        $varName = ' (Size: ' . $vData['size'] . ')';
                                                                    }
                                                                }
                                                            @endphp
                                                            <tr class="item-subrow" 
                                                                data-item-id="{{ $item->id }}" 
                                                                data-sale-id="{{ $sale->id }}" 
                                                                data-gross="{{ $itemGross }}">
                                                                <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                                                <td>
                                                                    <strong>{{ $item->product_name ?? ($item->product->item_name ?? 'Item #' . $item->id) }}</strong>
                                                                    <span class="text-muted small">{{ $varName }}</span>
                                                                </td>
                                                                <td class="text-center font-weight-bold">
                                                                    {{ (float)$item->qty }} {{ $item->size_mode == 'by_cartons' ? 'Cartons' : 'Pcs' }}
                                                                    @if($item->loose_pieces > 0)
                                                                        <div class="small text-muted">+ {{ $item->loose_pieces }} Loose Pcs</div>
                                                                    @endif
                                                                </td>
                                                                <td class="text-right">
                                                                    Rs. {{ number_format($item->price, 2) }}
                                                                </td>
                                                                <td class="text-right font-weight-bold">
                                                                    Rs. {{ number_format($itemGross, 2) }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="input-group input-group-sm m-auto" style="max-width: 95px;">
                                                                        <input type="number" 
                                                                               step="0.1" 
                                                                               min="0" 
                                                                               max="100" 
                                                                               name="item_discount[{{ $sale->id }}][{{ $item->id }}]" 
                                                                               value="{{ (float)$item->discount_percent }}" 
                                                                               class="form-control form-control-sm text-center item-pct-input p-1" 
                                                                               data-sale-id="{{ $sale->id }}"
                                                                               data-item-id="{{ $item->id }}">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text p-1 font-weight-bold" style="font-size:10px;">%</span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-right text-danger font-weight-bold item-disc-val" id="item-disc-val-{{ $item->id }}">
                                                                    Rs. {{ number_format($item->discount_amount, 2) }}
                                                                </td>
                                                                <td class="text-right font-weight-bold text-success item-net-val" id="item-net-val-{{ $item->id }}" style="background:#f0fdf4; font-size: 13px;">
                                                                    Rs. {{ number_format($item->total, 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="fa fa-info-circle fa-2x mb-2 text-secondary d-block"></i>
                                            <h5>No sales found matching your filter criteria.</h5>
                                            <p class="small mb-0">Try changing your filters or selecting "All Sales".</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($sales->hasPages())
                    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} sales
                        </div>
                        <div>
                            {{ $sales->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // Toggle single item dropdown
    $(document).on('click', '.toggle-items', function() {
        var target = $(this).data('target');
        $(target).fadeToggle(180);
        var icon = $(this).find('.fa-chevron-down, .fa-chevron-up');
        if (icon.hasClass('fa-chevron-down')) {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        } else {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        }
    });

    // Expand / Collapse ALL Products toggle
    var allExpanded = false;
    $('#btnToggleAllItems').on('click', function() {
        if (!allExpanded) {
            $('.items-detail-row').fadeIn(180);
            $('.toggle-items').find('.fa-chevron-down').removeClass('fa-chevron-down').addClass('fa-chevron-up');
            $('#lblToggleAll').text('Collapse All Products');
            allExpanded = true;
        } else {
            $('.items-detail-row').fadeOut(180);
            $('.toggle-items').find('.fa-chevron-up').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            $('#lblToggleAll').text('Expand All Products');
            allExpanded = false;
        }
    });

    // Calculate effective multi-discount percentage and label for a sale
    function computeSaleMultiDiscount(saleId) {
        var row = $('#row-' + saleId);
        var totalPct = 0;
        var parts = [];

        // Check doctor
        var docChk = row.find('.chk-doc');
        if (docChk.is(':checked')) {
            var val = parseFloat(docChk.data('pct')) || 0;
            totalPct += val;
            parts.push('Doc ' + val + '%');
            row.find('.tier-badge-doc').addClass('checked-doc');
        } else {
            row.find('.tier-badge-doc').removeClass('checked-doc');
        }

        // Check medical
        var medChk = row.find('.chk-med');
        if (medChk.is(':checked')) {
            var val = parseFloat(medChk.data('pct')) || 0;
            totalPct += val;
            parts.push('Med ' + val + '%');
            row.find('.tier-badge-med').addClass('checked-med');
        } else {
            row.find('.tier-badge-med').removeClass('checked-med');
        }

        // Check distribution
        var distChk = row.find('.chk-dist');
        if (distChk.is(':checked')) {
            var val = parseFloat(distChk.data('pct')) || 0;
            totalPct += val;
            parts.push('Dist ' + val + '%');
            row.find('.tier-badge-dist').addClass('checked-dist');
        } else {
            row.find('.tier-badge-dist').removeClass('checked-dist');
        }

        // Check custom
        var customChk = row.find('.chk-custom');
        var customWrap = $('#custom-wrap-' + saleId);
        if (customChk.is(':checked')) {
            customWrap.show();
            var val = parseFloat(customWrap.find('.custom-pct-input').val()) || 0;
            totalPct += val;
            parts.push('Custom ' + val + '%');
            row.find('.tier-badge-custom').addClass('checked-custom');
        } else {
            customWrap.hide();
            row.find('.tier-badge-custom').removeClass('checked-custom');
        }

        var labelText = parts.length > 0 ? parts.join(' + ') + ' = ' + totalPct.toFixed(1) + '%' : '0.0%';
        $('#combined-label-' + saleId).text('Total: ' + totalPct.toFixed(1) + '%');
        $('#effective-pct-' + saleId).val(totalPct);
        $('#tier-label-' + saleId).val(parts.join(' + '));

        return totalPct;
    }

    // Recalculate single sale row and all its items live
    function recalcSaleRow(saleId) {
        var row = $('#row-' + saleId);
        var totalPct = computeSaleMultiDiscount(saleId);
        var extraDisc = parseFloat(row.data('extra-disc')) || 0;
        var oldNet = parseFloat(row.data('old-net')) || 0;

        var itemRows = $('#items-' + saleId).find('.item-subrow');
        var newTotalBill = 0;
        var totalItemDisc = 0;

        if (itemRows.length > 0) {
            itemRows.each(function() {
                var itemRow = $(this);
                var itemId = itemRow.data('item-id');
                var gross = parseFloat(itemRow.data('gross')) || 0;
                var itemInput = itemRow.find('.item-pct-input');

                // If user hasn't explicitly customized this individual item, sync it with the sale's multi-discount total
                var linePct = parseFloat(itemInput.val());
                if (isNaN(linePct) || itemInput.data('auto-synced') !== false) {
                    linePct = totalPct;
                    itemInput.val(linePct);
                }

                var lineDisc = Math.round((gross * linePct / 100) * 100) / 100;
                var lineNet = Math.max(0, Math.round((gross - lineDisc) * 100) / 100);

                $('#item-disc-val-' + itemId).text('Rs. ' + lineDisc.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#item-net-val-' + itemId).text('Rs. ' + lineNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                totalItemDisc += lineDisc;
                newTotalBill += lineNet;
            });
        } else {
            var gross = parseFloat(row.data('gross')) || 0;
            totalItemDisc = Math.round((gross * totalPct / 100) * 100) / 100;
            newTotalBill = Math.max(0, gross - totalItemDisc);
        }

        var newNet = Math.max(0, Math.round((newTotalBill - extraDisc) * 100) / 100);
        var diff = Math.round((newNet - oldNet) * 100) / 100;

        $('#proj-disc-' + saleId).text('Rs. ' + totalItemDisc.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + totalPct.toFixed(1) + '%)');
        $('#proj-net-' + saleId).text('Rs. ' + newNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // Display Difference / Change pill
        var changeHtml = '';
        if (diff < 0) {
            changeHtml = '<span class="badge badge-danger change-pill"><i class="fa fa-arrow-down mr-1"></i> Rs. ' + Math.abs(diff).toFixed(2) + '</span>';
        } else if (diff > 0) {
            changeHtml = '<span class="badge badge-success change-pill"><i class="fa fa-arrow-up mr-1"></i> +Rs. ' + diff.toFixed(2) + '</span>';
        } else {
            changeHtml = '<span class="badge badge-light border change-pill text-muted">No change</span>';
        }
        $('#change-cell-' + saleId).html(changeHtml);

        row.data('proj-disc', totalItemDisc);
        row.data('proj-net', newNet);
    }

    // Recalculate Master KPI Strip
    function recalcMasterKPI() {
        var selectedCount = 0;
        var totalOrig = 0;
        var totalDisc = 0;
        var totalNewNet = 0;

        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            selectedCount++;
            totalOrig += parseFloat(row.data('old-net')) || 0;
            totalDisc += parseFloat(row.data('proj-disc')) || 0;
            totalNewNet += parseFloat(row.data('proj-net')) || 0;
        });

        $('#kpiSelectedCount').text(selectedCount);
        $('#btnSelectedCount').text(selectedCount);
        $('#kpiOriginalTotal').text('Rs. ' + totalOrig.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#kpiDiscountTotal').text('Rs. ' + totalDisc.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#kpiNewNetTotal').text('Rs. ' + totalNewNet.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        var totalDeduction = totalOrig - totalNewNet;
        $('#kpiTotalSavingsSub').text('Total Deduction: Rs. ' + totalDeduction.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        var avgPct = totalOrig > 0 ? ((totalDisc / totalOrig) * 100).toFixed(1) : '0.0';
        $('#kpiDiscountPercent').text(avgPct + '% average deduction');

        if (selectedCount > 0) {
            $('#btnSubmitBulk').prop('disabled', false);
        } else {
            $('#btnSubmitBulk').prop('disabled', true);
        }
    }

    // Initialize all visible sales on load
    $('.sale-row').each(function() {
        var saleId = $(this).data('sale-id');
        recalcSaleRow(saleId);
    });
    recalcMasterKPI();

    // Event: Multi-Tier Checkbox changed
    $(document).on('change', '.tier-chk', function() {
        var saleId = $(this).data('sale-id');
        // Auto-check this sale row if tier is interacted with
        $('#chk-' + saleId).prop('checked', true).trigger('change');
        // Reset item sync flag so items follow new multi-discount
        $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Custom % input changed
    $(document).on('input change', '.custom-pct-input', function() {
        var saleId = $(this).data('sale-id');
        $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Individual item discount % changed
    $(document).on('input change', '.item-pct-input', function() {
        var saleId = $(this).data('sale-id');
        $(this).data('auto-synced', false); // User manually customized this product
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Sale row checkbox changed
    $(document).on('change', '.sale-checkbox', function() {
        var row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.addClass('table-primary');
        } else {
            row.removeClass('table-primary');
        }
        recalcMasterKPI();
    });

    // Event: Master Check All
    $('#checkAllMaster').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.sale-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Preset: Doctor Only
    $('#btnPresetDoctor').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.chk-doc').prop('checked', true);
            row.find('.chk-med').prop('checked', false);
            row.find('.chk-dist').prop('checked', false);
            row.find('.chk-custom').prop('checked', false);
            $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
            recalcSaleRow(saleId);
        });
        recalcMasterKPI();
    });

    // Preset: Medical Only
    $('#btnPresetMedical').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.chk-doc').prop('checked', false);
            row.find('.chk-med').prop('checked', true);
            row.find('.chk-dist').prop('checked', false);
            row.find('.chk-custom').prop('checked', false);
            $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
            recalcSaleRow(saleId);
        });
        recalcMasterKPI();
    });

    // Preset: Distribution Only
    $('#btnPresetDist').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.chk-doc').prop('checked', false);
            row.find('.chk-med').prop('checked', false);
            row.find('.chk-dist').prop('checked', true);
            row.find('.chk-custom').prop('checked', false);
            $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
            recalcSaleRow(saleId);
        });
        recalcMasterKPI();
    });

    // Multi-Preset: Medical + Distribution (User specifically requested!)
    $('#btnPresetCombineMedDist').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.chk-doc').prop('checked', false);
            row.find('.chk-med').prop('checked', true);
            row.find('.chk-dist').prop('checked', true);
            row.find('.chk-custom').prop('checked', false);
            $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
            recalcSaleRow(saleId);
        });
        recalcMasterKPI();
    });

    // Multi-Preset: Doctor + Distribution
    $('#btnPresetCombineDocDist').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.chk-doc').prop('checked', true);
            row.find('.chk-med').prop('checked', false);
            row.find('.chk-dist').prop('checked', true);
            row.find('.chk-custom').prop('checked', false);
            $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
            recalcSaleRow(saleId);
        });
        recalcMasterKPI();
    });

    // Preset: Custom % Prompt
    $('#btnApplyCustomPrompt').on('click', function() {
        var checkedBoxes = $('.sale-checkbox:checked');
        if (checkedBoxes.length === 0) {
            Swal.fire('No Selection', 'Please select at least one sale first.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Set Uniform Custom %',
            text: 'Enter custom discount percentage for ' + checkedBoxes.length + ' selected sales:',
            input: 'number',
            inputAttributes: {
                min: 0,
                max: 100,
                step: 0.1
            },
            inputValue: 10,
            showCancelButton: true,
            confirmButtonText: 'Apply Custom %',
            confirmButtonColor: '#2563eb'
        }).then(function(result) {
            if (result.isConfirmed && result.value !== '') {
                var customVal = parseFloat(result.value) || 0;
                checkedBoxes.each(function() {
                    var saleId = $(this).val();
                    var row = $('#row-' + saleId);
                    row.find('.chk-doc').prop('checked', false);
                    row.find('.chk-med').prop('checked', false);
                    row.find('.chk-dist').prop('checked', false);
                    row.find('.chk-custom').prop('checked', true);
                    row.find('.custom-pct-input').val(customVal);
                    $('#custom-wrap-' + saleId).show();
                    $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
                    recalcSaleRow(saleId);
                });
                recalcMasterKPI();
            }
        });
    });

    // Submit Bulk Discounts with SweetAlert Confirmation & Change Summary
    $('#btnSubmitBulk').on('click', function() {
        var selectedBoxes = $('.sale-checkbox:checked');
        var count = selectedBoxes.length;

        if (count === 0) {
            Swal.fire('Error', 'Please select at least one sale to update.', 'warning');
            return;
        }

        var origTotal = $('#kpiOriginalTotal').text();
        var totalDisc = $('#kpiDiscountTotal').text();
        var newNet = $('#kpiNewNetTotal').text();

        Swal.fire({
            title: 'Confirm Bulk Discount Update',
            html: `
                <div class="text-left py-2">
                    <p class="mb-2">Are you sure you want to apply discounts to <strong>${count} selected sale(s)</strong>?</p>
                    <ul class="list-group list-group-flush small mb-3 border">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Selected Sales:</span>
                            <strong>${count} Invoices</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Original Net Total:</span>
                            <strong>${origTotal}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between text-danger">
                            <span>Total Discount Applied:</span>
                            <strong>-${totalDisc}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Projected New Net Total:</span>
                            <strong>${newNet}</strong>
                        </li>
                    </ul>
                    <div class="alert alert-success p-2 small mb-0">
                        <i class="fa fa-shield-alt mr-1"></i> <strong>Safe Operation:</strong> Inventory quantities, carton/loose counts, and returns remain 100% untouched.
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-check mr-1"></i> Yes, Apply Now',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#64748b'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Applying Discounts...',
                    html: 'Updating invoice financials, journal vouchers, and customer ledgers safely. Please wait.',
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formData = $('#bulkDiscountForm').serialize();

                $.ajax({
                    url: "{{ route('sale.bulk_discount.apply') }}",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(resp) {
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Discounts Applied Successfully!',
                                text: resp.message,
                                confirmButtonColor: '#10b981'
                            }).then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error', resp.message || 'An error occurred.', 'error');
                        }
                    },
                    error: function(xhr) {
                        var errMsg = 'An unexpected error occurred.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errMsg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endsection
