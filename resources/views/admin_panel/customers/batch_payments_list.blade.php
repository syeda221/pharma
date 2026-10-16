@extends('admin_panel.layout.app')

@section('content')
<style>
    .batch-list-container,
    .batch-list-container input,
    .batch-list-container select,
    .batch-list-container button,
    .batch-list-container table {
        font-family: 'Poppins', 'Montserrat', 'Outfit', sans-serif !important;
    }

    .batch-list-container {
        padding: 20px 24px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    .card-dark-border {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    .batch-table {
        font-size: .84rem;
        margin-bottom: 0;
    }
    .batch-table th, 
    .batch-table td {
        border: 1px solid #cbd5e1 !important;
        vertical-align: middle;
    }
    .batch-table thead th {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        font-size: .74rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 11px 10px;
        white-space: nowrap;
    }
    .batch-table tbody tr:hover {
        background-color: #f8fafc;
    }
</style>

<div class="batch-list-container">

    {{-- HEADER CARD --}}
    <div class="card card-dark-border mb-3">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom" style="border-bottom: 1.5px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        <i class="fas fa-history text-primary me-2"></i>Customer Recovery & Batch Payment History
                    </h5>
                    <span class="badge bg-light text-secondary border px-2.5 py-1 fw-semibold font-monospace">
                        {{ $payments->total() }} Records
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('customer-payments.bulk.create') }}" class="btn btn-primary btn-sm px-3 fw-bold" style="border-radius: 6px; font-size: .83rem;">
                        <i class="fas fa-plus-circle me-1"></i> New Bulk Recovery Batch
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('customer-payments.batches') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-bold text-secondary small">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" style="border: 1.5px solid #64748b; border-radius: 6px; height: 34px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-bold text-secondary small">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" style="border: 1.5px solid #64748b; border-radius: 6px; height: 34px;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-bold text-secondary small">Customer</label>
                        <select name="customer_id" class="form-select form-select-sm select2" style="border: 1.5px solid #64748b;">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-bold text-secondary small">Batch No</label>
                        <input type="text" name="batch_no" value="{{ request('batch_no') }}" placeholder="BAT-REC-..." class="form-control form-control-sm" style="border: 1.5px solid #64748b; border-radius: 6px; height: 34px;">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold" style="border-radius: 6px; height: 34px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- RECENT BATCHES SUMMARY PILLS --}}
    @if(isset($batchSummary) && $batchSummary->count() > 0)
    <div class="card card-dark-border mb-3">
        <div class="card-header bg-white py-2 px-3 border-bottom" style="border-bottom: 1.5px solid #cbd5e1 !important;">
            <span class="fw-bold text-dark small"><i class="fas fa-layer-group text-primary me-1"></i>Recent Recovery Batches (One-Click Consolidated Print)</span>
        </div>
        <div class="card-body p-2.5">
            <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1">
                @foreach($batchSummary as $bs)
                <div class="border rounded p-2 d-flex align-items-center justify-content-between gap-3 bg-white" style="min-width: 280px; border: 1.5px solid #cbd5e1 !important; border-radius: 8px !important;">
                    <div>
                        <div class="fw-bold font-monospace text-primary" style="font-size: 12px;">{{ $bs->batch_no }}</div>
                        <div class="text-muted" style="font-size: 11px;">
                            <span>{{ $bs->payment_date }}</span> &bull; <span><strong>{{ $bs->total_customers }}</strong> Customers</span>
                        </div>
                        <div class="fw-bold text-success font-monospace" style="font-size: 13px;">
                            PKR {{ number_format($bs->total_amount, 2) }}
                        </div>
                    </div>
                    <a href="{{ route('customer-payments.batch-print', $bs->batch_no) }}" target="_blank" class="btn btn-sm btn-outline-dark px-2.5 py-1.5 fw-bold" title="Print Consolidated Sheet" style="border-radius:6px; font-size:11px;">
                        <i class="fas fa-print me-1"></i> Sheet
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- PAYMENTS TABLE --}}
    <div class="card card-dark-border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle batch-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th style="width: 140px;">BATCH NO</th>
                            <th style="width: 140px;">RECEIPT NO</th>
                            <th>CUSTOMER</th>
                            <th class="text-center" style="width: 110px;">DATE</th>
                            <th class="text-center" style="width: 130px; background-color: #1e3a8a !important; color:#ffffff !important;">AMOUNT (PKR)</th>
                            <th class="text-center" style="width: 110px;">METHOD</th>
                            <th style="width: 140px;">REF / CHEQUE #</th>
                            <th class="text-center" style="width: 80px;">SLIP</th>
                            <th class="text-center" style="width: 120px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $key => $p)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $payments->firstItem() + $key }}</td>
                            <td>
                                <a href="{{ route('customer-payments.batch-print', $p->batch_no) }}" target="_blank" class="fw-bold font-monospace text-primary text-decoration-none" title="Print entire batch">
                                    {{ $p->batch_no }}
                                </a>
                            </td>
                            <td>
                                <span class="badge border font-monospace" style="background:#f1f5f9; color:#0f172a; border-color:#cbd5e1 !important; font-size:11.5px;">
                                    {{ $p->receipt_no ?: ('PAY-' . $p->id) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->customer->customer_name ?? 'N/A' }}</div>
                                <div class="text-muted small" style="font-size:11px;">Code: {{ $p->customer->customer_id ?? 'N/A' }}</div>
                            </td>
                            <td class="text-center font-monospace">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d-M-Y') : 'N/A' }}</td>
                            <td class="text-center font-monospace fw-bold text-primary" style="font-size: 13.5px;">
                                {{ number_format($p->amount, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $p->payment_method ?: 'Cash' }}</span>
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $p->cheque_no ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($p->attachment)
                                    <button type="button" class="btn btn-sm btn-outline-success p-1 btn-view-attachment" data-url="{{ asset('storage/' . $p->attachment) }}" data-title="Receipt Slip: {{ $p->receipt_no }}" title="View Attachment Slip" style="border-radius: 6px; font-size:11px;">
                                        <i class="fas fa-paperclip"></i> View
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customer-payments.receipt', $p->id) }}" target="_blank" class="btn btn-outline-primary p-1.5" title="Print Individual Receipt" style="border-radius: 6px 0 0 6px;">
                                        <i class="fas fa-print"></i> Receipt
                                    </a>
                                    <a href="{{ route('customer-payments.batch-print', $p->batch_no) }}" target="_blank" class="btn btn-outline-dark p-1.5" title="Print Combined Batch Sheet" style="border-radius: 0 6px 6px 0;">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-secondary"></i>
                                <p class="mb-0">No customer recovery batches found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
            <div class="p-3 d-flex justify-content-end border-top">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ATTACHMENT MODAL --}}
<div class="modal fade" id="slipModal" tabindex="-1" role="dialog" aria-labelledby="slipModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px; border: 1.5px solid #334155; overflow:hidden;">
            <div class="modal-header py-2.5 px-3 bg-dark text-white d-flex align-items-center justify-content-between">
                <h6 class="modal-title fw-bold text-white mb-0" id="slipModalTitle">
                    <i class="fas fa-file-alt me-2 text-primary"></i> Receipt Attachment Slip
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="font-size: 24px; font-weight: normal; opacity: 0.9; text-shadow: none; border: none; background: transparent; cursor: pointer; outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-3 text-center" id="slipModalBody" style="background:#f8fafc; min-height: 400px; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <!-- dynamic content -->
            </div>
            <div class="modal-footer py-2 px-3 bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $(document).on('click', '.btn-view-attachment', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var title = $(this).data('title') || 'Receipt Slip';
        $('#slipModalTitle').html('<i class="fas fa-paperclip me-2 text-primary"></i> ' + title);

        var ext = url.split('.').pop().toLowerCase();
        if (ext === 'pdf') {
            $('#slipModalBody').html(`
                <div class="w-100">
                    <iframe src="${url}" style="width:100%; height:550px; border:1px solid #cbd5e1; border-radius:8px;"></iframe>
                    <div class="mt-2 text-right text-end">
                        <a href="${url}" target="_blank" download class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </a>
                    </div>
                </div>
            `);
        } else {
            $('#slipModalBody').html(`
                <div class="w-100 text-center">
                    <img src="${url}" class="img-fluid rounded border shadow-sm" style="max-height: 550px; max-width: 100%; object-fit: contain;" alt="Receipt Slip">
                    <div class="mt-2 text-right text-end">
                        <a href="${url}" target="_blank" download class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Download Image
                        </a>
                    </div>
                </div>
            `);
        }
        $('#slipModal').modal('show');
    });
});
</script>
@endsection
