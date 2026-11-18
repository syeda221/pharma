@extends('admin_panel.layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}">

<style>
    :root {
        --iv-primary: #e11d48;
        --iv-primary-hover: #be123c;
        --iv-bg: #f8fafc;
        --iv-border: #e2e8f0;
        --iv-text: #1e293b;
        --iv-muted: #64748b;
    }

    .iv-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
        border: 1px solid var(--iv-border);
        padding: 26px 30px;
    }

    .iv-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--iv-border);
    }

    .iv-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--iv-text);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .iv-title i {
        color: var(--iv-primary);
        background: #ffe4e6;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 1.25rem;
    }

    .iv-section-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--iv-primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 22px 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .iv-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--iv-border);
    }

    .iv-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--iv-muted);
        margin-bottom: 5px;
    }

    .iv-input {
        background: #fff;
        border: 1px solid var(--iv-border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 0.92rem;
        color: var(--iv-text);
        transition: all 0.2s ease;
        width: 100%;
    }
    .iv-input:focus {
        border-color: var(--iv-primary);
        box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
        outline: none;
    }
    .iv-input::placeholder { color: #cbd5e1; }

    select.iv-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.65rem center;
        background-repeat: no-repeat;
        background-size: 1.4em 1.4em;
        padding-right: 2.5rem;
    }

    .btn-iv-primary {
        background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 11px 32px;
        font-weight: 600;
        font-size: 0.98rem;
        box-shadow: 0 3px 8px rgba(225, 29, 72, 0.3);
        transition: all 0.2s;
    }
    .btn-iv-primary:hover {
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(225, 29, 72, 0.4);
    }

    .btn-iv-secondary {
        background: #f1f5f9;
        color: var(--iv-text) !important;
        border: 1px solid var(--iv-border);
        border-radius: 8px;
        padding: 9px 22px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .btn-iv-secondary:hover { background: #e2e8f0; }

    .balance-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.2px;
    }
    .balance-dr { background: #fef2f2; color: #dc2626; border: 1.5px solid #fca5a5; }
    .balance-cr { background: #f0fdf4; color: #16a34a; border: 1.5px solid #86efac; }
    .balance-neutral { background: #f1f5f9; color: #64748b; border: 1.5px solid #cbd5e1; }

    /* Attachment Box */
    .attachment-box {
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 18px;
        text-align: center;
        background-color: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .attachment-box:hover {
        border-color: var(--iv-primary);
        background-color: #fff1f2;
    }

    /* Select2 custom styling */
    .select2-container--default .select2-selection--single {
        background-color: #fff !important;
        border: 1px solid var(--iv-border) !important;
        border-radius: 8px !important;
        height: 42px !important;
        padding: 6px 10px !important;
        font-size: 0.92rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: var(--iv-text) !important;
        font-weight: 600;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 10px !important;
    }

    /* Disable number input spin buttons */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] { 
        -moz-appearance: textfield; 
        appearance: textfield;
    }

    .iv-table {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--iv-border);
    }
    .iv-table thead th {
        background: #0f172a;
        color: #fff;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 11px 14px;
        text-align: center;
        border: none;
    }
    .iv-table tbody td {
        padding: 10px 14px;
        vertical-align: middle;
        border-color: var(--iv-border);
        font-size: 13px;
    }
</style>

<div class="main-content">
    <div class="main-content-inner" style="padding: 15px 20px;">
        <div class="container-fluid p-0">

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 10px; background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 10px; background-color: #fff1f2; color: #9f1239; border: 1px solid #fecdd3;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            
                {{-- ⚙ Account Settings (Collapsible) --}}
                <div class="d-flex justify-content-end mb-2">
                    <a href="#dfSettingsPanelInv" data-toggle="collapse" style="font-size:12px; color:#64748b; text-decoration:none; cursor:pointer;">
                        <i class="bi bi-gear-fill me-1"></i> Account Settings
                    </a>
                </div>
                <div class="collapse" id="dfSettingsPanelInv">
                    <form method="POST" action="{{ route('doctor-funds.settings.save') }}" id="dfSettingsFormInv">
                        @csrf
                        <div class="d-flex align-items-center p-3 rounded border mb-3" style="background:#f8fafc; gap:12px;">
                            <label style="white-space:nowrap; font-size:12px; font-weight:700; color:#334155; margin-bottom:0;">Fund Account Name:</label>
                            <input type="text" name="doctor_fund_account_name" class="form-control form-control-sm"
                                value="{{ $fundAccountName }}"
                                placeholder="e.g. SHAHZAIB RANA / Meezan Doctor Fund Account"
                                style="max-width:360px; border-radius:7px;">
                            <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap; border-radius:7px;">
                                <i class="bi bi-save me-1"></i> Save
                            </button>
                            <small class="text-muted" style="white-space:nowrap; font-size:11px;">This name auto-fills the account field on all vouchers.</small>
                        </div>
                    </form>
                </div>

                <form action="{{ route('doctor-funds.investment.store') }}" method="POST" enctype="multipart/form-data" id="investmentVoucherForm">
                @csrf

                <div class="iv-card">
                    {{-- Header --}}
                    <div class="iv-header">
                        <div class="iv-title">
                            <i class="bi bi-person-heart"></i>
                            <div>
                                <span>Doctor Investment & Expense Voucher</span>
                                <div class="text-muted" style="font-size: 0.8rem; font-weight: 500;">
                                    Record promotional investment, clinic support, or incentive spent on doctors (Isolated Fund Account)
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="gap: 10px;">
                            <div class="balance-badge {{ $currentFundBalance >= 0 ? 'balance-cr' : 'balance-dr' }}">
                                Available Fund Balance: PKR {{ number_format($currentFundBalance, 2) }}
                            </div>
                            <a href="{{ route('doctor-funds.index') }}" class="btn btn-iv-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                            </a>
                        </div>
                    </div>

                    {{-- Section 1: Voucher & Date Details --}}
                    <div class="iv-section-label">Voucher & Date Details</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="iv-label">Voucher No <small class="text-muted">(Auto)</small></label>
                            <input type="text" class="iv-input" style="background: #f1f5f9; font-weight: 700; color: #0f172a;" name="voucher_no_display" value="{{ $nextVoucherNo }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="iv-label">Investment Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="iv-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="iv-label">Dedicated Bank Account</label>
                            <input type="text" name="bank_account_name" id="bank_account_name_inv" class="iv-input" value="{{ $fundAccountName }}" placeholder="e.g. Meezan Bank - Doctor Fund">
                        </div>
                        <div class="col-md-3">
                            <label class="iv-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_method" class="iv-input" required>
                                <option value="Bank Transfer">Bank Transfer / Online</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    {{-- Section 2: Doctor & Zone Information with Current Balance --}}
                    <div class="iv-section-label">Doctor & Distributor (Zone)</div>
                    <div class="row g-3 mb-3 align-items-start">
                        <div class="col-md-4">
                            <label class="iv-label">Distributor / Zone <span class="text-danger">*</span></label>
                            <select name="zone_id" id="inv_zone_id" class="iv-input select2" required>
                                <option value="">-- Select Zone --</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->zone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="iv-label">Doctor (Customer) <span class="text-danger">*</span></label>
                            <select name="customer_id" id="inv_customer_id" class="iv-input select2" required>
                                <option value="">-- Select Zone First --</option>
                            </select>
                        </div>

                        {{-- Doctor Balance Display Badge --}}
                        <div class="col-md-4">
                            <label class="iv-label">Doctor Current Ledger Balance</label>
                            <div id="invDoctorBalanceDisplay" class="balance-badge balance-neutral text-center font-weight-bold" style="width: 100%; font-size: 1.05rem;">
                                0.00 Dr
                            </div>
                            <small class="text-muted text-center d-block mt-1" style="font-size: 11px;">Doctor's current ERP ledger balance</small>
                        </div>
                    </div>

                    {{-- Section 3: Financial & Investment Category --}}
                    <div class="iv-section-label">Investment Details & Category</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="iv-label">Investment Amount (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="background:#fff1f2; color:#be123c; border-color:var(--iv-border);">PKR</span>
                                </div>
                                <input type="number" step="0.01" min="1" name="amount" class="iv-input font-weight-bold text-danger" placeholder="0.00" required style="font-size: 1.25rem;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="iv-label">Investment Category <span class="text-danger">*</span></label>
                            <select name="purpose_tag" class="iv-input" required>
                                <option value="Promotional / Marketing Support">Promotional / Marketing Support</option>
                                <option value="Clinic Renovation & Assistance">Clinic Renovation & Assistance</option>
                                <option value="Medical Equipment Sponsorship">Medical Equipment Sponsorship</option>
                                <option value="Doctor Direct Incentive">Doctor Direct Incentive</option>
                                <option value="Conference & CME Sponsorship">Conference & CME Sponsorship</option>
                                <option value="Gifts, Books & Samples">Gifts, Books & Samples</option>
                                <option value="Travel & Hospitality">Travel & Hospitality</option>
                                <option value="Other Promotional Expense">Other Promotional Expense</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="iv-label">Cheque / Voucher / Ref No.</label>
                            <input type="text" name="reference_no" class="iv-input" placeholder="e.g. TR-54910 or Cheque #1029">
                        </div>
                        <div class="col-12 mt-2">
                            <label class="iv-label">Investment Notes / Remarks</label>
                            <input type="text" name="narration" id="inv_narration" class="iv-input" placeholder="Purpose, commitment terms, doctor notes...">
                        </div>
                    </div>

                    {{-- Section 4: Attachment Slip / Document --}}
                    <div class="iv-section-label">Supporting Document / Receipt Slip</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="attachment-box" onclick="document.getElementById('inv_attachment_input').click();">
                                <i class="bi bi-cloud-arrow-up-fill text-danger" style="font-size: 2.2rem;"></i>
                                <div class="font-weight-bold text-dark mt-1" id="inv_file_label" style="font-size: 0.95rem;">
                                    Click here to browse or drag & drop receipt / slip / approval document
                                </div>
                                <small class="text-muted">Supports JPG, PNG, WEBP, or PDF files (Max: 10MB)</small>
                                <input type="file" name="attachment" id="inv_attachment_input" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display: none;" onchange="handleFilePreview(this, 'inv_file_label', 'inv_preview_container');">
                            </div>
                            <div id="inv_preview_container" class="mt-2 text-center" style="display: none;"></div>
                        </div>
                    </div>

                    {{-- Form Footer Actions --}}
                    <div class="d-flex justify-content-end align-items-center gap-3 pt-3 border-top" style="gap: 12px;">
                        <a href="{{ route('doctor-funds.index') }}" class="btn btn-iv-secondary">Cancel</a>
                        <button type="submit" class="btn btn-iv-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save Doctor Investment Voucher
                        </button>
                    </div>
                </div>
            </form>

            {{-- Recent Doctor Investments History --}}
            <div class="iv-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="bi bi-clock-history me-1 text-danger"></i> Recent Doctor Investment Vouchers (Last 10)
                    </h6>
                    <a href="{{ route('doctor-funds.index', ['type' => 'investment']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                        View All in Ledger
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table iv-table mb-0">
                        <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Distributor (Zone)</th>
                                <th>Doctor (Customer)</th>
                                <th>Category</th>
                                <th>Payment Mode</th>
                                <th class="text-right">Amount Invested</th>
                                <th class="text-center">Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvestments as $item)
                                <tr>
                                    <td class="font-weight-bold text-dark text-center">{{ $item->voucher_no }}</td>
                                    <td class="text-center">{{ $item->transaction_date->format('d M, Y') }}</td>
                                    <td><span class="font-weight-bold text-dark">{{ $item->zone ? $item->zone->zone : 'N/A' }}</span></td>
                                    <td><span class="text-primary font-weight-bold">{{ $item->customer ? $item->customer->customer_name : 'N/A' }}</span></td>
                                    <td><span class="badge badge-info" style="font-size: 11px;">{{ $item->purpose_tag }}</span></td>
                                    <td><span class="badge badge-light border">{{ $item->payment_method }}</span></td>
                                    <td class="text-right font-weight-bold text-danger">-PKR {{ number_format($item->amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($item->attachment)
                                            <a href="{{ $item->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-danger p-1 px-2" style="border-radius: 6px; font-size: 11px;">
                                                <i class="bi bi-paperclip"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No doctor investment vouchers recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    var invDoctorsMap = {};

    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        // Dynamic Doctor filtering on Zone selection
        $('#inv_zone_id').on('change', function() {
            var zoneId = $(this).val();
            var $doctorSelect = $('#inv_customer_id');
            $doctorSelect.html('<option value="">Loading doctors in zone...</option>');
            invDoctorsMap = {};
            resetInvBalanceDisplay();

            if (zoneId) {
                $.ajax({
                    url: "{{ url('/doctor-funds/doctors-by-zone') }}/" + zoneId,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        $doctorSelect.html('<option value="">-- Select Doctor --</option>');
                        if (res.doctors && res.doctors.length > 0) {
                            $.each(res.doctors, function(idx, doc) {
                                invDoctorsMap[doc.id] = doc;
                                $doctorSelect.append('<option value="' + doc.id + '">' + doc.text + '</option>');
                            });
                        } else {
                            $doctorSelect.html('<option value="">No doctors registered in this zone</option>');
                        }
                        if (typeof $.fn.select2 !== 'undefined') {
                            $doctorSelect.trigger('change');
                        }
                    },
                    error: function() {
                        $doctorSelect.html('<option value="">Error loading doctors</option>');
                    }
                });
            } else {
                $doctorSelect.html('<option value="">-- Select Zone First --</option>');
            }
        });

        // Doctor selection -> Show Balance
        $('#inv_customer_id').on('change', function() {
            var docId = $(this).val();
            if (docId && invDoctorsMap[docId]) {
                var doc = invDoctorsMap[docId];
                updateInvDoctorBalance(doc.balance, doc.balance_formatted, doc.customer_name);
            } else if (docId) {
                $.ajax({
                    url: "{{ url('/doctor-funds/customer-balance') }}/" + docId,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        updateInvDoctorBalance(res.balance, res.balance_formatted, res.customer_name);
                    }
                });
            } else {
                resetInvBalanceDisplay();
            }
        });
    });

    function updateInvDoctorBalance(bal, formatted, docName) {
        var $badge = $('#invDoctorBalanceDisplay');
        $badge.removeClass('balance-neutral balance-cr balance-dr');

        if (bal > 0) {
            $badge.addClass('balance-dr');
            $badge.html(formatted + ' <small>(Receivable)</small>');
        } else if (bal < 0) {
            $badge.addClass('balance-cr');
            $badge.html(formatted + ' <small>(Advance/Credit)</small>');
        } else {
            $badge.addClass('balance-neutral');
            $badge.html('0.00 <small>(Nil Balance)</small>');
        }

        if (!$('#inv_narration').val() && docName) {
            $('#inv_narration').val('Investment / Marketing support for ' + docName);
        }
    }

    function resetInvBalanceDisplay() {
        $('#invDoctorBalanceDisplay').removeClass('balance-dr balance-cr').addClass('balance-neutral').html('0.00 Dr');
    }

    function handleFilePreview(input, labelId, previewContainerId) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var fileName = file.name;
            var fileSize = (file.size / 1024 / 1024).toFixed(2);

            $('#' + labelId).html('<span class="text-danger font-weight-bold"><i class="bi bi-check2-circle me-1"></i> Selected: ' + fileName + ' (' + fileSize + ' MB)</span>');

            var ext = fileName.split('.').pop().toLowerCase();
            var $preview = $('#' + previewContainerId);
            $preview.show();

            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $preview.html('<img src="' + e.target.result + '" class="img-thumbnail rounded shadow-sm" style="max-height: 140px;">');
                }
                reader.readAsDataURL(file);
            } else if (ext === 'pdf') {
                $preview.html('<div class="p-2 bg-white rounded border d-inline-block text-danger font-weight-bold"><i class="bi bi-file-earmark-pdf-fill me-1" style="font-size:1.4rem;"></i> PDF Document Attached</div>');
            }
        }
    }
</script>
@endpush
@endsection
