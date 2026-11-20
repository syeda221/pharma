@extends('admin_panel.layout.app')

@section('content')
<link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}">

<style>
    :root {
        --rv-primary: #059669;
        --rv-primary-hover: #047857;
        --rv-bg: #f8fafc;
        --rv-border: #e2e8f0;
        --rv-text: #1e293b;
        --rv-muted: #64748b;
    }

    .rv-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
        border: 1px solid var(--rv-border);
        padding: 26px 30px;
    }

    .rv-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--rv-border);
    }

    .rv-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--rv-text);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rv-title i {
        color: var(--rv-primary);
        background: #d1fae5;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 1.25rem;
    }

    .rv-section-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--rv-primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 22px 0 12px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rv-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--rv-border);
    }

    .rv-label {
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--rv-muted);
        margin-bottom: 5px;
    }

    .rv-input {
        background: #fff;
        border: 1px solid var(--rv-border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 0.92rem;
        color: var(--rv-text);
        transition: all 0.2s ease;
        width: 100%;
    }
    .rv-input:focus {
        border-color: var(--rv-primary);
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        outline: none;
    }
    .rv-input::placeholder { color: #cbd5e1; }

    select.rv-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.65rem center;
        background-repeat: no-repeat;
        background-size: 1.4em 1.4em;
        padding-right: 2.5rem;
    }

    .btn-rv-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff !important;
        border: none;
        border-radius: 8px;
        padding: 11px 32px;
        font-weight: 600;
        font-size: 0.98rem;
        box-shadow: 0 3px 8px rgba(5, 150, 105, 0.3);
        transition: all 0.2s;
    }
    .btn-rv-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 5px 12px rgba(5, 150, 105, 0.4);
    }

    .btn-rv-secondary {
        background: #f1f5f9;
        color: var(--rv-text) !important;
        border: 1px solid var(--rv-border);
        border-radius: 8px;
        padding: 9px 22px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .btn-rv-secondary:hover { background: #e2e8f0; }

    .balance-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.2px;
    }
    .balance-dr { 
        background: #fef2f2; 
        color: #dc2626; 
        border: 1.5px solid #fca5a5; 
    }
    .balance-cr { 
        background: #f0fdf4; 
        color: #16a34a; 
        border: 1.5px solid #86efac; 
    }
    .balance-neutral { 
        background: #f1f5f9; 
        color: #64748b; 
        border: 1.5px solid #cbd5e1; 
    }

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
        border-color: var(--rv-primary);
        background-color: #f0fdf4;
    }

    /* Select2 custom styling */
    .select2-container--default .select2-selection--single {
        background-color: #fff !important;
        border: 1px solid var(--rv-border) !important;
        border-radius: 8px !important;
        height: 42px !important;
        padding: 6px 10px !important;
        font-size: 0.92rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px !important;
        color: var(--rv-text) !important;
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

    .rv-table {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--rv-border);
    }
    .rv-table thead th {
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
    .rv-table tbody td {
        padding: 10px 14px;
        vertical-align: middle;
        border-color: var(--rv-border);
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
                        <a href="#dfSettingsPanelRec" data-toggle="collapse" style="font-size:12px; color:#64748b; text-decoration:none; cursor:pointer;">
                            <i class="bi bi-gear-fill me-1"></i> Account Settings
                        </a>
                    </div>
                    <div class="collapse" id="dfSettingsPanelRec">
                        <form method="POST" action="{{ route('doctor-funds.settings.save') }}" id="dfSettingsFormRec">
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

                <form action="{{ route('doctor-funds.receiving.store') }}" method="POST" enctype="multipart/form-data" id="receivingVoucherForm">
                @csrf

                <div class="rv-card">
                    {{-- Header --}}
                    <div class="rv-header">
                        <div class="rv-title">
                            <i class="bi bi-cash-stack"></i>
                            <div>
                                <span>Distributor Fund Receiving Voucher</span>
                                <div class="text-muted" style="font-size: 0.8rem; font-weight: 500;">
                                    Record recovery received from distributor for doctor supplies (Isolated Fund Account)
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2" style="gap: 10px;">
                            <div class="balance-badge {{ $currentFundBalance >= 0 ? 'balance-cr' : 'balance-dr' }}">
                                Fund Balance: PKR {{ number_format($currentFundBalance, 2) }}
                            </div>
                            <a href="{{ route('doctor-funds.index') }}" class="btn btn-rv-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Ledger
                            </a>
                        </div>
                    </div>

                    {{-- Section 1: Voucher & Date Details --}}
                    <div class="rv-section-label">Voucher & Date Details</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="rv-label">Voucher No <small class="text-muted">(Auto)</small></label>
                            <input type="text" class="rv-input" style="background: #f1f5f9; font-weight: 700; color: #0f172a;" name="voucher_no_display" value="{{ $nextVoucherNo }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="rv-label">Receipt Date <span class="text-danger">*</span></label>
                            <input type="date" name="transaction_date" class="rv-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="rv-label d-flex justify-content-between align-items-center">
                                <span>Dedicated Account / Bank <span class="text-danger">*</span></span>
                                <span class="badge bg-light text-secondary border px-1" style="font-size: 10px; font-weight: 600;">
                                    <i class="bi bi-lock-fill text-success"></i> Fixed
                                </span>
                            </label>
                            <div style="position: relative;">
                                <i class="bi bi-bank2 text-success" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 1.05rem; pointer-events: none; z-index: 2;"></i>
                                <input type="text" name="bank_account_name" id="bank_account_name" class="rv-input"
                                    value="{{ $fundAccountName ?: 'SHAHZAIB RANA' }}" readonly
                                    style="background-color: #f8fafc !important; cursor: not-allowed; font-weight: 700; color: #0f172a; padding-left: 38px;"
                                    title="Doctor Fund Dedicated Account (Locked: SHAHZAIB RANA)">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="bi bi-shield-check text-success"></i> Fixed Account (SHAHZAIB RANA)
                            </small>
                        </div>
                        <div class="col-md-3">
                            <label class="rv-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_method" class="rv-input" required id="payment_method">
                                <option value="Bank Transfer">Bank Transfer / Online</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    {{-- Section 2: Received From (Distributor / Zone & Doctor with Outstanding Balance) --}}
                    <div class="rv-section-label">Received From (Distributor & Doctor Outstanding Balance)</div>
                    <div class="row g-3 mb-3 align-items-start">
                        <div class="col-md-4">
                            <label class="rv-label">Distributor / Zone <span class="text-danger">*</span></label>
                            <select name="zone_id" id="zone_id" class="rv-input select2" required>
                                <option value="">-- Select Distributor Zone --</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->zone }}</option>
                                @endforeach
                            </select>
                            <div id="zoneBalanceInfo" class="mt-1 text-muted" style="font-size: 11px; display: none;"></div>
                        </div>

                        <div class="col-md-4">
                            <label class="rv-label">Doctor (Customer)</label>
                            <select name="customer_id" id="customer_id" class="rv-input select2">
                                <option value="">-- Select Doctor (Or Select Zone First) --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" data-zone="{{ $doc->zone }}">{{ $doc->customer_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Select doctor directly or filter by zone.</small>
                        </div>

                        {{-- Prominent Customer Outstanding Balance Badge --}}
                        <div class="col-md-4">
                            <label class="rv-label">Customer Outstanding Balance (Receivable)</label>
                            <div id="balanceDisplay" class="balance-badge balance-neutral text-center font-weight-bold" style="width: 100%; font-size: 1.05rem;">
                                0.00 Dr
                            </div>
                            <div id="balanceBreakdown" class="text-muted text-center mt-1" style="font-size: 11.5px; display: none;"></div>
                            <div id="remainingBalanceCalc" class="mt-1 text-center font-weight-bold" style="font-size: 12px; display: none;"></div>
                            <input type="hidden" id="currentCustomerBalance" value="0">
                        </div>
                    </div>

                    {{-- Section 3: Financial & Payment Details --}}
                    <div class="rv-section-label">Financial & Payment Details</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="rv-label">Amount Received (PKR) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text font-weight-bold" style="background:#ecfdf5; color:#065f46; border-color:var(--rv-border);">PKR</span>
                                </div>
                                <input type="number" step="0.01" min="1" name="amount" id="amount" class="rv-input font-weight-bold text-success" placeholder="0.00" required style="font-size: 1.25rem;">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="rv-label">Cheque / Slip / Ref No.</label>
                            <input type="text" name="reference_no" class="rv-input" placeholder="e.g. TR-98421 or Slip #004">
                        </div>

                        <div class="col-md-4">
                            <label class="rv-label">Remarks / Narration</label>
                            <input type="text" name="narration" id="narration" class="rv-input" placeholder="Receipt remarks, recovery against sales...">
                        </div>
                    </div>

                    {{-- Section 4: Attachment Slip / Document --}}
                    <div class="rv-section-label">Attachment Slip / Supporting Document</div>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="attachment-box" onclick="document.getElementById('attachment_input').click();">
                                <i class="bi bi-cloud-arrow-up-fill text-success" style="font-size: 2.2rem;"></i>
                                <div class="font-weight-bold text-dark mt-1" id="file_label" style="font-size: 0.95rem;">
                                    Click here to browse or drag & drop slip/document
                                </div>
                                <small class="text-muted">Supports JPG, PNG, WEBP, or PDF files (Max: 10MB)</small>
                                <input type="file" name="attachment" id="attachment_input" accept=".jpg,.jpeg,.png,.webp,.pdf" style="display: none;" onchange="handleFilePreview(this, 'file_label', 'preview_container');">
                            </div>
                            <div id="preview_container" class="mt-2 text-center" style="display: none;"></div>
                        </div>
                    </div>

                    {{-- Form Footer Actions --}}
                    <div class="d-flex justify-content-end align-items-center gap-3 pt-3 border-top" style="gap: 12px;">
                        <a href="{{ route('doctor-funds.index') }}" class="btn btn-rv-secondary">Cancel</a>
                        <button type="submit" class="btn btn-rv-primary">
                            <i class="bi bi-check2-circle me-1"></i> Save Receiving Voucher
                        </button>
                    </div>
                </div>
            </form>

            {{-- Recent Receiving Vouchers History --}}
            <div class="rv-card mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-dark mb-0">
                        <i class="bi bi-clock-history me-1 text-primary"></i> Recent Receiving Vouchers (Last 10)
                    </h6>
                    <a href="{{ route('doctor-funds.index', ['type' => 'receiving']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px;">
                        View All in Ledger
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table rv-table mb-0">
                        <thead>
                            <tr>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Distributor (Zone)</th>
                                <th>Doctor</th>
                                <th>Payment Mode</th>
                                <th class="text-right">Amount Received</th>
                                <th class="text-center">Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentReceivings as $item)
                                <tr>
                                    <td class="font-weight-bold text-dark text-center">{{ $item->voucher_no }}</td>
                                    <td class="text-center">{{ $item->transaction_date->format('d M, Y') }}</td>
                                    <td><span class="font-weight-bold text-dark">{{ $item->zone ? $item->zone->zone : 'N/A' }}</span></td>
                                    <td>{{ $item->customer ? $item->customer->customer_name : 'General Pool' }}</td>
                                    <td><span class="badge badge-light border">{{ $item->payment_method }}</span></td>
                                    <td class="text-right font-weight-bold text-success">+PKR {{ number_format($item->amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($item->attachment)
                                            <a href="{{ $item->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-success p-1 px-2" style="border-radius: 6px; font-size: 11px;">
                                                <i class="bi bi-paperclip"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No receiving vouchers recorded yet.</td>
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
    var doctorsMap = {};

    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        var allDoctorsList = @json($doctors->map(fn($d) => ['id' => $d->id, 'name' => $d->customer_name, 'zone' => $d->zone]));

        function restoreAllDoctorsDropdown(selectedDocId) {
            var $doctorSelect = $('#customer_id');
            $doctorSelect.empty().append('<option value="">-- Select Doctor (Or Select Zone First) --</option>');
            $.each(allDoctorsList, function(idx, doc) {
                var isSel = selectedDocId && doc.id == selectedDocId ? 'selected' : '';
                $doctorSelect.append('<option value="' + doc.id + '" data-zone="' + (doc.zone || '') + '" ' + isSel + '>' + doc.name + '</option>');
            });
            if (typeof $.fn.select2 !== 'undefined') {
                $doctorSelect.trigger('change.select2');
            }
        }

        // Zone selection -> Load Doctors & their Outstanding Balances
        $('#zone_id').on('change', function(e, triggeredFrom) {
            var zoneId = $(this).val();
            var $doctorSelect = $('#customer_id');
            var currentDocId = $doctorSelect.val();

            if (triggeredFrom === 'from_doctor') {
                // Don't wipe doctor dropdown if zone change was triggered by doctor selection
                if (zoneId) {
                    $.ajax({
                        url: "{{ url('/doctor-funds/doctors-by-zone') }}/" + zoneId,
                        type: "GET",
                        dataType: "json",
                        success: function(res) {
                            if (res.zone_total_formatted) {
                                $('#zoneBalanceInfo').html('<i class="bi bi-info-circle me-1"></i> Zone Total Outstanding: <strong>PKR ' + res.zone_total_formatted + '</strong>').show();
                            }
                        }
                    });
                }
                return;
            }

            doctorsMap = {};
            resetBalanceDisplay();

            if (zoneId) {
                $doctorSelect.html('<option value="">Loading doctors for this zone...</option>');
                $.ajax({
                    url: "{{ url('/doctor-funds/doctors-by-zone') }}/" + zoneId,
                    type: "GET",
                    dataType: "json",
                    success: function(res) {
                        $doctorSelect.html('<option value="">-- General Zone Collection (Or Select Doctor) --</option>');
                        if (res.doctors && res.doctors.length > 0) {
                            $.each(res.doctors, function(idx, doc) {
                                doctorsMap[doc.id] = doc;
                                $doctorSelect.append('<option value="' + doc.id + '" data-zone="' + zoneId + '">' + doc.text + '</option>');
                            });
                        }
                        if (res.zone_total_formatted) {
                            $('#zoneBalanceInfo').html('<i class="bi bi-info-circle me-1"></i> Zone Total Outstanding: <strong>PKR ' + res.zone_total_formatted + '</strong>').show();
                        } else {
                            $('#zoneBalanceInfo').hide();
                        }
                        if (typeof $.fn.select2 !== 'undefined') {
                            $doctorSelect.trigger('change.select2');
                        }
                    },
                    error: function() {
                        $doctorSelect.html('<option value="">Error loading doctors</option>');
                    }
                });
            } else {
                restoreAllDoctorsDropdown(null);
                $('#zoneBalanceInfo').hide();
            }
        });

        // Doctor selection -> Auto select zone if needed & show Outstanding Balance
        $('#customer_id').on('change', function() {
            var docId = $(this).val();
            if (docId) {
                var selectedOption = $(this).find(':selected');
                var docZone = selectedOption.data('zone');
                if (docZone && $('#zone_id').val() != docZone) {
                    $('#zone_id').val(docZone).trigger('change', ['from_doctor']);
                }

                if (doctorsMap[docId]) {
                    var doc = doctorsMap[docId];
                    updateCustomerBalance(doc.balance, doc.balance_formatted, doc.customer_name, doc.raw_formatted, doc.already_received_formatted, doc.already_received);
                } else {
                    // Fetch individually if not in map
                    $.ajax({
                        url: "{{ url('/doctor-funds/customer-balance') }}/" + docId,
                        type: "GET",
                        dataType: "json",
                        success: function(res) {
                            updateCustomerBalance(res.balance, res.balance_formatted, res.customer_name, res.raw_formatted, res.already_received_formatted, res.already_received);
                        }
                    });
                }
            } else {
                resetBalanceDisplay();
            }
        });

        // Live calculation when user enters receiving amount
        $('#amount').on('input', function() {
            calculateRemaining();
        });
    });

    function updateCustomerBalance(bal, formatted, docName, rawFormatted, alreadyReceivedFormatted, alreadyReceived) {
        $('#currentCustomerBalance').val(bal);
        var $badge = $('#balanceDisplay');
        $badge.removeClass('balance-neutral balance-cr balance-dr');

        if (bal > 0) {
            $badge.addClass('balance-dr');
            $badge.html('<i class="bi bi-clock-history me-1"></i> ' + formatted + ' <small>(Receivable)</small>');
        } else if (bal < 0) {
            $badge.addClass('balance-cr');
            $badge.html('<i class="bi bi-check-circle me-1"></i> ' + formatted + ' <small>(Advance/Credit)</small>');
        } else {
            $badge.addClass('balance-neutral');
            $badge.html('0.00 <small>(Nil Balance)</small>');
        }

        if (alreadyReceived && parseFloat(alreadyReceived) > 0) {
            $('#balanceBreakdown').html(
                '<i class="bi bi-info-circle text-primary me-1"></i> Gross Sales: <strong>PKR ' + rawFormatted + '</strong> &middot; Already Paid: <strong class="text-success">PKR ' + alreadyReceivedFormatted + '</strong>'
            ).show();
        } else {
            $('#balanceBreakdown').hide();
        }

        // Auto remarks if empty
        if (!$('#narration').val() && docName) {
            $('#narration').val('Recovery received for ' + docName);
        }

        calculateRemaining();
    }

    function resetBalanceDisplay() {
        $('#currentCustomerBalance').val(0);
        $('#balanceDisplay').removeClass('balance-dr balance-cr').addClass('balance-neutral').html('0.00 Dr');
        $('#balanceBreakdown').hide();
        $('#remainingBalanceCalc').hide();
    }

    function calculateRemaining() {
        var curBal = parseFloat($('#currentCustomerBalance').val()) || 0;
        var rcvAmt = parseFloat($('#amount').val()) || 0;

        if (curBal > 0 && rcvAmt > 0) {
            var remaining = curBal - rcvAmt;
            var remFormatted = Math.abs(remaining).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            var remType = remaining >= 0 ? 'Dr (Still Receivable)' : 'Cr (Advance Paid)';
            var remColor = remaining >= 0 ? '#b91c1c' : '#15803d';

            $('#remainingBalanceCalc').html(
                '<span style="color:' + remColor + ';">Remaining After Receipt: PKR ' + remFormatted + ' ' + remType + '</span>'
            ).show();
        } else {
            $('#remainingBalanceCalc').hide();
        }
    }

    function handleFilePreview(input, labelId, previewContainerId) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var fileName = file.name;
            var fileSize = (file.size / 1024 / 1024).toFixed(2);

            $('#' + labelId).html('<span class="text-success font-weight-bold"><i class="bi bi-check2-circle me-1"></i> Selected: ' + fileName + ' (' + fileSize + ' MB)</span>');

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
