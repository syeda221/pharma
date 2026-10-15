@extends('admin_panel.layout.app')

@section('content')
<style>
    .stock-closing-wrap {
        padding: 20px 24px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    /* KPI Metric Cards */
    .closing-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .closing-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .closing-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    /* Table & Card Styles */
    .closing-panel-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 20px;
    }

    .closing-table thead th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 14px;
        border-bottom: 1.5px solid #cbd5e1 !important;
        white-space: nowrap;
        vertical-align: middle;
    }

    .closing-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        font-size: .82rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .closing-table tbody tr:hover {
        background-color: #f8fafc;
    }

    .action-btn-group .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 13px;
    }
</style>

<div class="stock-closing-wrap">

    {{-- TOP HEADER BAR --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="fas fa-clipboard-check text-primary me-2"></i>Monthly Physical Stock Closing
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold" style="font-size: 11.5px; border-radius: 6px;">
                    <i class="fas fa-warehouse me-1"></i>{{ $mainWarehouse->warehouse_name ?? 'Main Store' }}
                </span>
            </div>
            <p class="text-muted small mb-0 mt-1">Warehouse stock reconciliation, physical count audit sheets, and variance history</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('stock-closing.create') }}" class="btn btn-primary px-3 py-2 fw-semibold d-inline-flex align-items-center shadow-sm" style="border-radius: 8px; font-size: .84rem; background: #4f46e5; border-color: #4f46e5;">
                <i class="fas fa-plus me-1.5"></i> Conduct New Monthly Closing
            </a>
        </div>
    </div>

    {{-- KPI METRICS CARDS --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="closing-kpi-card">
                <div class="closing-kpi-icon" style="background: #eef2ff; color: #4f46e5;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Audit Closings</div>
                    <div class="fs-4 fw-bold text-dark mt-0.5">{{ $totalClosings }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="closing-kpi-card">
                <div class="closing-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-sliders-h"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Stock Reconciled / Adjusted</div>
                    <div class="fs-4 fw-bold text-success mt-0.5">{{ $totalAdjusted }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="closing-kpi-card">
                <div class="closing-kpi-icon" style="background: #f0f9ff; color: #0284c7;">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">This Month Audits</div>
                    <div class="fs-4 fw-bold text-dark mt-0.5">{{ $thisMonthClosings }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER FORM CARD --}}
    <div class="closing-panel-card p-3 mb-3">
        <form method="GET" action="{{ route('stock-closing.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Closing Month</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-alt"></i></span>
                        <input type="month" name="closing_month" class="form-control" value="{{ request('closing_month') }}" style="height: 34px; border-radius: 0 6px 6px 0;">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Search Records</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by closing code, reason, or remarks..." value="{{ request('search') }}" style="height: 34px; border-radius: 0 6px 6px 0;">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill fw-semibold d-inline-flex align-items-center justify-content-center" style="height: 34px; border-radius: 6px; background:#4f46e5; border-color:#4f46e5;">
                            <i class="fas fa-filter me-1.5"></i> Filter Records
                        </button>
                        <a href="{{ route('stock-closing.index') }}" class="btn btn-light border btn-sm text-secondary d-inline-flex align-items-center justify-content-center px-3" style="height: 34px; border-radius: 6px;" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- LIST TABLE CARD --}}
    <div class="closing-panel-card">
        <div class="card-header bg-white py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-radius: 12px 12px 0 0;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark fs-6">
                    <i class="fas fa-list text-primary me-1.5"></i>Audit History Records
                </span>
                <span class="badge bg-light text-secondary border small">{{ $closings->total() }} Total Closings</span>
            </div>
        </div>

        <div class="p-0">
            <div class="table-responsive">
                <table class="table align-middle closing-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" style="width: 150px;">CLOSING CODE</th>
                            <th style="width: 110px;">MONTH</th>
                            <th>WAREHOUSE</th>
                            <th class="text-center" style="width: 90px;">ITEMS</th>
                            <th class="text-center" style="width: 110px;">MATCHED</th>
                            <th class="text-center" style="width: 120px;">DISCREPANCIES</th>
                            <th class="text-center" style="width: 130px;">NET VARIANCE</th>
                            <th style="width: 130px;">ACTION MODE</th>
                            <th>REASON / NOTES</th>
                            <th class="text-end pe-3" style="width: 130px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($closings as $c)
                            @php
                                $vPieces = (float) $c->net_variance_pieces;
                                $vColor = $vPieces == 0 ? '#059669' : ($vPieces < 0 ? '#dc2626' : '#2563eb');
                                $vSign = $vPieces > 0 ? '+' : '';
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('stock-closing.show', $c->id) }}" class="fw-bold text-primary font-monospace" style="font-size: 13px;">
                                        {{ $c->closing_code }}
                                    </a>
                                    <div class="text-muted" style="font-size: 11px;">{{ \Carbon\Carbon::parse($c->closing_date)->format('d-M-Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 11px;">
                                        {{ \Carbon\Carbon::parse($c->closing_month . '-01')->format('M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $c->warehouse->warehouse_name ?? 'Main Store' }}</div>
                                    <div class="text-muted small" style="font-size: 10.5px;">By: {{ $c->creator->name ?? 'Admin' }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ $c->total_items_count }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 11px;">
                                        <i class="fas fa-check me-0.5"></i>{{ $c->matched_count }} Matched
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($c->discrepant_count > 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-exclamation-circle me-0.5"></i>{{ $c->discrepant_count }} Diff
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">0 Diff</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold" style="color: {{ $vColor }}; font-size: 12.5px;">
                                    {{ $vSign }}{{ number_format($vPieces, 0) }} Pcs
                                </td>
                                <td>
                                    @if($c->adjustment_mode === 'equal')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-equals me-1"></i>Equalized
                                        </span>
                                    @elseif($c->adjustment_mode === 'selective')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-list-check me-1"></i>Selective
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border px-2 py-1" style="font-size: 11px;">
                                            <i class="fas fa-eye me-1"></i>Audit Only
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;">{{ $c->reason_type ?? 'Audit Correction' }}</div>
                                    @if($c->remarks)
                                        <div class="text-muted small text-truncate" style="font-size: 11px; max-width: 220px;">{{ $c->remarks }}</div>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1 action-btn-group">
                                        <a href="{{ route('stock-closing.show', $c->id) }}" class="btn btn-outline-primary" title="View Audit Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('stock-closing.edit', $c->id) }}" class="btn btn-outline-warning text-dark" title="Edit Audit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('stock-closing.print', $c->id) }}" target="_blank" class="btn btn-outline-secondary" title="Print Audit Sheet">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="fas fa-clipboard-check fa-3x mb-3 text-secondary opacity-50"></i>
                                        <div class="fs-6 fw-semibold text-dark">No monthly stock closings recorded yet.</div>
                                        <div class="text-muted small mt-1">Start by conducting your first monthly physical count and audit.</div>
                                        <div class="mt-3">
                                            <a href="{{ route('stock-closing.create') }}" class="btn btn-primary btn-sm px-3 py-2 fw-semibold" style="background:#4f46e5; border-color:#4f46e5; border-radius: 6px;">
                                                <i class="fas fa-plus me-1"></i> Conduct First Monthly Closing
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($closings->hasPages())
                <div class="p-3 border-top">
                    {{ $closings->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
