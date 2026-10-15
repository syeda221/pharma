@extends('admin_panel.layout.app')

@section('content')
<style>
    .closing-page {
        padding: 20px 24px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }
    .detail-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
</style>

<div class="closing-page">

    {{-- HEADER BAR --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0 font-monospace text-primary">
                    {{ $closing->closing_code }}
                </h5>
                <span class="badge bg-primary px-2.5 py-1 text-white">
                    Month: {{ \Carbon\Carbon::parse($closing->closing_month . '-01')->format('F Y') }}
                </span>
                @if($closing->adjustment_mode === 'equal')
                    <span class="badge bg-success px-2 py-1"><i class="fas fa-check-double me-1"></i>System Stock Equalized</span>
                @elseif($closing->adjustment_mode === 'selective')
                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-sliders me-1"></i>Selective Adjustment</span>
                @else
                    <span class="badge bg-secondary px-2 py-1"><i class="fas fa-eye me-1"></i>Audit Sheet Only</span>
                @endif
            </div>
            <div class="text-muted small mt-1">
                Warehouse: <strong>{{ $closing->warehouse->warehouse_name ?? 'Main' }}</strong> | 
                Audit Date: <strong>{{ \Carbon\Carbon::parse($closing->closing_date)->format('d-M-Y') }}</strong> | 
                Audited By: <strong>{{ $closing->creator->name ?? 'Admin' }}</strong>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('stock-closing.edit', $closing->id) }}" class="btn btn-warning btn-sm px-3 fw-bold text-dark" style="border-radius: 6px;">
                <i class="fas fa-edit me-1"></i> Edit Audit
            </a>
            <a href="{{ route('stock-closing.print', $closing->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm px-3 fw-bold" style="border-radius: 6px;">
                <i class="fas fa-print me-1"></i> Print Sheet
            </a>
            <a href="{{ route('stock-closing.index') }}" class="btn btn-light border btn-sm px-3 fw-bold text-secondary" style="border-radius: 6px;">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('stock-closing.create') }}" class="btn btn-primary btn-sm px-3 fw-bold" style="background:#4f46e5; border-color:#4f46e5; border-radius: 6px;">
                <i class="fas fa-plus me-1"></i> New Closing
            </a>
        </div>
    </div>

    {{-- KPI METRIC PILLS --}}
    <div class="row g-2 mb-3">
        <div class="col-md-2">
            <div class="detail-card p-2.5 text-center">
                <div class="text-muted small fw-bold text-uppercase" style="font-size:10px;">Total Products</div>
                <div class="fs-5 fw-bold text-dark">{{ $closing->total_items_count }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="detail-card p-2.5 text-center" style="background:#f0fdf4; border-color:#86efac;">
                <div class="text-success small fw-bold text-uppercase" style="font-size:10px;">Matched Stock</div>
                <div class="fs-5 fw-bold text-success">{{ $closing->matched_count }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="detail-card p-2.5 text-center" style="background:#fef2f2; border-color:#fca5a5;">
                <div class="text-danger small fw-bold text-uppercase" style="font-size:10px;">Discrepancies</div>
                <div class="fs-5 fw-bold text-danger">{{ $closing->discrepant_count }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="detail-card p-2.5 text-center">
                <div class="text-secondary small fw-bold text-uppercase" style="font-size:10px;">System Book Stock</div>
                <div class="fs-5 fw-bold text-secondary">{{ number_format($closing->total_system_pieces, 0) }} Pcs</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="detail-card p-2.5 text-center" style="background:#f0f9ff; border-color:#bae6fd;">
                <div class="text-primary small fw-bold text-uppercase" style="font-size:10px;">Physical Count</div>
                <div class="fs-5 fw-bold text-primary">{{ number_format($closing->total_physical_pieces, 0) }} Pcs</div>
            </div>
        </div>
        <div class="col-md-2">
            @php
                $vPcs = (float)$closing->net_variance_pieces;
                $vBg = $vPcs == 0 ? '#f0fdf4' : ($vPcs < 0 ? '#fff1f2' : '#f0f9ff');
                $vBorder = $vPcs == 0 ? '#86efac' : ($vPcs < 0 ? '#fecdd3' : '#bae6fd');
                $vColor = $vPcs == 0 ? '#059669' : ($vPcs < 0 ? '#dc2626' : '#2563eb');
                $vSign = $vPcs > 0 ? '+' : '';
            @endphp
            <div class="detail-card p-2.5 text-center" style="background:{{ $vBg }}; border-color:{{ $vBorder }};">
                <div class="small fw-bold text-uppercase" style="font-size:10px; color:{{ $vColor }};">Net Variance</div>
                <div class="fs-5 fw-bold" style="color:{{ $vColor }};">{{ $vSign }}{{ number_format($vPcs, 0) }} Pcs</div>
            </div>
        </div>
    </div>

    {{-- REASON & REMARKS CARD --}}
    @if($closing->reason_type || $closing->remarks)
        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px; background:#ffffff;">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col-md-4 border-end">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size:11px;">Variance Reason:</div>
                        <div class="fw-bold text-dark fs-6">{{ $closing->reason_type ?? 'Audit Correction' }}</div>
                    </div>
                    <div class="col-md-8 ps-md-3">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size:11px;">Detailed Remarks & Audit Notes:</div>
                        <div class="text-dark small">{{ $closing->remarks ?: 'No remarks provided.' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ITEMS TABLE CARD --}}
    <div class="card border-0 shadow-sm" style="border-radius:10px; background:#ffffff;">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
            <span class="fw-bold text-dark fs-6"><i class="fas fa-list-check me-2 text-primary"></i>Reconciliation Items Breakdown</span>
            <span class="badge bg-light text-secondary border">{{ count($closing->items) }} Products Audited</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size:.80rem;">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>PRODUCT NAME & CODE</th>
                            <th class="text-center" style="width: 70px;">PPB</th>
                            <th class="text-center" style="width: 170px;">SYSTEM STOCK (BOOK)</th>
                            <th class="text-center" style="width: 170px;">PHYSICAL COUNT (GODOWN)</th>
                            <th class="text-center" style="width: 170px;">VARIANCE (DIFF)</th>
                            <th class="text-center" style="width: 90px;">STATUS</th>
                            <th class="text-center" style="width: 100px;">SYSTEM ADJUSTED?</th>
                            <th class="text-center" style="width: 140px;">FINAL STOCK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($closing->items as $idx => $it)
                            @php
                                $ppb = $it->pieces_per_box > 0 ? $it->pieces_per_box : 1;
                                $statusBadge = match($it->status) {
                                    'matched' => '<span class="badge bg-success-subtle text-success border border-success-subtle">Matched</span>',
                                    'shortage' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Shortage</span>',
                                    'surplus' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Surplus</span>',
                                    default => '<span class="badge bg-light text-muted border">-</span>'
                                };
                            @endphp
                            <tr>
                                <td class="text-center text-muted fw-bold">{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size:12.5px;">{{ $it->variant_name ?: ($it->product->item_name ?? 'Product ID #' . $it->product_id) }}</div>
                                    <div class="d-flex align-items-center flex-wrap gap-1 mt-0.5">
                                        <span class="badge bg-light text-primary border font-monospace" style="font-size:10px;">{{ $it->product->item_code ?? '-' }}</span>
                                        @if($it->variant_name || $it->variant_key)
                                            <span class="badge border" style="font-size:10px; background:#e0e7ff; color:#3730a3; border-color:#c7d2fe !important;"><i class="fas fa-tag me-0.5"></i>Variant</span>
                                        @endif
                                        <span class="badge bg-light text-secondary border" style="font-size:10px;">{{ $it->product->category_relation->name ?? 'Category' }}</span>
                                        <span class="badge bg-light text-muted border" style="font-size:10px;">Unit: {{ $it->product->unit->name ?? 'Pcs' }}</span>
                                    </div>
                                </td>
                                <td class="text-center text-secondary fw-semibold">{{ $ppb }}</td>
                                
                                {{-- System Stock --}}
                                <td class="text-center bg-light">
                                    <div class="fw-bold text-dark font-monospace">
                                        {{ (int)$it->system_cartons }} Ctn {{ (int)$it->system_pieces > 0 ? '+ ' . (int)$it->system_pieces . ' Pcs' : '' }}
                                    </div>
                                    <div class="text-muted small" style="font-size:10px;">Total: {{ (int)$it->system_total_pieces }} Pcs</div>
                                </td>

                                {{-- Physical Stock --}}
                                <td class="text-center" style="background:#f8fafc;">
                                    <div class="fw-bold text-primary font-monospace">
                                        {{ (int)$it->physical_cartons }} Ctn {{ (int)$it->physical_pieces > 0 ? '+ ' . (int)$it->physical_pieces . ' Pcs' : '' }}
                                    </div>
                                    <div class="text-muted small" style="font-size:10px;">Total: {{ (int)$it->physical_total_pieces }} Pcs</div>
                                </td>

                                {{-- Variance --}}
                                <td class="text-center">
                                    <div class="fw-bold {{ $it->variance_pieces == 0 ? 'text-success' : ($it->variance_pieces < 0 ? 'text-danger' : 'text-primary') }}">
                                        {{ $it->variance_display }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="text-center">
                                    {!! $statusBadge !!}
                                </td>

                                {{-- Adjusted --}}
                                <td class="text-center">
                                    @if($it->is_adjusted)
                                        <span class="badge bg-success px-2 py-1"><i class="fas fa-check me-0.5"></i>Yes</span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1">No</span>
                                    @endif
                                </td>

                                {{-- Final Stock --}}
                                <td class="text-center bg-light font-monospace fw-bold text-dark">
                                    {{ (int)$it->new_stock }} Pcs
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
