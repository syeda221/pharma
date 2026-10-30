@extends('admin_panel.layout.app')

@section('content')
<div class="container-fluid py-3">
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('sale.index') }}" class="btn btn-sm btn-light border rounded-circle" style="width:32px; height:32px;">
                    <i class="fas fa-arrow-left text-muted"></i>
                </a>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-table-list text-primary me-1"></i> Net Sales Audit Sheet &bull; {{ $sale->invoice_no }}
                    </h5>
                    <small class="text-muted">Standard Town &amp; Customer Distribution Reconciliation Sheet</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-sm btn-outline-primary px-3">
                    <i class="fas fa-edit me-1"></i> Edit Invoice
                </a>
                <button type="button" class="btn btn-sm btn-primary px-3" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print Sheet
                </button>
            </div>
        </div>
        <div class="card-body p-4">
            @include('admin_panel.sale.partials.audit_sheet_table', ['sale' => $sale, 'auditRows' => $auditRows, 'partyDiscountAmt' => $partyDiscountAmt ?? 0])
        </div>
    </div>
</div>
@endsection
