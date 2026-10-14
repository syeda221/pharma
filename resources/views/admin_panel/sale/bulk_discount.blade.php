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
        min-width: 140px;
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
        font-size: 19px;
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

    .tier-pill {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        margin: 2px;
        user-select: none;
    }
    .tier-pill:hover {
        transform: scale(1.05);
    }
    .tier-pill.tier-doc { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .tier-pill.tier-med { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
    .tier-pill.tier-dist { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
    .tier-pill.active {
        box-shadow: 0 0 0 2px #2563eb;
        font-weight: 800;
    }
    .tier-pill.tier-doc.active { background: #2563eb; color: #fff; border-color: #1d4ed8; }
    .tier-pill.tier-med.active { background: #059669; color: #fff; border-color: #047857; }
    .tier-pill.tier-dist.active { background: #7c3aed; color: #fff; border-color: #6d28d9; }

    .table-bulk th {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        background: #f8fafc;
        color: #475569;
        vertical-align: middle;
        border-top: none;
    }
    .table-bulk td {
        vertical-align: middle;
        font-size: 13px;
    }
    .item-subtable {
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px;
        margin: 8px 0;
        border: 1px dashed #cbd5e1;
    }
    .badge-no-disc {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
    }
    .badge-has-disc {
        background: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 4px;
    }
</style>

<div class="main-content-inner">
    <div class="container-fluid pt-3">

        <!-- Top Header & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-percent text-primary mr-2"></i>Bulk Discount Manager & Live Preview
                </h4>
                <p class="text-muted small mb-0">
                    Apply customer discount tiers (Doctor, Medical, Distribution) to past sales in bulk without opening individual invoices.
                </p>
            </div>
            <div>
                <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Back to Sales
                </a>
            </div>
        </div>

        <!-- Safety Assurance Alert -->
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3 py-2 px-3" style="background: #eff6ff; border-left: 4px solid #3b82f6 !important; border-radius: 6px;">
            <i class="fa fa-shield-alt text-primary fa-lg mr-3"></i>
            <div class="small text-dark">
                <strong>Inventory Stock Safe Guarantee:</strong> Updating discounts modifies invoice totals, customer ledgers, and journal vouchers only. 
                <span class="text-primary font-weight-bold">Warehouse stock, carton counts, loose pieces, and return quantities are completely untouched.</span>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('sale.bulk_discount') }}" id="filterForm">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
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
                            <label class="small font-weight-bold text-muted mb-1">Discount Status</label>
                            <select name="discount_filter" class="form-control form-control-sm">
                                <option value="no_discount" {{ $discountFilter == 'no_discount' ? 'selected' : '' }}>No Discount / 0% Only</option>
                                <option value="has_discount" {{ $discountFilter == 'has_discount' ? 'selected' : '' }}>With Discount (>0%)</option>
                                <option value="all" {{ $discountFilter == 'all' ? 'selected' : '' }}>All Sales</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Date Range</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From Date">
                                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To Date">
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-12 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary btn-sm px-3 mr-2 shadow-sm">
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
                <div class="kpi-sub">New balance after update</div>
            </div>
        </div>

        <!-- Main Form & Table -->
        <form id="bulkDiscountForm">
            @csrf

            <!-- Batch Action Control Bar -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center bg-white rounded">
                    <div class="d-flex align-items-center flex-wrap mb-1 mb-md-0">
                        <div class="custom-control custom-checkbox mr-3 ml-2">
                            <input type="checkbox" class="custom-control-input" id="checkAllMaster">
                            <label class="custom-control-label font-weight-bold text-dark small" for="checkAllMaster">
                                Select All (<span id="totalVisibleCount">{{ $sales->count() }}</span> on this page)
                            </label>
                        </div>

                        <!-- Bulk Preset Actions -->
                        <div class="btn-group btn-group-sm mr-2 my-1" role="group">
                            <button type="button" class="btn btn-outline-primary" id="btnApplyAllDoctor" title="Apply each customer's Doctor tier discount">
                                <i class="fa fa-user-md mr-1"></i> Apply Doctor Tier
                            </button>
                            <button type="button" class="btn btn-outline-success" id="btnApplyAllMedical" title="Apply each customer's Medical tier discount">
                                <i class="fa fa-clinic-medical mr-1"></i> Apply Medical Tier
                            </button>
                            <button type="button" class="btn btn-outline-purple" style="border-color:#7c3aed; color:#7c3aed;" id="btnApplyAllDist" title="Apply each customer's Distribution tier discount">
                                <i class="fa fa-truck mr-1"></i> Apply Distribution Tier
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnApplyCustomPrompt" title="Apply a uniform custom % to all selected">
                                <i class="fa fa-cog mr-1"></i> Set Custom %
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
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
                                    <th width="40" class="text-center">#</th>
                                    <th>Invoice & Date</th>
                                    <th>Customer & Type</th>
                                    <th>Customer Discount Tiers</th>
                                    <th>Current Disc</th>
                                    <th width="240">Select Tier to Apply</th>
                                    <th class="text-right">Original Total</th>
                                    <th class="text-right">Projected Disc</th>
                                    <th class="text-right">New Net</th>
                                    <th width="80" class="text-center">Items</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    @php
                                        $cust = $sale->customer_relation;
                                        $tiers = $cust ? $cust->discount_tiers : ['medical' => 0, 'doctor' => 0, 'distribution' => 0];
                                        
                                        // Current discount info
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

                                        // Default proposed tier: doctor if > 0, else first positive tier, else doctor
                                        $proposedTier = 'doctor';
                                        if (($tiers['doctor'] ?? 0) > 0) {
                                            $proposedTier = 'doctor';
                                        } elseif (($tiers['medical'] ?? 0) > 0) {
                                            $proposedTier = 'medical';
                                        } elseif (($tiers['distribution'] ?? 0) > 0) {
                                            $proposedTier = 'distribution';
                                        }
                                        $proposedPct = (float)($tiers[$proposedTier] ?? 0);
                                    @endphp
                                    <tr class="sale-row" 
                                        id="row-{{ $sale->id }}"
                                        data-sale-id="{{ $sale->id }}"
                                        data-gross="{{ $grossTotal }}"
                                        data-old-net="{{ $sale->total_net }}"
                                        data-extra-disc="{{ $sale->total_extradiscount ?? 0 }}"
                                        data-tier-doc="{{ (float)($tiers['doctor'] ?? 0) }}"
                                        data-tier-med="{{ (float)($tiers['medical'] ?? 0) }}"
                                        data-tier-dist="{{ (float)($tiers['distribution'] ?? 0) }}">
                                        
                                        <!-- Checkbox -->
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

                                        <!-- Invoice & Date -->
                                        <td>
                                            <a href="{{ route('sales.invoice', $sale->id) }}" target="_blank" class="font-weight-bold text-primary">
                                                #{{ $sale->invoice_no }}
                                            </a>
                                            <div class="text-muted small">
                                                {{ $sale->created_at ? $sale->created_at->format('d M, Y') : '-' }}
                                            </div>
                                            <span class="badge {{ $sale->sale_status == 'posted' ? 'badge-success' : 'badge-warning' }} px-1 small" style="font-size: 10px;">
                                                {{ ucfirst($sale->sale_status) }}
                                            </span>
                                        </td>

                                        <!-- Customer & Type -->
                                        <td>
                                            <div class="font-weight-bold text-dark">
                                                {{ $cust->customer_name ?? 'N/A' }}
                                            </div>
                                            <div class="small text-muted">
                                                <span class="badge badge-light border">{{ $cust->customer_type ?? 'Regular' }}</span>
                                            </div>
                                        </td>

                                        <!-- Customer Available Tiers Pills -->
                                        <td>
                                            <div class="d-flex flex-wrap align-items-center">
                                                <span class="tier-pill tier-doc pill-click" data-sale-id="{{ $sale->id }}" data-tier="doctor" title="Click to select Doctor tier">
                                                    Doc: {{ (float)($tiers['doctor'] ?? 0) }}%
                                                </span>
                                                <span class="tier-pill tier-med pill-click" data-sale-id="{{ $sale->id }}" data-tier="medical" title="Click to select Medical tier">
                                                    Med: {{ (float)($tiers['medical'] ?? 0) }}%
                                                </span>
                                                <span class="tier-pill tier-dist pill-click" data-sale-id="{{ $sale->id }}" data-tier="distribution" title="Click to select Distribution tier">
                                                    Dist: {{ (float)($tiers['distribution'] ?? 0) }}%
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Current Disc Status -->
                                        <td>
                                            @if($hasDiscount)
                                                <span class="badge-has-disc">
                                                    {{ ucfirst($sale->discount_tier ?: 'Custom') }}: Rs. {{ number_format($currentDiscAmount, 2) }}
                                                </span>
                                            @else
                                                <span class="badge-no-disc">0% / None</span>
                                            @endif
                                        </td>

                                        <!-- New Tier Selection -->
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <select name="tier[{{ $sale->id }}]" class="form-control form-control-sm tier-select" data-sale-id="{{ $sale->id }}">
                                                    <option value="doctor" {{ $proposedTier == 'doctor' ? 'selected' : '' }}>Doctor ({{ (float)($tiers['doctor'] ?? 0) }}%)</option>
                                                    <option value="medical" {{ $proposedTier == 'medical' ? 'selected' : '' }}>Medical ({{ (float)($tiers['medical'] ?? 0) }}%)</option>
                                                    <option value="distribution" {{ $proposedTier == 'distribution' ? 'selected' : '' }}>Distribution ({{ (float)($tiers['distribution'] ?? 0) }}%)</option>
                                                    <option value="custom">Custom %</option>
                                                </select>
                                                <div class="input-group-append custom-pct-wrap" id="custom-wrap-{{ $sale->id }}" style="display: none; max-width: 80px;">
                                                    <input type="number" 
                                                           step="0.01" 
                                                           min="0" 
                                                           max="100" 
                                                           name="custom_percent[{{ $sale->id }}]" 
                                                           class="form-control form-control-sm custom-pct-input text-right" 
                                                           data-sale-id="{{ $sale->id }}"
                                                           placeholder="%" 
                                                           value="{{ $proposedPct }}">
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Original Total -->
                                        <td class="text-right text-dark font-weight-bold">
                                            Rs. {{ number_format($sale->total_net, 2) }}
                                        </td>

                                        <!-- Projected Discount -->
                                        <td class="text-right text-danger font-weight-bold" id="proj-disc-{{ $sale->id }}">
                                            Rs. 0.00
                                        </td>

                                        <!-- New Net Total -->
                                        <td class="text-right text-success font-weight-bold" id="proj-net-{{ $sale->id }}">
                                            Rs. {{ number_format($sale->total_net, 2) }}
                                        </td>

                                        <!-- Items Expand Button -->
                                        <td class="text-center">
                                            <button type="button" class="btn btn-xs btn-outline-info toggle-items" data-target="#items-{{ $sale->id }}" title="Inspect Products">
                                                <i class="fa fa-boxes"></i> {{ $sale->items->count() }}
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Accordion Sub-row: Product Items -->
                                    <tr id="items-{{ $sale->id }}" class="items-detail-row" style="display: none;">
                                        <td colspan="10" class="p-0 border-0">
                                            <div class="item-subtable mx-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <strong class="small text-muted text-uppercase">
                                                        <i class="fa fa-list mr-1"></i> Invoice Products Breakdown
                                                    </strong>
                                                    <span class="small text-muted">You can also adjust discount % per product if required</span>
                                                </div>
                                                <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 12px;">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Product</th>
                                                            <th class="text-center" width="100">Billed Qty</th>
                                                            <th class="text-right" width="110">Unit Price</th>
                                                            <th class="text-right" width="130">Line Gross</th>
                                                            <th class="text-center" width="140">Line Disc %</th>
                                                            <th class="text-right" width="130">Line Disc Amount</th>
                                                            <th class="text-right" width="130">Line Net Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($sale->items as $item)
                                                            @php
                                                                $itemGross = (float)($item->total + $item->discount_amount);
                                                                if ($itemGross <= 0) {
                                                                    $itemGross = (float)$item->qty * (float)$item->price;
                                                                }
                                                            @endphp
                                                            <tr class="item-subrow" 
                                                                data-item-id="{{ $item->id }}" 
                                                                data-sale-id="{{ $sale->id }}" 
                                                                data-gross="{{ $itemGross }}">
                                                                <td>
                                                                    <strong>{{ $item->product_name ?? ($item->product->item_name ?? 'Item #' . $item->id) }}</strong>
                                                                </td>
                                                                <td class="text-center">
                                                                    {{ (float)$item->qty }} {{ $item->size_mode == 'by_cartons' ? 'Ctn' : 'Pcs' }}
                                                                </td>
                                                                <td class="text-right">
                                                                    Rs. {{ number_format($item->price, 2) }}
                                                                </td>
                                                                <td class="text-right item-gross-val">
                                                                    Rs. {{ number_format($itemGross, 2) }}
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="input-group input-group-sm m-auto" style="max-width: 100px;">
                                                                        <input type="number" 
                                                                               step="0.01" 
                                                                               min="0" 
                                                                               max="100" 
                                                                               name="item_discount[{{ $sale->id }}][{{ $item->id }}]" 
                                                                               value="{{ (float)$item->discount_percent }}" 
                                                                               class="form-control form-control-sm text-center item-pct-input" 
                                                                               data-sale-id="{{ $sale->id }}"
                                                                               data-item-id="{{ $item->id }}">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text p-1" style="font-size:10px;">%</span>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="text-right text-danger item-disc-val" id="item-disc-val-{{ $item->id }}">
                                                                    Rs. {{ number_format($item->discount_amount, 2) }}
                                                                </td>
                                                                <td class="text-right font-weight-bold text-success item-net-val" id="item-net-val-{{ $item->id }}">
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
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            <i class="fa fa-info-circle fa-2x mb-2 text-secondary d-block"></i>
                                            No sales found matching your filter criteria.
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
    // Initialize select2
    if ($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // Toggle Item detail rows
    $(document).on('click', '.toggle-items', function() {
        var target = $(this).data('target');
        $(target).fadeToggle(150);
    });

    // Helper: calculate effective discount percentage for a row
    function getRowEffectivePercent(saleId) {
        var row = $('#row-' + saleId);
        var tier = row.find('.tier-select').val();
        var pct = 0;

        if (tier === 'doctor') {
            pct = parseFloat(row.data('tier-doc')) || 0;
        } else if (tier === 'medical') {
            pct = parseFloat(row.data('tier-med')) || 0;
        } else if (tier === 'distribution') {
            pct = parseFloat(row.data('tier-dist')) || 0;
        } else if (tier === 'custom') {
            pct = parseFloat(row.find('.custom-pct-input').val()) || 0;
        }
        return pct;
    }

    // Recalculate a single sale's projected discount & net
    function recalcSaleRow(saleId) {
        var row = $('#row-' + saleId);
        var pct = getRowEffectivePercent(saleId);
        var extraDisc = parseFloat(row.data('extra-disc')) || 0;

        // Check if items table has inputs
        var itemRows = $('#items-' + saleId).find('.item-subrow');
        var newTotalBill = 0;
        var totalItemDisc = 0;

        if (itemRows.length > 0) {
            itemRows.each(function() {
                var itemRow = $(this);
                var itemId = itemRow.data('item-id');
                var gross = parseFloat(itemRow.data('gross')) || 0;
                var itemInput = itemRow.find('.item-pct-input');
                
                // If user didn't individually touch the item input, sync it with the sale's tier pct
                var linePct = parseFloat(itemInput.val());
                if (isNaN(linePct) || itemInput.data('auto-synced') !== false) {
                    linePct = pct;
                    itemInput.val(linePct);
                }

                var lineDisc = Math.round((gross * linePct / 100) * 100) / 100;
                var lineNet = Math.max(0, Math.round((gross - lineDisc) * 100) / 100);

                $('#item-disc-val-' + itemId).text('Rs. ' + lineDisc.toFixed(2));
                $('#item-net-val-' + itemId).text('Rs. ' + lineNet.toFixed(2));

                totalItemDisc += lineDisc;
                newTotalBill += lineNet;
            });
        } else {
            var gross = parseFloat(row.data('gross')) || 0;
            totalItemDisc = Math.round((gross * pct / 100) * 100) / 100;
            newTotalBill = Math.max(0, gross - totalItemDisc);
        }

        var newNet = Math.max(0, Math.round((newTotalBill - extraDisc) * 100) / 100);

        $('#proj-disc-' + saleId).text('Rs. ' + totalItemDisc.toFixed(2) + ' (' + pct.toFixed(1) + '%)');
        $('#proj-net-' + saleId).text('Rs. ' + newNet.toFixed(2));

        // Store projected values on row
        row.data('proj-disc', totalItemDisc);
        row.data('proj-net', newNet);

        // Update pill active state
        var tier = row.find('.tier-select').val();
        row.find('.pill-click').removeClass('active');
        row.find('.pill-click[data-tier="' + tier + '"]').addClass('active');

        // Toggle custom input wrap
        if (tier === 'custom') {
            $('#custom-wrap-' + saleId).show();
        } else {
            $('#custom-wrap-' + saleId).hide();
        }
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

        var avgPct = totalOrig > 0 ? ((totalDisc / totalOrig) * 100).toFixed(1) : '0.0';
        $('#kpiDiscountPercent').text(avgPct + '% average deduction');

        if (selectedCount > 0) {
            $('#btnSubmitBulk').prop('disabled', false);
        } else {
            $('#btnSubmitBulk').prop('disabled', true);
        }
    }

    // Recalculate all visible rows on initial load
    $('.sale-row').each(function() {
        var saleId = $(this).data('sale-id');
        recalcSaleRow(saleId);
    });
    recalcMasterKPI();

    // Event: Tier dropdown changed
    $(document).on('change', '.tier-select', function() {
        var saleId = $(this).data('sale-id');
        // Reset item inputs auto-synced flag so they update to new tier
        $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Click on tier pill
    $(document).on('click', '.pill-click', function() {
        var saleId = $(this).data('sale-id');
        var tier = $(this).data('tier');
        var row = $('#row-' + saleId);
        row.find('.tier-select').val(tier).trigger('change');
        // Auto check this row
        $('#chk-' + saleId).prop('checked', true).trigger('change');
    });

    // Event: Custom % input changed
    $(document).on('input change', '.custom-pct-input', function() {
        var saleId = $(this).data('sale-id');
        $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Individual item discount input changed
    $(document).on('input change', '.item-pct-input', function() {
        var saleId = $(this).data('sale-id');
        $(this).data('auto-synced', false); // User manually tweaked this item
        recalcSaleRow(saleId);
        recalcMasterKPI();
    });

    // Event: Checkbox toggled
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

    // Presets: Apply Doctor to all
    $('#btnApplyAllDoctor').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.tier-select').val('doctor').trigger('change');
        });
    });

    // Presets: Apply Medical to all
    $('#btnApplyAllMedical').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.tier-select').val('medical').trigger('change');
        });
    });

    // Presets: Apply Distribution to all
    $('#btnApplyAllDist').on('click', function() {
        $('.sale-checkbox:checked').each(function() {
            var saleId = $(this).val();
            var row = $('#row-' + saleId);
            row.find('.tier-select').val('distribution').trigger('change');
        });
    });

    // Presets: Apply Custom % to all selected
    $('#btnApplyCustomPrompt').on('click', function() {
        var checkedBoxes = $('.sale-checkbox:checked');
        if (checkedBoxes.length === 0) {
            Swal.fire('No Selection', 'Please select at least one sale first.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Set Uniform Discount %',
            text: 'Enter the discount percentage to apply to all ' + checkedBoxes.length + ' selected sales:',
            input: 'number',
            inputAttributes: {
                min: 0,
                max: 100,
                step: 0.1
            },
            inputValue: 10,
            showCancelButton: true,
            confirmButtonText: 'Apply to Selected',
            confirmButtonColor: '#2563eb'
        }).then(function(result) {
            if (result.isConfirmed && result.value !== '') {
                var customVal = parseFloat(result.value) || 0;
                checkedBoxes.each(function() {
                    var saleId = $(this).val();
                    var row = $('#row-' + saleId);
                    row.find('.tier-select').val('custom');
                    row.find('.custom-pct-input').val(customVal);
                    $('#custom-wrap-' + saleId).show();
                    $('#items-' + saleId).find('.item-pct-input').data('auto-synced', true);
                    recalcSaleRow(saleId);
                });
                recalcMasterKPI();
            }
        });
    });

    // Submit Bulk Discounts with SweetAlert Confirmation
    $('#btnSubmitBulk').on('click', function() {
        var selectedBoxes = $('.sale-checkbox:checked');
        var count = selectedBoxes.length;

        if (count === 0) {
            Swal.fire('Error', 'Please select at least one sale to update.', 'warning');
            return;
        }

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
                        <li class="list-group-item d-flex justify-content-between text-danger">
                            <span>Total Discount to Deduct:</span>
                            <strong>${totalDisc}</strong>
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
            confirmButtonText: '<i class="fa fa-check mr-1"></i> Yes, Apply Discounts Now',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#64748b'
        }).then(function(result) {
            if (result.isConfirmed) {
                // Show loading
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
