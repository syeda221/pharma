@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --excel-border: #cbd5e1;
            --excel-header-bg: #1e293b;
            --excel-header-text: #ffffff;
            --excel-row-hover: #f1f5f9;
            --excel-focus: #2563eb;
        }

        /* Compact Layout & Card */
        .voucher-sheet-card {
            background: #ffffff;
            border: 1px solid var(--excel-border);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .sheet-header-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-bar {
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            padding: 4px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 10px;
            margin-top: 14px;
        }

        /* Compact Excel Form Inputs */
        .ex-label {
            font-size: 0.72rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            display: block;
        }

        .ex-input {
            height: 32px;
            font-size: 0.83rem;
            padding: 3px 8px;
            border: 1px solid var(--excel-border);
            border-radius: 4px;
            background-color: #ffffff;
            color: #0f172a;
            width: 100%;
            transition: all 0.15s ease;
        }

        .ex-input:focus {
            border-color: var(--excel-focus);
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
            background-color: #ffffff;
        }

        .ex-input[readonly] {
            background-color: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        select.ex-input {
            appearance: auto;
            cursor: pointer;
        }

        /* Compact Balance Pill */
        .balance-badge {
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            border-radius: 4px;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }
        .balance-dr {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #dc2626;
        }
        .balance-cr {
            background: #f0fdf4;
            border-color: #86efac;
            color: #16a34a;
        }

        /* Excel-like Table Styling */
        .excel-table-container {
            border: 1px solid var(--excel-border);
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
            margin-top: 8px;
        }

        .excel-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.83rem;
            margin-bottom: 0;
        }

        .excel-table thead th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 7px 10px;
            border: 1px solid #334155;
            vertical-align: middle;
        }

        .excel-table tbody td {
            padding: 4px 6px;
            border: 1px solid var(--excel-border);
            vertical-align: middle;
            background-color: #ffffff;
        }

        .excel-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .excel-table .row-number {
            font-weight: 700;
            color: #64748b;
            text-align: center;
            background-color: #f8fafc;
            width: 40px;
            user-select: none;
        }

        .excel-table .cell-input {
            height: 28px;
            font-size: 0.83rem;
            padding: 2px 6px;
            border: 1px solid transparent;
            border-radius: 3px;
            background: transparent;
            width: 100%;
            color: #0f172a;
        }

        .excel-table .cell-input:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .excel-table .cell-input:focus {
            border-color: var(--excel-focus);
            background: #ffffff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .excel-table select.cell-input {
            appearance: auto;
            border: 1px solid #cbd5e1;
            background: #ffffff;
        }

        .btn-mini-add {
            width: 28px;
            height: 28px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #2563eb;
            font-size: 0.75rem;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .btn-mini-add:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        .btn-mini-del {
            width: 26px;
            height: 26px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            border: 1px solid #fecaca;
            background: #fff;
            color: #ef4444;
            font-size: 0.75rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .btn-mini-del:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        /* Add Row Bar */
        .add-row-bar {
            padding: 6px 12px;
            background: #f8fafc;
            border-top: 1px solid var(--excel-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Summary Total Bar */
        .total-summary-card {
            background: #0f172a;
            color: #ffffff;
            border-radius: 6px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 280px;
        }

        .total-summary-value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #38bdf8;
            background: transparent;
            border: none;
            text-align: right;
            width: 160px;
            font-family: monospace;
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

        /* Select2 Custom Styling for Expense Voucher */
        .excel-table .select2-container {
            width: 100% !important;
            flex-grow: 1;
        }
        .excel-table .select2-container--default .select2-selection--single {
            height: 30px !important;
            border: 1px solid var(--excel-border) !important;
            border-radius: 4px !important;
            background-color: #ffffff !important;
            padding: 1px 6px !important;
            display: flex;
            align-items: center;
        }
        .excel-table .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            color: #0f172a !important;
            font-size: 0.83rem !important;
            padding-left: 0 !important;
            font-weight: 500;
        }
        .excel-table .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
            right: 4px !important;
        }
        .excel-table .select2-container--default.select2-container--focus .select2-selection--single,
        .excel-table .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--excel-focus) !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2) !important;
        }

        /* Header select2 styles */
        .select2-container--default .select2-selection--single {
            border: 1px solid var(--excel-border) !important;
            border-radius: 4px !important;
            height: 32px !important;
            padding: 2px 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            color: #0f172a !important;
            font-size: 0.83rem !important;
        }
        .select2-dropdown {
            border: 1px solid var(--excel-border) !important;
            border-radius: 6px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            z-index: 99999 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--excel-border) !important;
            border-radius: 4px !important;
            padding: 4px 8px !important;
            font-size: 0.82rem !important;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-3 px-md-4">

                {{-- Alert Messages --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-2 px-3 small border-0 shadow-sm rounded-3 mb-3">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="padding: 0.75rem;"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 small border-0 shadow-sm rounded-3 mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="padding: 0.75rem;"></button>
                    </div>
                @endif

                <form action="{{ route('expense_vochers.update', $voucher->id) }}" method="POST" id="expenseEditForm">
                    @csrf
                    @method('PUT')

                    <div class="voucher-sheet-card">

                        {{-- Action Top Bar --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                            <div class="sheet-header-title">
                                <i class="bi bi-pencil-square text-warning"></i> Edit Expense Voucher #{{ $voucher->evid }}
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('all_expense_vochers') }}" class="btn btn-outline-secondary btn-sm fw-bold d-inline-flex align-items-center" style="height: 32px; font-size: 0.8rem; padding: 4px 12px; border-radius: 5px; margin-right: 12px;">
                                    <i class="bi bi-list-ul me-1" style="margin-right: 5px;"></i> All Expenses
                                </a>
                                <a href="{{ route('expenseprint', $voucher->id) }}" target="_blank" class="btn btn-outline-primary btn-sm fw-bold d-inline-flex align-items-center" style="height: 32px; font-size: 0.8rem; padding: 4px 12px; border-radius: 5px; margin-right: 12px;">
                                    <i class="bi bi-printer me-1" style="margin-right: 5px;"></i> Print
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm fw-bold shadow-sm d-inline-flex align-items-center" style="height: 32px; font-size: 0.8rem; padding: 4px 16px; border-radius: 5px;">
                                    <i class="bi bi-check2 me-1" style="margin-right: 5px;"></i> Update Voucher
                                </button>
                            </div>
                        </div>

                        {{-- Section 1: Header / Voucher Info --}}
                        <div class="section-bar">1. Voucher &amp; Source Details</div>
                        <div class="row g-2 mb-2">
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="ex-label">Voucher #</label>
                                <input type="text" class="ex-input fw-bold text-primary font-monospace" name="evid" value="{{ $voucher->evid }}" readonly>
                            </div>
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="ex-label">Date</label>
                                <input type="date" name="entry_date" class="ex-input" value="{{ $voucher->entry_date }}" required>
                            </div>
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="ex-label">Ref / Cheque #</label>
                                <input type="text" name="ref_no_header" class="ex-input" value="{{ $voucher->reference_no }}" placeholder="e.g. Chq-1029">
                            </div>
                            <div class="col-6 col-md-3 col-lg-3">
                                <label class="ex-label">Payment Head (Source)</label>
                                <select name="vendor_type" class="ex-input" id="partyType" required>
                                    <option value="" disabled>Select Source Head</option>
                                    @foreach ($AccountHeads as $head)
                                        <option value="{{ $head->id }}" {{ (string)$voucher->type === (string)$head->id ? 'selected' : '' }}>
                                            {{ $head->name }}
                                        </option>
                                    @endforeach
                                    <option value="vendor" {{ $voucher->type === 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="customer" {{ $voucher->type === 'customer' ? 'selected' : '' }}>Customer</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3 col-lg-3">
                                <label class="ex-label">Account / Paid From</label>
                                <select name="vendor_id" class="ex-input" id="partyId" required>
                                    <option value="" disabled>Select Account</option>
                                    @foreach ($accountsList as $acc)
                                        <option value="{{ $acc->id }}" 
                                            data-code="{{ $acc->account_code }}" 
                                            data-bal="{{ $acc->balance }}"
                                            {{ (string)$voucher->party_id === (string)$acc->id ? 'selected' : '' }}>
                                            {{ $acc->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="ex-label">Account Code</label>
                                <input type="text" name="tel" id="tel" class="ex-input font-monospace" readonly value="{{ $accountCode }}" placeholder="Auto Code">
                            </div>
                            <div class="col-6 col-md-3 col-lg-3">
                                <label class="ex-label">Account Balance</label>
                                <div id="balanceContainer" class="balance-badge {{ $currentBalance >= 0 ? 'balance-dr' : 'balance-cr' }}">
                                    <span style="font-size: 0.72rem; color: #64748b;">Current:</span>
                                    <span id="balanceDisplay">
                                        {{ number_format(abs($currentBalance), 2) }}
                                        <span style="font-size: 0.75em">{{ $currentBalance >= 0 ? 'Dr' : 'Cr' }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-lg-7">
                                <label class="ex-label">Global Remarks / Description</label>
                                <input type="text" name="remarks" class="ex-input" id="remarks" value="{{ $voucher->remarks }}" placeholder="Description of expense payment...">
                            </div>
                        </div>

                        {{-- Section 2: Excel Expense Grid --}}
                        <div class="section-bar d-flex justify-content-between align-items-center">
                            <span>2. Expense Allocation (Excel Grid)</span>
                            <span class="text-muted text-lowercase font-normal" style="font-size: 0.72rem; font-weight: normal;">Press Enter in amount to add line</span>
                        </div>

                        {{-- Hidden Template for new row categories --}}
                        <template id="categoryOptionsTemplate">
                            <option value="" disabled selected>-- Select Category --</option>
                            @foreach ($expenseCategories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </template>

                        <div class="excel-table-container">
                            <div class="table-responsive">
                                <table class="excel-table" id="voucherTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;" class="text-center">#</th>
                                            <th style="width: 35%;">Expense Category</th>
                                            <th style="width: 41%;">Remarks / Description</th>
                                            <th style="width: 18%;" class="text-end">Amount (PKR)</th>
                                            <th style="width: 45px;" class="text-center">Del</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($existingRows as $idx => $row)
                                            <tr>
                                                <td class="row-number">{{ $idx + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <select name="row_account_id[]" class="cell-input rowAccountCategory" required>
                                                            <option value="" disabled>-- Select Category --</option>
                                                            @foreach ($expenseCategories as $cat)
                                                                <option value="{{ $cat->id }}" {{ (string)$cat->id === (string)$row['category_id'] ? 'selected' : '' }}>
                                                                    {{ $cat->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#newExpenseCategoryModal" class="btn-mini-add" title="Quick Add Category">
                                                            <i class="bi bi-plus-lg"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" name="narration_text[]" class="cell-input" placeholder="e.g. Utility bill, stationery, repair..." value="{{ $row['narration_text'] }}">
                                                    <input type="hidden" name="narration_id[]" value="{{ $row['narration_id'] }}">
                                                </td>
                                                <td>
                                                    <input type="number" name="amount[]" step="0.01" class="cell-input text-end fw-bold amount font-monospace" placeholder="0.00" value="{{ $row['amount'] }}" required>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn-mini-del removeRow" title="Delete Row">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="add-row-bar">
                                <button type="button" class="btn btn-outline-primary btn-sm fw-bold d-inline-flex align-items-center" id="addNewRow" style="height: 28px; font-size: 0.78rem; padding: 2px 10px; border-radius: 4px;">
                                    <i class="bi bi-plus-circle me-1" style="margin-right: 5px;"></i> Add Another Row (Enter)
                                </button>
                                <span class="text-muted small" style="font-size: 0.74rem;">Rows: <strong id="rowCountDisplay">{{ count($existingRows) }}</strong></span>
                            </div>
                        </div>

                        {{-- Bottom Total & Summary Bar --}}
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top flex-wrap gap-2">
                            <div class="text-muted small">
                                <i class="bi bi-info-circle me-1" style="margin-right: 5px;"></i> Please verify amounts before updating the voucher.
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="total-summary-card shadow-sm" style="margin-right: 16px;">
                                    <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase;">Total Expense</span>
                                    <div class="d-flex align-items-center" style="margin-left: 12px;">
                                        <span style="color: #94a3b8; font-size: 0.9rem; margin-right: 6px;">Rs.</span>
                                        <input type="text" name="total_amount" class="total-summary-value" id="totalAmount" readonly value="{{ number_format((float)$voucher->total_amount, 2, '.', '') }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary fw-bold shadow-sm d-inline-flex align-items-center" style="height: 44px; padding: 0 24px; border-radius: 6px; font-size: 0.9rem;">
                                    <i class="bi bi-check-circle-fill me-2" style="margin-right: 8px;"></i> Update Expense
                                </button>
                            </div>
                        </div>

                        {{-- Hidden dummy fields for controller compatibility --}}
                        <input type="hidden" name="reference_no[]" value="">
                        <input type="hidden" name="discount_value[]" value="0">
                        <input type="hidden" name="rate[]" value="0">

                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Add Expense Category Modal -->
    <div class="modal fade" id="newExpenseCategoryModal" tabindex="-1" role="dialog" aria-labelledby="newExpenseCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white px-4 py-3">
                    <h5 class="modal-title fw-bold text-white mb-0" id="newExpenseCategoryModalLabel">
                        <i class="bi bi-folder-plus me-1" style="margin-right: 5px;"></i> Create Expense Category
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 1.5rem; background:none; border:none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="newExpenseCategoryForm">
                    @csrf
                    <div class="modal-body p-4 text-start">
                        <div class="form-group mb-3">
                            <label class="ex-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="ex-input" placeholder="e.g. Office Stationery, Utility Bills" required style="height: 38px;">
                        </div>
                        <div class="form-group mb-3">
                            <label class="ex-label">Category Code (Optional)</label>
                            <input type="text" name="code" class="ex-input font-monospace" placeholder="e.g. EXP-01" style="height: 38px;">
                        </div>
                        <div class="form-group mb-0">
                            <label class="ex-label">Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Notes about this expense category..." style="font-size: 0.85rem; border-color: #cbd5e1;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top px-4 py-3">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" id="btnSaveNewCategory">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {

            // Helper to initialize Select2 on category dropdowns
            function initCategorySelect2($elem) {
                $elem.select2({
                    placeholder: "-- Select Category --",
                    allowClear: true,
                    width: 'resolve'
                });
            }

            // Initialize Select2 on page load
            $('#partyType').select2({ placeholder: "Select Source Head", allowClear: true, width: '100%' });
            $('#partyId').select2({ placeholder: "Select Account", allowClear: true, width: '100%' });
            initCategorySelect2($('.rowAccountCategory'));

            // Header Party Type Selection
            $('#partyType').on('change', function() {
                let type = $(this).val();
                loadPartyList(type);
            });

            function loadPartyList(type) {
                let $select = $('#partyId');
                $select.html('<option value="">Loading accounts...</option>').trigger('change');
                $('#tel').val('');
                updateBalance(0);

                if (type === 'vendor' || type === 'customer') {
                    $.get('{{ route("party.list") }}?type=' + type, function(data) {
                        $select.empty().append('<option value="" disabled selected>Select Party</option>');
                        data.forEach(function(item) {
                            $select.append(
                                `<option value="${item.id}" data-phone="${item.mobile || ''}" data-bal="${item.closing_balance}">${item.text}</option>`
                            );
                        });
                        $select.val('').trigger('change');
                    });
                } else if (type) {
                    $.get('{{ url("get-accounts-by-head") }}/' + type, function(data) {
                        $select.empty().append('<option value="" disabled selected>Select Account</option>');
                        data.forEach(function(acc) {
                            $select.append(
                                `<option value="${acc.id}" data-code="${acc.account_code}" data-bal="${acc.current_balance || acc.opening_balance || 0}">${acc.title}</option>`
                            );
                        });
                        $select.val('').trigger('change');
                    });
                } else {
                    $select.empty().append('<option value="" disabled selected>Select Account</option>').trigger('change');
                }
            }

            $('#partyId').on('change', function() {
                let $opt = $(this).find(':selected');
                if ($opt.val()) {
                    let codeOrPhone = $opt.data('phone') || $opt.data('code') || '';
                    $('#tel').val(codeOrPhone);
                    let bal = parseFloat($opt.data('bal')) || 0;
                    updateBalance(bal);
                }
            });

            function updateBalance(bal) {
                let $container = $('#balanceContainer');
                let $badge = $('#balanceDisplay');
                let formatted = Math.abs(bal).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (bal >= 0) {
                    $container.removeClass('balance-cr').addClass('balance-dr');
                    $badge.html(formatted + ' <span style="font-size: 0.75em">Dr</span>');
                } else {
                    $container.removeClass('balance-dr').addClass('balance-cr');
                    $badge.html(formatted + ' <span style="font-size: 0.75em">Cr</span>');
                }
            }

            // Totals Calculation & Row Numbers
            function updateRowIndices() {
                let count = 0;
                $('#voucherTable tbody tr').each(function(index) {
                    $(this).find('.row-number').text(index + 1);
                    count++;
                });
                $('#rowCountDisplay').text(count);
            }

            function calculateTotal() {
                let total = 0;
                $('.amount').each(function() {
                    total += parseFloat($(this).val()) || 0;
                });
                $('#totalAmount').val(total.toFixed(2));
            }

            $(document).on('input', '.amount', function() {
                calculateTotal();
            });

            // Prevent arrow up/down keys and scroll wheel from changing amount
            $(document).on('keydown', 'input[type="number"], .amount', function(e) {
                if (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.keyCode === 38 || e.keyCode === 40) {
                    e.preventDefault();
                }
            });

            $(document).on('wheel', 'input[type="number"], .amount', function(e) {
                $(this).blur();
            });

            // Add Row
            $('#addNewRow').on('click', function() {
                let optionsHtml = $('#categoryOptionsTemplate').html();
                let $newRow = $(`
                <tr>
                    <td class="row-number">1</td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <select name="row_account_id[]" class="cell-input rowAccountCategory" required>
                                ${optionsHtml}
                            </select>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#newExpenseCategoryModal" class="btn-mini-add" title="Quick Add Category">
                                <i class="bi bi-plus-lg"></i>
                            </a>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="narration_text[]" class="cell-input" placeholder="e.g. Utility bill, stationery, repair...">
                        <input type="hidden" name="narration_id[]" value="">
                    </td>
                    <td>
                        <input type="number" name="amount[]" step="0.01" class="cell-input text-end fw-bold amount font-monospace" placeholder="0.00" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn-mini-del removeRow" title="Delete Row"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                `);
                $('#voucherTable tbody').append($newRow);
                let $catSelect = $newRow.find('.rowAccountCategory');
                initCategorySelect2($catSelect);
                updateRowIndices();
            });

            // Remove Row
            $(document).on('click', '.removeRow', function() {
                if ($('#voucherTable tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                    updateRowIndices();
                    calculateTotal();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Notice',
                        text: 'At least one expense line is required.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });

            // Enter key on amount adds new row
            $(document).on('keypress', '.amount', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#addNewRow').click();
                }
            });

            // Handle New Expense Category AJAX Submission
            $('#newExpenseCategoryForm').on('submit', function(e) {
                e.preventDefault();
                let btn = $('#btnSaveNewCategory');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.ajax({
                    url: "{{ route('expense_categories.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        btn.prop('disabled', false).text('Save Category');
                        if(response.success && response.category) {
                            Swal.fire({ toast:true, position:'top-end', icon:'success', title:'Category Created', showConfirmButton:false, timer:1500 });
                            $('#newExpenseCategoryModal').modal('hide');
                            $('#newExpenseCategoryForm')[0].reset();
                            
                            let newCat = response.category;
                            let newOption = new Option(newCat.name, newCat.id, false, false);
                            $('.rowAccountCategory').each(function() {
                                $(this).append(newOption.cloneNode(true));
                            });
                            $('#categoryOptionsTemplate').append(newOption.cloneNode(true));
                        } else {
                            Swal.fire('Error', response.message || 'Failed to create Category.', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).text('Save Category');
                        let msg = 'Failed to create Category.';
                        if(xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });
    </script>
@endsection
