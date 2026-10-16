@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Consistent ERP & Navbar Typography */
    .bulk-pay-container,
    .bulk-pay-container input,
    .bulk-pay-container select,
    .bulk-pay-container button,
    .bulk-pay-container textarea,
    .bulk-pay-container table {
        font-family: 'Poppins', 'Montserrat', 'Outfit', sans-serif !important;
    }

    .bulk-pay-container {
        padding: 20px 24px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }

    /* Crisp High-Contrast Cards */
    .recovery-card-dark {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }

    /* KPI Summary Pills Bar */
    .recovery-pill-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        overflow-x: auto;
        white-space: nowrap;
    }
    .recovery-pill {
        flex: 1 1 0px;
        min-width: 170px;
        padding: 10px 14px;
        border-radius: 10px;
        background: #ffffff;
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .recovery-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .recovery-pill .pill-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 3px;
    }
    .recovery-pill .pill-val {
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.2;
    }

    /* Dynamic Table Styling with Dark Borders */
    .recovery-table-wrap {
        max-height: calc(100vh - 420px);
        min-height: 380px;
        overflow-x: auto !important;
        overflow-y: auto !important;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        position: relative;
    }
    .recovery-table {
        font-size: .83rem;
        margin-bottom: 0;
        border-collapse: collapse;
        width: 100%;
        min-width: 1100px;
    }
    .recovery-table th, 
    .recovery-table td {
        border: 1px solid #cbd5e1 !important;
        vertical-align: middle;
    }
    .recovery-table thead th {
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
    .recovery-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Inputs with Defined Dark Borders */
    .table-input {
        height: 34px;
        font-size: .84rem;
        font-weight: 700;
        border-radius: 6px;
        border: 1.5px solid #64748b !important;
        color: #0f172a;
        background: #ffffff;
    }
    .table-input:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25) !important;
        outline: none;
    }

    /* Custom File Attachment Button & Badge */
    .file-upload-btn-wrap {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    .file-upload-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        z-index: 2;
    }
    .file-upload-badge {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 5px 8px;
        font-size: 11px;
        font-weight: 600;
        border: 1.5px dashed #94a3b8;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    .file-upload-badge:hover {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }
    .file-upload-badge.has-file {
        border: 1.5px solid #10b981;
        background: #ecfdf5;
        color: #065f46;
    }

    /* Select2 High-Contrast Integration */
    .select2-container--default .select2-selection--single {
        height: 34px !important;
        border: 1.5px solid #64748b !important;
        border-radius: 6px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        font-size: .83rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    .select2-dropdown {
        border: 1.5px solid #2563eb !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        z-index: 99999 !important;
    }
    .select2-container {
        z-index: 9999 !important;
    }
</style>

<div class="bulk-pay-container">

    <form id="bulkPaymentForm" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="batch_no" value="{{ $defaultBatchNo }}">

        {{-- TOP META & CONTROLS CARD --}}
        <div class="card recovery-card-dark mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 mb-3 border-bottom" style="border-bottom: 1.5px solid #e2e8f0 !important;">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center">
                            <i class="fas fa-hand-holding-usd text-primary me-2"></i>Bulk Customer Payment Receipt & Recovery
                        </h5>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-semibold font-monospace" style="font-size: 12px; border-radius: 6px;">
                            <i class="fas fa-receipt me-1"></i>{{ $defaultBatchNo }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('customer-payments.batches') }}" class="btn btn-outline-secondary btn-sm px-3 fw-semibold" style="border-radius: 6px; font-size: .82rem; border: 1.5px solid #cbd5e1;">
                            <i class="fas fa-history me-1"></i> Batch History
                        </a>
                        <a href="{{ route('customer.payments') }}" class="btn btn-outline-primary btn-sm px-3 fw-semibold" style="border-radius: 6px; font-size: .82rem; border: 1.5px solid #3b82f6;">
                            <i class="fas fa-user-check me-1"></i> Single Payment Mode
                        </a>
                    </div>
                </div>

                {{-- Header Filter / Batch Setup Fields --}}
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label mb-1 fw-bold text-secondary small">
                            <i class="fas fa-calendar-alt text-primary me-1"></i>Receipt Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="payment_date" id="batchPaymentDate" class="form-control form-control-sm fw-bold table-input" value="{{ $defaultDate }}" required>
                    </div>

                    <div class="col-md-9">
                        <label class="form-label mb-1 fw-bold text-secondary small">
                            <i class="fas fa-route text-primary me-1"></i>Batch General Note / Recovery Route
                        </label>
                        <input type="text" name="batch_note" id="batchNoteInput" class="form-control form-control-sm table-input" placeholder="e.g. Market recovery by sales officer, route collection, counter receipt...">
                    </div>
                </div>

            </div>
        </div>

    {{-- METRIC SUMMARY PILL BAR --}}
    <div class="card recovery-card-dark mb-3">
        <div class="card-body p-2.5">
            <div class="recovery-pill-bar">
                
                <div class="recovery-pill" style="background: #ffffff; border: 1.5px solid #64748b;">
                    <div class="pill-label text-secondary"><i class="fas fa-users me-1"></i>Total Customers</div>
                    <div class="pill-val text-dark" id="pillCustomerCount">0</div>
                </div>

                <div class="recovery-pill" style="background: #ecfdf5; border: 1.5px solid #10b981;">
                    <div class="pill-label text-success"><i class="fas fa-money-bill-wave me-1"></i>Total Collected</div>
                    <div class="pill-val text-success" id="pillTotalAmount">PKR 0</div>
                </div>

                <div class="recovery-pill" style="background: #eff6ff; border: 1.5px solid #3b82f6;">
                    <div class="pill-label text-primary"><i class="fas fa-wallet me-1"></i>Cash Collection</div>
                    <div class="pill-val text-primary" id="pillCashAmount">PKR 0</div>
                </div>

                <div class="recovery-pill" style="background: #f5f3ff; border: 1.5px solid #8b5cf6;">
                    <div class="pill-label" style="color: #6d28d9;"><i class="fas fa-university me-1"></i>Bank / Online</div>
                    <div class="pill-val" style="color: #6d28d9;" id="pillBankAmount">PKR 0</div>
                </div>

                <div class="recovery-pill" style="background: #fff1f2; border: 1.5px solid #f43f5e;">
                    <div class="pill-label" style="color: #be123c;"><i class="fas fa-chart-line-down me-1"></i>Total Receivables Decreased</div>
                    <div class="pill-val" style="color: #be123c;" id="pillNetDecreased">PKR 0</div>
                </div>

            </div>
        </div>
    </div>

    {{-- DYNAMIC GRID TABLE CARD --}}
    <div class="card recovery-card-dark mb-3">
        <div class="card-header bg-white py-2.5 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2 border-bottom" style="border-radius: 12px 12px 0 0; border-bottom: 1.5px solid #cbd5e1 !important;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark fs-6">
                    <i class="fas fa-list-check text-primary me-1.5"></i>Batch Collection Grid Entries
                </span>
                <span class="badge bg-light text-secondary border small" id="rowCountBadge">0 Rows</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-xs btn-outline-primary py-1.5 px-3 fw-bold" id="btnAddRow" style="font-size: 11.5px; border-radius: 6px; border: 1.5px solid #3b82f6;">
                    <i class="fas fa-plus-circle me-1"></i> Add Customer Row <kbd class="bg-primary text-white border-0 ms-1 px-1.5 py-0.5" style="font-size:9.5px;">Alt + A</kbd>
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-1.5 px-3 fw-bold" id="btnClearAllRows" style="font-size: 11.5px; border-radius: 6px; border: 1.5px solid #64748b;">
                    <i class="fas fa-eraser me-1"></i> Clear Grid
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="recovery-table-wrap">
                <table class="table align-middle recovery-table" id="recoveryTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 38px;">#</th>
                            <th style="min-width: 250px;">CUSTOMER NAME & CODE</th>
                            <th class="text-center" style="width: 135px; background-color: #1e293b !important; color:#ffffff !important;">CURRENT BALANCE</th>
                            <th class="text-center" style="width: 155px; background-color: #1e3a8a !important; color:#ffffff !important;">AMOUNT RECEIVED (PKR)</th>
                            <th class="text-center" style="width: 135px; background-color: #1e293b !important; color:#ffffff !important;">NEW BALANCE</th>
                            <th class="text-center" style="width: 125px;">PAYMENT TYPE</th>
                            <th class="text-center" style="width: 200px; background-color: #064e3b !important; color:#ffffff !important;">DEPOSIT ACCOUNT</th>
                            <th class="text-center" style="width: 160px;">SLIP / RECEIPT ATTACH</th>
                            <th class="text-center" style="width: 50px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="recoveryTableBody">
                        {{-- Rows generated dynamically via JavaScript --}}
                    </tbody>
                    <tfoot class="bg-light fw-bold" style="border-top: 2px solid #0f172a !important;">
                        <tr>
                            <td colspan="3" class="text-end text-dark pe-3">GRAND TOTAL COLLECTION:</td>
                            <td class="text-center font-monospace fs-6 text-primary fw-extrabold" id="footTotalReceived">PKR 0.00</td>
                            <td colspan="5" class="text-muted small ps-3">Total recovery amount will be credited to individual customer ledgers & selected deposit accounts.</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- SUBMIT BAR --}}
        <div class="card-footer bg-white py-3 px-3 d-flex align-items-center justify-content-between border-top" style="border-top: 1.5px solid #cbd5e1 !important; border-radius: 0 0 12px 12px;">
            <div class="text-muted small">
                <i class="fas fa-info-circle text-primary me-1"></i>
                Saving this batch will automatically generate individual receipt vouchers, update ledgers, and enable consolidated print.
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" id="btnSubmitBatch" class="btn btn-primary px-4 py-2 fw-bold d-flex align-items-center gap-2" style="border-radius: 8px; font-size: 14px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">
                    <i class="fas fa-save"></i>
                    <span>Save & Generate Receipts</span>
                </button>
            </div>
        </div>

    </form>
</div>

{{-- ATTACHMENT LIGHTBOX / PREVIEW MODAL --}}
<div class="modal fade" id="attachmentPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: 1.5px solid #334155; overflow: hidden;">
            <div class="modal-header py-2.5 px-3 bg-dark text-white">
                <h6 class="modal-title fw-bold d-flex align-items-center" id="previewModalTitle">
                    <i class="fas fa-paperclip me-2 text-primary"></i>Attachment Preview
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center" id="previewModalContent" style="min-height: 350px; background: #f8fafc;">
                <!-- Dynamically injected preview -->
            </div>
        </div>
    </div>
</div>

{{-- POST-SUBMISSION SUCCESS ACTION MODAL --}}
<div class="modal fade" id="batchSuccessModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4" style="border-radius: 14px; border: 1.5px solid #cbd5e1;">
            <div class="mb-3">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 28px;">
                    <i class="fas fa-check"></i>
                </div>
            </div>
            <h4 class="fw-bold text-dark mb-1">Batch Saved Successfully!</h4>
            <p class="text-secondary small mb-3">
                Batch Reference: <strong class="text-primary font-monospace" id="modalBatchNo"></strong><br>
                Total <strong id="modalReceiptCount"></strong> customer receipts and ledger entries updated.
            </p>

            <div class="d-flex flex-column gap-2.5 mb-3">
                {{-- Consolidated Recovery Sheet Button --}}
                <a id="btnBatchPrintSheet" href="#" target="_blank" class="btn btn-primary fw-bold py-2.5 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="border-radius: 8px; font-size: 14px;">
                    <i class="fas fa-file-invoice fs-5"></i> Print Consolidated Recovery Sheet (A4)
                </a>

                {{-- Print All Individual Receipts in one click --}}
                <button type="button" id="btnPrintAllIndividual" class="btn btn-outline-dark fw-bold py-2 d-flex align-items-center justify-content-center gap-2" style="border-radius: 8px; font-size: 13px; border: 1.5px solid #334155;">
                    <i class="fas fa-print text-primary"></i> Print All Customer Receipts (Open All)
                </button>

                {{-- Direct Itemized List of Customer Receipts --}}
                <div class="border rounded p-2 text-start bg-light" style="border: 1.5px solid #cbd5e1 !important; border-radius: 10px;">
                    <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                        <span class="small fw-bold text-dark d-flex align-items-center">
                            <i class="fas fa-receipt text-primary me-1.5"></i>Individual Receipts by Customer:
                        </span>
                        <span class="badge bg-secondary text-white font-monospace" id="modalReceiptCountBadge">0 Receipts</span>
                    </div>

                    <div id="individualReceiptsList" class="d-flex flex-column gap-1.5" style="max-height: 190px; overflow-y: auto;">
                        <!-- Injected customer receipt rows -->
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-center gap-2 pt-2 border-top">
                <a href="{{ route('customer-payments.batches') }}" class="btn btn-light fw-bold px-3 py-1.5" style="border-radius: 6px; font-size: 13px;">
                    <i class="fas fa-list me-1"></i> View Batch History
                </a>
                <button type="button" class="btn btn-dark fw-bold px-3 py-1.5" onclick="window.location.reload()" style="border-radius: 6px; font-size: 13px;">
                    <i class="fas fa-plus me-1"></i> Create Another Batch
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
{{-- PASS CUSTOMER MASTER DATA TO JAVASCRIPT --}}
<script>
    const CUSTOMERS_MASTER = {!! json_encode($customerList) !!};
    const HEADS_MASTER = {!! json_encode($headsData) !!};
</script>

<script>
$(document).ready(function() {

    let rowCount = 0;

    // Safe HTML Escaping
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper: Format Currency PKR
    function formatPKR(val) {
        return 'PKR ' + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Add a new Row to Grid
    function addGridRow(defaultCustomer = null) {
        rowCount++;
        let rowId = 'row_' + rowCount;

        // Customer Options
        let customerOptions = '<option value="">-- Select Customer --</option>';
        if (Array.isArray(CUSTOMERS_MASTER)) {
            CUSTOMERS_MASTER.forEach(c => {
                let selected = (defaultCustomer && defaultCustomer == c.id) ? 'selected' : '';
                customerOptions += `<option value="${c.id}" data-balance="${c.balance}" data-code="${escapeHtml(c.code)}" ${selected}>${escapeHtml(c.name)} (${escapeHtml(c.code)})</option>`;
            });
        }

        // Payment Type (Heads: Cash & Bank only)
        let headOptions = '';
        if (Array.isArray(HEADS_MASTER)) {
            HEADS_MASTER.forEach((h, idx) => {
                headOptions += `<option value="${escapeHtml(h.name)}" data-head-id="${h.id}" ${idx === 0 ? 'selected' : ''}>${escapeHtml(h.name)}</option>`;
            });
        }

        // Deposit Accounts for the first/default head
        let firstHeadAccounts = (Array.isArray(HEADS_MASTER) && HEADS_MASTER.length > 0 && HEADS_MASTER[0].accounts) ? HEADS_MASTER[0].accounts : [];
        let accountOptions = '';
        firstHeadAccounts.forEach((acc, aIdx) => {
            accountOptions += `<option value="${acc.id}" ${aIdx === 0 ? 'selected' : ''}>${escapeHtml(acc.title)} (${escapeHtml(acc.code)})</option>`;
        });

        let html = `
            <tr id="${rowId}" class="recovery-row" data-row-id="${rowCount}">
                <td class="text-center text-muted fw-bold row-index" style="font-size:11px;">${rowCount}</td>
                
                {{-- Customer Selector --}}
                <td>
                    <select name="payments[${rowCount}][customer_id]" class="form-select form-select-sm customer-select" required>
                        ${customerOptions}
                    </select>
                </td>

                {{-- Current Balance Badge --}}
                <td class="text-center">
                    <span class="badge border font-monospace current-balance-badge" style="font-size: 12px; background:#f1f5f9; color:#0f172a; border-color:#cbd5e1 !important;">
                        PKR 0.00
                    </span>
                    <input type="hidden" class="hidden-current-balance" value="0">
                </td>

                {{-- Amount Received --}}
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text fw-bold" style="background:#e2e8f0; border: 1.5px solid #64748b; border-right:none; font-size:11px;">Rs</span>
                        <input type="number" min="0.01" step="0.01" name="payments[${rowCount}][amount]" class="form-control table-input input-amount" placeholder="0.00" required style="border-radius: 0 6px 6px 0;">
                    </div>
                </td>

                {{-- New Balance --}}
                <td class="text-center">
                    <span class="badge border font-monospace new-balance-badge" style="font-size: 12px; background:#f8fafc; color:#64748b; border-color:#cbd5e1 !important;">
                        PKR 0.00
                    </span>
                </td>

                {{-- Payment Type (Head) --}}
                <td>
                    <select name="payments[${rowCount}][payment_method]" class="form-select form-select-sm table-input select-head fw-bold text-dark">
                        ${headOptions}
                    </select>
                </td>

                {{-- Deposit Account (Category-wise dynamic dropdown) --}}
                <td>
                    <select name="payments[${rowCount}][account_id]" class="form-select form-select-sm table-input select-account fw-semibold" required>
                        ${accountOptions}
                    </select>
                </td>

                {{-- Slip Attachment --}}
                <td>
                    <div class="file-upload-btn-wrap">
                        <input type="file" name="attachments[${rowCount}]" class="file-upload-input" accept="image/*,.pdf" data-row-id="${rowCount}">
                        <div class="file-upload-badge" id="fileBadge_${rowCount}">
                            <span><i class="fas fa-paperclip me-1"></i>Attach Slip</span>
                            <i class="fas fa-plus text-muted" style="font-size: 9px;"></i>
                        </div>
                    </div>
                </td>

                {{-- Action Delete --}}
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm p-1.5 btn-delete-row" title="Remove Row" style="border-radius:6px; font-size:11px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#recoveryTableBody').append(html);

        // Initialize Select2 cleanly without invalid dropdownParent
        $(`#${rowId} .customer-select`).select2({
            placeholder: '-- Select Customer --',
            allowClear: true,
            width: '100%'
        });

        if (defaultCustomer) {
            $(`#${rowId} .customer-select`).val(defaultCustomer).trigger('change');
        }

        updateRowIndices();
        recalculateTotals();
    }

    // Update row numbers and badges
    function updateRowIndices() {
        let rows = $('#recoveryTableBody tr');
        rows.each(function(idx) {
            $(this).find('.row-index').text(idx + 1);
        });
        $('#rowCountBadge').text(rows.length + ' Rows');
        $('#pillCustomerCount').text(rows.length);
    }

    // Customer Selection Change Listener
    $(document).on('change', '.customer-select', function() {
        let tr = $(this).closest('tr');
        let selected = $(this).find('option:selected');
        let balance = parseFloat(selected.data('balance') || 0);

        tr.find('.hidden-current-balance').val(balance);
        tr.find('.current-balance-badge').text(formatPKR(balance));

        if (balance > 0) {
            tr.find('.current-balance-badge').css({'background': '#fee2e2', 'color': '#991b1b', 'border-color': '#fca5a5'});
        } else if (balance < 0) {
            tr.find('.current-balance-badge').css({'background': '#ecfdf5', 'color': '#065f46', 'border-color': '#a7f3d0'});
        } else {
            tr.find('.current-balance-badge').css({'background': '#f1f5f9', 'color': '#0f172a', 'border-color': '#cbd5e1'});
        }

        recalculateRowBalance(tr);
    });

    // Amount Input Listener
    $(document).on('input change', '.input-amount', function() {
        let tr = $(this).closest('tr');
        recalculateRowBalance(tr);
        recalculateTotals();
    });

    // Payment Type (Head) Change Listener -> Updates Row's Deposit Account Options Dynamically
    $(document).on('change', '.select-head', function() {
        let tr = $(this).closest('tr');
        let selectedHeadName = $(this).val();
        let headObj = Array.isArray(HEADS_MASTER) ? HEADS_MASTER.find(h => h.name.toLowerCase() === selectedHeadName.toLowerCase()) : null;
        let accSelect = tr.find('.select-account');

        accSelect.empty();
        if (headObj && headObj.accounts && headObj.accounts.length > 0) {
            headObj.accounts.forEach((acc, idx) => {
                accSelect.append(new Option(`${acc.title} (${acc.code})`, acc.id, idx === 0, idx === 0));
            });
        } else {
            accSelect.append(new Option('-- No Account Found --', ''));
        }

        recalculateTotals();
    });

    // Recalculate New Balance for a Single Row
    function recalculateRowBalance(tr) {
        let cur = parseFloat(tr.find('.hidden-current-balance').val() || 0);
        let amt = parseFloat(tr.find('.input-amount').val() || 0);
        let rem = cur - amt;

        let badge = tr.find('.new-balance-badge');
        badge.text(formatPKR(rem));

        if (rem === 0 && cur > 0) {
            badge.css({'background': '#dcfce7', 'color': '#166534', 'border-color': '#86efac'}).html('<i class="fas fa-check-circle me-1"></i>Settled (0.00)');
        } else if (rem > 0) {
            badge.css({'background': '#fef2f2', 'color': '#991b1b', 'border-color': '#fecaca'});
        } else {
            badge.css({'background': '#eff6ff', 'color': '#1e40af', 'border-color': '#bfdbfe'});
        }
    }

    // Recalculate Top Metric Summary & Footer
    function recalculateTotals() {
        let totalAmount = 0;
        let cashAmount = 0;
        let bankAmount = 0;

        $('.recovery-row').each(function() {
            let amt = parseFloat($(this).find('.input-amount').val() || 0);
            let head = ($(this).find('.select-head').val() || 'Cash').toLowerCase();

            totalAmount += amt;
            if (head.includes('cash')) {
                cashAmount += amt;
            } else {
                bankAmount += amt;
            }
        });

        $('#pillTotalAmount').text(formatPKR(totalAmount));
        $('#footTotalReceived').text(formatPKR(totalAmount));
        $('#pillCashAmount').text(formatPKR(cashAmount));
        $('#pillBankAmount').text(formatPKR(bankAmount));
        $('#pillNetDecreased').text(formatPKR(totalAmount));
    }

    // Delete Row Handler
    $(document).on('click', '.btn-delete-row', function() {
        if ($('#recoveryTableBody tr').length <= 1) {
            Swal.fire({
                icon: 'info',
                title: 'Minimum 1 Row Required',
                text: 'You must have at least one payment row in the batch.'
            });
            return;
        }
        $(this).closest('tr').remove();
        updateRowIndices();
        recalculateTotals();
    });

    // Clear All Handler
    $('#btnClearAllRows').on('click', function() {
        Swal.fire({
            title: 'Clear All Rows?',
            text: 'Are you sure you want to reset all payment entries?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, clear all'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#recoveryTableBody').empty();
                rowCount = 0;
                addGridRow();
            }
        });
    });

    // Add Row Handler
    $('#btnAddRow').on('click', function() {
        addGridRow();
    });

    // Keyboard Shortcut Alt + A to Add Row
    $(document).on('keydown', function(e) {
        if (e.altKey && (e.key === 'a' || e.key === 'A')) {
            e.preventDefault();
            addGridRow();
        }
    });

    // File Input Change Listener (preview & badge update)
    $(document).on('change', '.file-upload-input', function(e) {
        let rowId = $(this).data('row-id');
        let file = this.files[0];
        let badge = $(`#fileBadge_${rowId}`);

        if (file) {
            let filename = file.name;
            if (filename.length > 16) {
                filename = filename.substring(0, 13) + '...';
            }
            badge.addClass('has-file').html(`
                <span title="${file.name}"><i class="fas fa-file-check me-1 text-success"></i>${filename}</span>
                <span class="preview-btn text-primary ms-1" data-row-id="${rowId}" title="Preview File"><i class="fas fa-eye"></i></span>
            `);
        } else {
            badge.removeClass('has-file').html(`
                <span><i class="fas fa-paperclip me-1"></i>Attach Slip</span>
                <i class="fas fa-plus text-muted" style="font-size: 9px;"></i>
            `);
        }
    });

    // File Preview Modal Trigger
    $(document).on('click', '.preview-btn', function(e) {
        e.stopPropagation();
        e.preventDefault();
        let rowId = $(this).data('row-id');
        let input = $(`input[data-row-id="${rowId}"]`)[0];

        if (input && input.files && input.files[0]) {
            let file = input.files[0];
            let reader = new FileReader();

            $('#previewModalTitle').html(`<i class="fas fa-file-alt me-2 text-primary"></i>${file.name}`);

            if (file.type.startsWith('image/')) {
                reader.onload = function(e) {
                    $('#previewModalContent').html(`
                        <img src="${e.target.result}" class="img-fluid rounded border shadow-sm" style="max-height: 500px;" alt="Receipt Slip Preview">
                    `);
                    $('#attachmentPreviewModal').modal('show');
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                reader.onload = function(e) {
                    $('#previewModalContent').html(`
                        <iframe src="${e.target.result}" style="width:100%; height:500px; border:none; border-radius:8px;"></iframe>
                    `);
                    $('#attachmentPreviewModal').modal('show');
                };
                reader.readAsDataURL(file);
            } else {
                $('#previewModalContent').html(`
                    <div class="py-5 text-muted">
                        <i class="fas fa-file fa-3x mb-2"></i>
                        <p>Preview not available for this file format.<br>File: <strong>${file.name}</strong></p>
                    </div>
                `);
                $('#attachmentPreviewModal').modal('show');
            }
        }
    });

    // Submit Form Handler via AJAX
    $('#bulkPaymentForm').on('submit', function(e) {
        e.preventDefault();

        let rows = $('.recovery-row');
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Empty Grid', text: 'Please add at least one customer row.' });
            return;
        }

        let hasInvalid = false;
        rows.each(function() {
            let custId = $(this).find('.customer-select').val();
            let amt = parseFloat($(this).find('.input-amount').val() || 0);
            let accId = $(this).find('.select-account').val();
            if (!custId || amt <= 0 || !accId) {
                hasInvalid = true;
            }
        });

        if (hasInvalid) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Entries',
                text: 'Please select a Customer, enter a valid positive Amount, and select a Deposit Account for every row.'
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Bulk Payment Save?',
            text: `Are you sure you want to process collection for ${rows.length} customers? Ledgers will be updated immediately.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Save & Process',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                let formData = new FormData($('#bulkPaymentForm')[0]);
                return $.ajax({
                    url: "{{ route('customer-payments.bulk.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).then(response => {
                    return response;
                }).catch(error => {
                    let msg = 'Failed to save payments.';
                    if (error.responseJSON && error.responseJSON.message) {
                        msg = error.responseJSON.message;
                    }
                    Swal.showValidationMessage(msg);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value.success) {
                let res = result.value;

                $('#modalBatchNo').text(res.batch_no);
                $('#modalReceiptCount').text(res.count);
                $('#btnBatchPrintSheet').attr('href', res.batch_print_url);

                let listHtml = '';
                if (res.payments && res.payments.length > 0) {
                    res.payments.forEach(p => {
                        listHtml += `
                            <div class="d-flex align-items-center justify-content-between p-2 bg-white border rounded shadow-sm" style="border: 1px solid #cbd5e1 !important; border-radius: 6px;">
                                <div class="text-truncate me-2 text-start" style="max-width: 250px;">
                                    <span class="badge bg-dark font-monospace text-white px-1.5 py-0.5 me-1" style="font-size: 10.5px;">${p.receipt_no}</span>
                                    <strong class="text-dark small">${escapeHtml(p.customer_name)}</strong>
                                    <div class="text-success font-monospace fw-bold" style="font-size: 11px;">${formatPKR(p.amount)}</div>
                                </div>
                                <a href="${p.receipt_url}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2.5 fw-bold d-flex align-items-center gap-1 individual-receipt-link" style="border-radius: 5px; font-size: 11.5px; border: 1.5px solid #2563eb;">
                                    <i class="fas fa-print"></i> Print
                                </a>
                            </div>
                        `;
                    });
                }
                $('#individualReceiptsList').html(listHtml);
                $('#modalReceiptCountBadge').text((res.payments ? res.payments.length : 0) + ' Receipts');

                $('#batchSuccessModal').modal('show');
            }
        });
    });

    // Open All Individual Receipts in separate tabs
    $(document).on('click', '#btnPrintAllIndividual', function(e) {
        e.preventDefault();
        let links = $('.individual-receipt-link');
        if (links.length === 0) return;
        links.each(function() {
            let url = $(this).attr('href');
            if (url && url !== '#') {
                window.open(url, '_blank');
            }
        });
    });

    // Populate initial 3 rows on page load
    addGridRow();
    addGridRow();
    addGridRow();

});
</script>
@endsection
