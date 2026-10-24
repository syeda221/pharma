@extends('admin_panel.layout.app')

@section('content')
    <link href="{{ asset('assets/vendors/select2/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        :root {
            --erp-primary: #4a69bd;
            --erp-primary-hover: #3855a2;
            --erp-success: #10b981;
            --erp-danger: #ef4444;
            --erp-bg: #f5f6fa;
            --erp-border: #dcdde1;
            --erp-text: #2f3640;
            --erp-muted: #7f8fa6;
        }

        body {
            background-color: var(--erp-bg);
            color: var(--erp-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .erp-card {
            background: white;
            border: 1px solid var(--erp-border);
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .erp-header {
            background: white;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--erp-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }

        .erp-header h5 {
            margin: 0;
            font-weight: 700;
            color: var(--erp-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--erp-muted);
            text-transform: uppercase;
            margin-bottom: 0.3rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid var(--erp-border);
            padding: 0.45rem 0.75rem;
            font-size: 0.88rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--erp-primary);
            box-shadow: 0 0 0 3px rgba(74, 105, 189, 0.15);
        }

        /* Select2 Theme */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid var(--erp-border) !important;
            border-radius: 6px !important;
            padding: 5px 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            color: var(--erp-text) !important;
            font-weight: 600;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .table-erp {
            width: 100%;
            margin-bottom: 0;
        }

        .table-erp thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            padding: 0.65rem 0.6rem;
            border-bottom: 2px solid var(--erp-border);
            white-space: nowrap;
        }

        .table-erp tbody td {
            vertical-align: middle;
            padding: 0.5rem 0.4rem;
            border-bottom: 1px solid #f1f2f6;
        }

        .kpi-card-mini {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            text-align: center;
        }
        .kpi-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 0.2rem;
        }
        .kpi-value {
            font-size: 1.15rem;
            font-weight: 800;
            color: #1e293b;
        }

        .tier-pill {
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            border-radius: 20px;
            padding: 0.25rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            user-select: none;
        }
        .tier-pill.active {
            background: var(--erp-primary);
            color: #ffffff;
            border-color: var(--erp-primary);
            box-shadow: 0 2px 4px rgba(74, 105, 189, 0.25);
        }
        .tier-pill:hover:not(.active) {
            background: #e2e8f0;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="erp-card">
            <div class="erp-header">
                <div>
                    <h5><i class="fas fa-undo-alt me-2"></i> Standalone Direct Sale Return</h5>
                    <small class="text-muted">Return products directly without invoice selection &bull; Auto updates inventory &amp; customer ledger</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-3 py-2 fs-6 font-monospace shadow-sm">
                        <i class="fas fa-hashtag me-1"></i> {{ $nextInvoice }}
                    </span>
                    <a href="{{ route('sale.return.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-list me-1"></i> Returns List
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <form id="directReturnForm" action="{{ route('sale.return.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="sale_id" value="">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- SECTION 1: HEADER DETAILS --}}
                    <div class="row g-3 mb-4 p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        <!-- Customer Selection -->
                        <div class="col-md-5">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-user text-primary me-1"></i> Customer <span class="text-danger">*</span></span>
                                <span id="custBalBadge" class="badge bg-danger d-none" style="font-size:0.75rem;">Bal: Rs <span id="custBalVal">0.00</span></span>
                            </label>
                            <select id="customerSelect" name="customer_id" class="form-select select2" required style="width:100%">
                                <option value="">-- Search &amp; Select Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        data-name="{{ $c->customer_name }}"
                                        data-prev-bal="{{ (float)($c->previous_balance ?? 0) }}"
                                        data-doc-disc="{{ (float)($c->custom_discount_doctor ?? 0) }}"
                                        data-dist-disc="{{ (float)($c->custom_discount_distribution ?? 0) }}"
                                        data-med-disc="{{ (float)($c->custom_discount_medical ?? 0) }}"
                                        data-zone="{{ $c->zone ?? '-' }}"
                                        data-type="{{ $c->customer_type ?? '-' }}">
                                        {{ $c->customer_name }} ({{ $c->customer_id ?? 'ID:'.$c->id }}) - {{ $c->customer_type ?? 'Customer' }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Customer Tiers & Info Box -->
                            <div id="customerTiersBox" class="mt-2 d-none">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <small class="text-muted fw-bold">Active Discount Tiers:</small>
                                    <span class="tier-pill" id="tierDocBtn" data-tier="doctor">
                                        <i class="fas fa-user-md"></i> Doctor: <span id="tierDocPct">0</span>%
                                    </span>
                                    <span class="tier-pill" id="tierDistBtn" data-tier="distribution">
                                        <i class="fas fa-truck"></i> Dist: <span id="tierDistPct">0</span>%
                                    </span>
                                    <span class="tier-pill" id="tierMedBtn" data-tier="medical">
                                        <i class="fas fa-pills"></i> Medical: <span id="tierMedPct">0</span>%
                                    </span>
                                    <span class="badge bg-success py-1 px-2 text-white fw-bold" id="combinedTierBadge">0% Total</span>
                                </div>
                            </div>
                        </div>

                        <!-- Warehouse Selection -->
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-warehouse text-primary me-1"></i> Return Warehouse <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select" required>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Return Date -->
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-calendar-alt text-primary me-1"></i> Return Date <span class="text-danger">*</span></label>
                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Return Reason / Remarks -->
                        <div class="col-md-2">
                            <label class="form-label"><i class="fas fa-comment-alt text-muted me-1"></i> Remarks</label>
                            <input type="text" name="return_reason" class="form-control" placeholder="e.g. Near expiry / excess">
                        </div>
                    </div>

                    {{-- SECTION 2: DYNAMIC RETURN ITEMS TABLE --}}
                    <div class="table-responsive rounded-3 border mb-3">
                        <table class="table table-erp table-hover align-middle mb-0" id="returnItemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 35px;" class="text-center">#</th>
                                    <th style="min-width: 280px;">PRODUCT &bull; VARIANT</th>
                                    <th style="width: 110px;" class="text-center">UNIT / PACK</th>
                                    <th style="width: 120px;" class="text-end">PRICE (TP)</th>
                                    <th style="width: 110px;" class="text-center">RETURN QTY</th>
                                    <th style="width: 100px;" class="text-center">BONUS RET</th>
                                    <th style="width: 100px;" class="text-center">DISC %</th>
                                    <th style="width: 110px;" class="text-end">DISC AMT</th>
                                    <th style="width: 130px;" class="text-end">LINE TOTAL</th>
                                    <th style="width: 45px;" class="text-center"><i class="fas fa-cog"></i></th>
                                </tr>
                            </thead>
                            <tbody id="returnTableBody">
                                {{-- Rows dynamically added via JS --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3 py-2 shadow-sm rounded-2" id="btnAddRow">
                            <i class="fas fa-plus-circle me-1"></i> + Add Another Product
                        </button>
                        <span class="text-muted small">
                            <i class="fas fa-info-circle text-info me-1"></i> Line Total = (Return Qty &times; TP Price) &minus; Discount
                        </span>
                    </div>

                    {{-- SECTION 3: OPERATIONAL WATERFALL SUMMARY CARDS --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="kpi-card-mini border-primary border-opacity-25 bg-light">
                                <div class="kpi-title text-primary"><i class="fas fa-tags me-1"></i> Gross Return (TP)</div>
                                <div class="kpi-value text-primary font-monospace" id="kpiGrossReturn">Rs 0.00</div>
                                <small class="text-muted d-block" style="font-size: 10px;">Billed Trade Price</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="kpi-card-mini border-danger border-opacity-25 bg-light">
                                <div class="kpi-title text-danger"><i class="fas fa-percent me-1"></i> Total Deducted Discount</div>
                                <div class="kpi-value text-danger font-monospace" id="kpiDeductedDiscount">-Rs 0.00</div>
                                <small class="text-muted d-block" style="font-size: 10px;">Tier Concessions</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="kpi-card-mini border-success border-opacity-50" style="background-color: #f0fdf4;">
                                <div class="kpi-title text-success"><i class="fas fa-hand-holding-usd me-1"></i> Net Return Refund</div>
                                <div class="kpi-value text-success font-monospace" id="kpiNetRefund">Rs 0.00</div>
                                <small class="text-muted d-block" style="font-size: 10px;">Realized Customer Credit</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="kpi-card-mini border-secondary border-opacity-25 bg-light">
                                <div class="kpi-title text-dark"><i class="fas fa-calculator me-1"></i> New Customer Balance</div>
                                <div class="kpi-value text-dark font-monospace" id="kpiNewBalance">Rs 0.00</div>
                                <small class="text-muted d-block" style="font-size: 10px;" id="kpiBalDetail">Bal: 0.00 - Ret: 0.00</small>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: SETTLEMENT & REFUND METHOD --}}
                    <div class="p-3 mb-4 rounded-3 border" style="background-color: #ffffff;">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-wallet text-primary me-2"></i> Settlement / Refund Method</h6>
                        
                        <div class="d-flex align-items-center gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refund_mode" id="modeLedger" value="ledger" checked>
                                <label class="form-check-label fw-bold" for="modeLedger">
                                    <i class="fas fa-book text-primary me-1"></i> Adjust into Customer Account / Ledger (Default)
                                    <small class="d-block text-muted fw-normal">Deducts from customer's payable debt via Credit Note Journal Voucher</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="refund_mode" id="modeCash" value="cash">
                                <label class="form-check-label fw-bold" for="modeCash">
                                    <i class="fas fa-money-bill-wave text-success me-1"></i> Issue Immediate Cash / Bank Refund
                                    <small class="d-block text-muted fw-normal">Physically pay cash back to customer from Cash in Hand or Bank</small>
                                </label>
                            </div>
                        </div>

                        <!-- Cash / Bank Account Selector (Shown only when Cash Refund chosen) -->
                        <div id="cashRefundRow" class="row g-3 d-none pt-2 border-top">
                            <div class="col-md-4">
                                <label class="form-label"><i class="fas fa-university text-primary me-1"></i> Payment Account</label>
                                <select name="payment_account_id[]" id="refundAccountId" class="form-select">
                                    <option value="">-- Select Cash/Bank Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->title }} ({{ $acc->account_number ?? 'Acc:'.$acc->id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="fas fa-coins text-success me-1"></i> Refund Cash Paid</label>
                                <input type="number" step="0.01" name="payment_amount[]" id="refundCashAmount" class="form-control font-monospace" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT ACTIONS --}}
                    <div class="d-flex justify-content-end align-items-center gap-3 pt-3 border-top">
                        <a href="{{ route('sale.return.index') }}" class="btn btn-light border px-4 py-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" id="btnSubmitReturn">
                            <i class="fas fa-check-circle me-1"></i> Post Direct Sale Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Options Data for JS -->
    <script>
        window.allProductOptions = @json($productOptions);
    </script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/select2/js/select2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Customer Select2
            $('#customerSelect').select2({
                placeholder: "-- Search & Select Customer --",
                allowClear: true,
                width: '100%'
            });

            let currentCustTiers = {
                doctor: 0,
                distribution: 0,
                medical: 0
            };
            let activeTiers = ['doctor', 'distribution']; // Default active tiers
            let currentCustomerBalance = 0;

            // Customer Change Handler
            $('#customerSelect').on('change', function() {
                let opt = $(this).find(':selected');
                if (!opt.val()) {
                    $('#custBalBadge').addClass('d-none');
                    $('#customerTiersBox').addClass('d-none');
                    currentCustTiers = { doctor: 0, distribution: 0, medical: 0 };
                    currentCustomerBalance = 0;
                    recalcAllRows();
                    return;
                }

                currentCustomerBalance = parseFloat(opt.data('prev-bal') || 0);
                $('#custBalVal').text(fmt(currentCustomerBalance));
                $('#custBalBadge').removeClass('d-none');

                currentCustTiers.doctor = parseFloat(opt.data('doc-disc') || 0);
                currentCustTiers.distribution = parseFloat(opt.data('dist-disc') || 0);
                currentCustTiers.medical = parseFloat(opt.data('med-disc') || 0);

                $('#tierDocPct').text(currentCustTiers.doctor);
                $('#tierDistPct').text(currentCustTiers.distribution);
                $('#tierMedPct').text(currentCustTiers.medical);

                // Default active tier based on customer tier presence
                activeTiers = [];
                if (currentCustTiers.distribution > 0) activeTiers.push('distribution');
                if (currentCustTiers.doctor > 0) activeTiers.push('doctor');
                if (activeTiers.length === 0 && currentCustTiers.medical > 0) activeTiers.push('medical');
                if (activeTiers.length === 0) activeTiers.push('doctor');

                updateTierPillUI();
                $('#customerTiersBox').removeClass('d-none');

                recalcAllRows();
            });

            // Tier Pill Toggle Handlers
            $('.tier-pill').on('click', function() {
                let tier = $(this).data('tier');
                if (activeTiers.includes(tier)) {
                    if (activeTiers.length > 1) { // keep at least 1 or allow 0
                        activeTiers = activeTiers.filter(t => t !== tier);
                    } else {
                        activeTiers = [];
                    }
                } else {
                    activeTiers.push(tier);
                }
                updateTierPillUI();
                recalcAllRows();
            });

            function getActiveTierDiscountPercent() {
                let sum = 0;
                activeTiers.forEach(t => {
                    sum += (currentCustTiers[t] || 0);
                });
                return sum;
            }

            function updateTierPillUI() {
                $('.tier-pill').removeClass('active');
                activeTiers.forEach(t => {
                    $(`.tier-pill[data-tier="${t}"]`).addClass('active');
                });
                let totPct = getActiveTierDiscountPercent();
                $('#combinedTierBadge').text(totPct + '% Total');
            }

            // ADD ROW LOGIC
            let rowCounter = 0;
            function addRow() {
                rowCounter++;
                let defaultDisc = getActiveTierDiscountPercent();

                let html = `
                    <tr class="item-row" data-row="${rowCounter}">
                        <td class="text-center fw-bold text-muted row-index">${$('#returnTableBody tr').length + 1}</td>
                        <td>
                            <select class="form-select select2-product product-picker" required style="width:100%">
                                <option value="">-- Search Product / Pack --</option>
                                ${window.allProductOptions.map(p => `
                                    <option value="${p.product_id}"
                                        data-price="${p.price}"
                                        data-conv="${p.conv_factor}"
                                        data-size="${p.size}"
                                        data-unit="${p.unit}"
                                        data-payload="${p.color_payload}">
                                        ${p.label}
                                    </option>
                                `).join('')}
                            </select>
                            <input type="hidden" name="product_id[]" class="input-prod-id">
                            <input type="hidden" name="color[]" class="input-color-payload">
                        </td>
                        <td class="text-center font-monospace text-muted col-unit">-</td>
                        <td>
                            <input type="number" step="0.01" min="0" name="price[]" class="form-control text-end input-price font-monospace fw-bold" placeholder="0.00" required>
                        </td>
                        <td>
                            <input type="number" step="1" min="1" name="qty[]" class="form-control text-center input-qty font-monospace fw-bold" value="1" required>
                            <small class="text-muted d-block text-center col-boxes" style="font-size: 10px;">(1 Box)</small>
                        </td>
                        <td>
                            <input type="number" step="1" min="0" name="return_bonus[]" class="form-control text-center input-bonus font-monospace" value="0">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" max="100" class="form-control text-center input-disc-pct font-monospace" value="${defaultDisc}">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" name="item_disc[]" class="form-control text-end input-disc-amt font-monospace" value="0.00">
                        </td>
                        <td class="text-end fw-bold font-monospace text-primary col-line-total">
                            Rs 0.00
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-1" style="line-height:1;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                `;

                let $tr = $(html);
                $('#returnTableBody').append($tr);

                // Init Select2 for this row
                $tr.find('.select2-product').select2({
                    placeholder: "-- Search Product / Pack --",
                    allowClear: true,
                    width: '100%'
                });

                recomputeRow($tr);
            }

            $('#btnAddRow').on('click', function() {
                addRow();
            });

            // Initial Row
            addRow();

            // Product selection change inside a row
            $(document).on('change', '.product-picker', function() {
                let $tr = $(this).closest('tr');
                let opt = $(this).find(':selected');

                if (!opt.val()) {
                    $tr.find('.input-prod-id').val('');
                    $tr.find('.input-color-payload').val('');
                    $tr.find('.input-price').val('0.00');
                    $tr.find('.col-unit').text('-');
                    recomputeRow($tr);
                    return;
                }

                $tr.find('.input-prod-id').val(opt.val());
                $tr.find('.input-color-payload').val(opt.data('payload'));
                $tr.find('.input-price').val(parseFloat(opt.data('price') || 0).toFixed(2));
                
                let unitStr = opt.data('unit') || 'Pcs';
                let conv = parseFloat(opt.data('conv') || 1);
                let size = opt.data('size');
                $tr.find('.col-unit').text(size !== '-' && size ? `${size} (${unitStr})` : unitStr);
                $tr.data('conv', conv);

                // Set default discount
                let activeDisc = getActiveTierDiscountPercent();
                $tr.find('.input-disc-pct').val(activeDisc);

                recomputeRow($tr);
            });

            // Input handlers for row calculation
            $(document).on('input', '.input-price, .input-qty, .input-bonus', function() {
                let $tr = $(this).closest('tr');
                recomputeRow($tr);
            });

            $(document).on('input', '.input-disc-pct', function() {
                let $tr = $(this).closest('tr');
                let price = parseFloat($tr.find('.input-price').val() || 0);
                let qty = parseFloat($tr.find('.input-qty').val() || 0);
                let gross = price * qty;
                let pct = parseFloat($(this).val() || 0);
                let discAmt = Math.round(((gross * pct) / 100) * 100) / 100;
                $tr.find('.input-disc-amt').val(discAmt.toFixed(2));
                recomputeRow($tr, false);
            });

            $(document).on('input', '.input-disc-amt', function() {
                let $tr = $(this).closest('tr');
                let price = parseFloat($tr.find('.input-price').val() || 0);
                let qty = parseFloat($tr.find('.input-qty').val() || 0);
                let gross = price * qty;
                let amt = parseFloat($(this).val() || 0);
                let pct = gross > 0 ? ((amt / gross) * 100).toFixed(2) : 0;
                $tr.find('.input-disc-pct').val(pct);
                recomputeRow($tr, false);
            });

            $(document).on('click', '.btn-remove-row', function() {
                if ($('#returnTableBody tr').length > 1) {
                    $(this).closest('tr').remove();
                    reindexRows();
                    updateGrandTotals();
                } else {
                    alert("At least one product item is required in the return.");
                }
            });

            function reindexRows() {
                $('#returnTableBody tr').each(function(idx) {
                    $(this).find('.row-index').text(idx + 1);
                });
            }

            function recomputeRow($tr, recalcDisc = true) {
                let price = parseFloat($tr.find('.input-price').val() || 0);
                let qty = parseFloat($tr.find('.input-qty').val() || 0);
                let conv = parseFloat($tr.data('conv') || 1);
                if (conv <= 0) conv = 1;

                let boxes = (qty / conv).toFixed(1);
                $tr.find('.col-boxes').text(`(${boxes} Box)`);

                let gross = price * qty;

                if (recalcDisc) {
                    let discPct = parseFloat($tr.find('.input-disc-pct').val() || 0);
                    let discAmt = Math.round(((gross * discPct) / 100) * 100) / 100;
                    $tr.find('.input-disc-amt').val(discAmt.toFixed(2));
                }

                let discAmt = parseFloat($tr.find('.input-disc-amt').val() || 0);
                let lineTotal = Math.max(0, gross - discAmt);

                $tr.find('.col-line-total').text('Rs ' + fmt(lineTotal));
                $tr.data('gross', gross);
                $tr.data('discount', discAmt);
                $tr.data('net', lineTotal);

                updateGrandTotals();
            }

            function recalcAllRows() {
                let activeDisc = getActiveTierDiscountPercent();
                $('#returnTableBody tr').each(function() {
                    $(this).find('.input-disc-pct').val(activeDisc);
                    recomputeRow($(this), true);
                });
            }

            function updateGrandTotals() {
                let totalGross = 0;
                let totalDiscount = 0;
                let totalNet = 0;

                $('#returnTableBody tr').each(function() {
                    totalGross += parseFloat($(this).data('gross') || 0);
                    totalDiscount += parseFloat($(this).data('discount') || 0);
                    totalNet += parseFloat($(this).data('net') || 0);
                });

                $('#kpiGrossReturn').text('Rs ' + fmt(totalGross));
                $('#kpiDeductedDiscount').text('-Rs ' + fmt(totalDiscount));
                $('#kpiNetRefund').text('Rs ' + fmt(totalNet));

                // Customer Balance Impact
                let newBalance = currentCustomerBalance - totalNet;
                $('#kpiNewBalance').text('Rs ' + fmt(newBalance));
                $('#kpiBalDetail').text(`Bal: ${fmt(currentCustomerBalance)} - Ret: ${fmt(totalNet)}`);

                // Auto update cash refund amount if cash mode selected
                if ($('#modeCash').is(':checked')) {
                    $('#refundCashAmount').val(totalNet.toFixed(2));
                }
            }

            // Refund Mode toggle
            $('input[name="refund_mode"]').on('change', function() {
                if ($(this).val() === 'cash') {
                    $('#cashRefundRow').removeClass('d-none');
                    let net = parseFloat($('#kpiNetRefund').text().replace(/[^0-9.-]+/g, "") || 0);
                    $('#refundCashAmount').val(net.toFixed(2));
                } else {
                    $('#cashRefundRow').addClass('d-none');
                    $('#refundAccountId').val('');
                    $('#refundCashAmount').val('');
                }
            });

            // Form Submit validation
            $('#directReturnForm').on('submit', function(e) {
                if (!$('#customerSelect').val()) {
                    e.preventDefault();
                    alert("Please select a customer.");
                    return false;
                }

                let validItems = 0;
                $('#returnTableBody tr').each(function() {
                    let prodId = $(this).find('.input-prod-id').val();
                    let qty = parseFloat($(this).find('.input-qty').val() || 0);
                    if (prodId && qty > 0) {
                        validItems++;
                    }
                });

                if (validItems === 0) {
                    e.preventDefault();
                    alert("Please select at least one product with a return quantity greater than 0.");
                    return false;
                }

                if ($('#modeCash').is(':checked')) {
                    if (!$('#refundAccountId').val()) {
                        e.preventDefault();
                        alert("Please select the Cash/Bank account from which refund was paid.");
                        return false;
                    }
                }

                $('#btnSubmitReturn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Posting Return...');
            });

            function fmt(val) {
                return Number(val || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
        });
    </script>
@endpush
