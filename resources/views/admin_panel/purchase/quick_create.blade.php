@extends('admin_panel.layout.app')

@section('content')
<style>
    /* Modern Clean ERP Styling */
    .premium-card {
        border: none !important;
        border-radius: 12px !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05) !important;
        background-color: #ffffff;
        margin-bottom: 20px;
    }
    .card-header-premium {
        background-color: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 12px 12px 0 0 !important;
        padding: 16px 20px;
        font-weight: 700;
        color: #1e293b;
    }
    .summary-card {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }
    .summary-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin-top: 5px;
    }
    .summary-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.8;
    }
    .form-control, .form-select {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .premium-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .premium-table thead th {
        background-color: #f1f5f9;
        color: #475569;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        padding: 14px 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    .premium-table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .btn-premium-primary {
        background-color: #2563eb;
        border: none;
        color: #ffffff;
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 20px;
        transition: all 0.2s;
    }
    .btn-premium-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    }
    /* Select2 overrides for modern look */
    .select2-container--bootstrap4 .select2-selection {
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
        height: calc(1.5em + .75rem + 2px) !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: calc(1.5em + .75rem) !important;
        padding-left: 15px !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .75rem) !important;
    }
</style>

<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid py-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Quick Purchase</h4>
                    <p class="text-muted mb-0">Record items, variants, and payments rapidly.</p>
                </div>
                <div>
                    <a href="{{ route('Purchase.home') }}" class="btn btn-outline-secondary px-4 fw-medium shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                </div>
            </div>

            <form action="{{ route('purchase.quick_store') }}" method="POST" id="quickPurchaseForm">
                @csrf
                
                <!-- Vendor & Product Details -->
                <div class="card premium-card">
                    <div class="card-header-premium">
                        <i class="fas fa-info-circle me-2 text-primary"></i> 1. Purchase Details
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Vendor <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="vendor_id" name="vendor_id" style="width: 100%;">
                                        <option value="">-- Select Existing Vendor --</option>
                                        @foreach($Vendor as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Or Create New Vendor</label>
                                    <input type="text" class="form-control" name="new_vendor_name" id="new_vendor_name" placeholder="Type new vendor name...">
                                </div>
                            </div>
                            <div class="col-md-4" id="openingBalanceWrapper" style="display: none;">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Opening Balance (New Vendor)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted">Rs</span>
                                        <input type="number" step="0.01" class="form-control border-start-0" name="opening_balance" id="opening_balance" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Category <span class="text-danger">*</span></label>
                                    <select class="form-select select2" name="category_id" id="category_id" style="width: 100%;" required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Sub Category</label>
                                    <select class="form-select select2" name="sub_category_id" id="sub_category_id" style="width: 100%;">
                                        <option value="">-- Select Subcategory --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="fw-bold text-dark">Base Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control fw-bold" name="base_product_name" required placeholder='e.g., "Floral Kurti"'>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Variants Table -->
                <div class="card premium-card">
                    <div class="card-header-premium d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-boxes me-2 text-primary"></i> 2. Variants & Pricing</div>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="addRowBtn" style="border-radius: 6px;">
                            <i class="fas fa-plus me-1"></i> Add Variant
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table premium-table mb-0" id="variantsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 18%;">Size <span class="text-danger">*</span></th>
                                        <th style="width: 18%;">Color <span class="text-danger">*</span></th>
                                        <th style="width: 12%;">Qty <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Base Price <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Sale Price <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Line Total</th>
                                        <th style="width: 7%; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="variant-row">
                                        <td><input type="text" class="form-control form-control-sm" name="variant_size[]" placeholder="M, L, XL" required></td>
                                        <td><input type="text" class="form-control form-control-sm" name="variant_color[]" placeholder="Red, Blue" required></td>
                                        <td><input type="number" class="form-control form-control-sm calc-qty text-center" name="qty[]" value="1" min="1" required></td>
                                        <td><input type="number" step="0.01" class="form-control form-control-sm calc-pprice text-end" name="purchase_price[]" placeholder="0.00" required></td>
                                        <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="sale_price[]" placeholder="0.00" required></td>
                                        <td><input type="text" class="form-control form-control-sm calc-line-total bg-light text-end fw-bold" readonly value="0.00"></td>
                                        <td class="text-center align-middle">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-light duplicate-row-btn border text-primary" title="Duplicate"><i class="fas fa-copy"></i></button>
                                                <button type="button" class="btn btn-sm btn-light remove-row-btn border text-danger" title="Remove"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="card premium-card">
                    <div class="card-header-premium">
                        <i class="fas fa-wallet me-2 text-primary"></i> 3. Immediate Payment
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="form-group mb-md-0">
                                    <label class="fw-bold text-dark">Payment Account</label>
                                    <select class="form-select select2" name="payment_account_id" id="payment_account_id" style="width: 100%;">
                                        <option value="">-- No Immediate Payment --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}" {{ str_contains(strtolower($acc->title), 'cash') ? 'selected' : '' }}>{{ $acc->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-md-0">
                                    <label class="fw-bold text-dark">Amount Paid Now</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-muted border-end-0">Rs</span>
                                        <input type="number" step="0.01" class="form-control form-control-lg fw-bold text-success border-start-0" name="payment_amount" id="payment_amount" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 text-end mt-4 mt-md-0">
                                <button type="submit" class="btn btn-premium-primary btn-lg w-100" id="submitBtn">
                                    <i class="fas fa-check me-2"></i> Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Summary Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="summary-card" style="margin-bottom: 0;">
                            <div class="row text-center">
                                <div class="col-md-3 border-end border-secondary">
                                    <div class="summary-label">Previous Balance</div>
                                    <div class="summary-value text-info" id="displayPrevBalance">0.00</div>
                                </div>
                                <div class="col-md-3 border-end border-secondary">
                                    <div class="summary-label">Purchase Total</div>
                                    <div class="summary-value text-warning" id="displayPurchaseTotal">0.00</div>
                                </div>
                                <div class="col-md-3 border-end border-secondary">
                                    <div class="summary-label">Paid Now</div>
                                    <div class="summary-value text-success" id="displayPaidAmount">0.00</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="summary-label">Remaining Balance</div>
                                    <div class="summary-value text-danger" id="displayRemainingBalance">0.00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var vendorBalances = @json($vendorBalances ?? []);

    // Initialize Select2 with modern theme
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // CRITICAL FIX: Ensure the search box actually gets focus when opened.
    // If it doesn't get focus, Up/Down arrow keys will just scroll the page instead of navigating the dropdown!
    $(document).on('select2:open', function() {
        let searchField = document.querySelector('.select2-search__field');
        if (searchField) {
            searchField.focus();
        }
    });

    // Make Select2 open on focus (when tabbing into it)
    $(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
        var $select = $(this).closest(".select2-container").siblings('select:enabled');
        if (!$select.data('select2').isOpen()) {
            $select.select2('open');
        }
    });

    // Prevent double-open bug when clicking with mouse
    $('select.select2').on('select2:closing', function (e) {
        $(e.target).data("select2").$selection.one('focus focusin', function (e) {
            e.stopPropagation();
        });
    });

    // Update Summary Logic
    function updateSummary() {
        var purchaseTotal = 0;
        $('#variantsTable tbody tr').each(function() {
            var qty = parseFloat($(this).find('.calc-qty').val()) || 0;
            var price = parseFloat($(this).find('.calc-pprice').val()) || 0;
            var lineTotal = qty * price;
            $(this).find('.calc-line-total').val(lineTotal.toFixed(2));
            purchaseTotal += lineTotal;
        });

        $('#displayPurchaseTotal').text(purchaseTotal.toFixed(2));

        var prevBalance = 0;
        var vendorId = $('#vendor_id').val();
        
        if (vendorId !== '') {
            prevBalance = parseFloat(vendorBalances[vendorId]) || 0;
        } else if ($('#new_vendor_name').val().trim() !== '') {
            prevBalance = parseFloat($('#opening_balance').val()) || 0;
        }

        $('#displayPrevBalance').text(prevBalance.toFixed(2));

        var paidNow = parseFloat($('#payment_amount').val()) || 0;
        $('#displayPaidAmount').text(paidNow.toFixed(2));

        var remaining = prevBalance + purchaseTotal - paidNow;
        $('#displayRemainingBalance').text(remaining.toFixed(2));
    }

    // Auto-fill payment amount when account is selected, ONLY if payment is currently 0 or empty
    $('#payment_account_id').on('change', function() {
        if($(this).val() !== '') {
            var purchaseTotal = parseFloat($('#displayPurchaseTotal').text()) || 0;
            var currentPayment = parseFloat($('#payment_amount').val()) || 0;
            if (currentPayment === 0 && purchaseTotal > 0) {
                $('#payment_amount').val(purchaseTotal.toFixed(2));
                updateSummary();
            }
        } else {
            $('#payment_amount').val('');
            updateSummary();
        }
    });

    // Vendor Logic
    $('#new_vendor_name').on('input', function() {
        if($(this).val().trim() !== '') {
            $('#vendor_id').val('').trigger('change.select2');
            $('#openingBalanceWrapper').slideDown(200);
        } else {
            $('#openingBalanceWrapper').slideUp(200);
            $('#opening_balance').val('');
        }
        updateSummary();
    });

    $('#vendor_id').on('change', function() {
        if($(this).val() !== '') {
            $('#new_vendor_name').val('');
            $('#openingBalanceWrapper').slideUp(200);
            $('#opening_balance').val('');
        }
        updateSummary();
    });

    $('#opening_balance, #payment_amount, .calc-qty, .calc-pprice').on('input', function() {
        updateSummary();
    });

    // Add Row
    $('#addRowBtn').on('click', function() {
        var newRow = `
            <tr class="variant-row">
                <td><input type="text" class="form-control form-control-sm" name="variant_size[]" placeholder="M, L, XL" required></td>
                <td><input type="text" class="form-control form-control-sm" name="variant_color[]" placeholder="Red, Blue" required></td>
                <td><input type="number" class="form-control form-control-sm calc-qty text-center" name="qty[]" value="1" min="1" required></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm calc-pprice text-end" name="purchase_price[]" placeholder="0.00" required></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm text-end" name="sale_price[]" placeholder="0.00" required></td>
                <td><input type="text" class="form-control form-control-sm calc-line-total bg-light text-end fw-bold" readonly value="0.00"></td>
                <td class="text-center align-middle">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-light duplicate-row-btn border text-primary" title="Duplicate"><i class="fas fa-copy"></i></button>
                        <button type="button" class="btn btn-sm btn-light remove-row-btn border text-danger" title="Remove"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
        $('#variantsTable tbody').append(newRow);
        updateSummary();
    });

    // Duplicate Row
    $(document).on('click', '.duplicate-row-btn', function() {
        var $currentRow = $(this).closest('tr');
        var $newRow = $currentRow.clone();
        
        $newRow.find('input[name="variant_size[]"]').val('');
        $newRow.find('input[name="variant_color[]"]').val('');
        $newRow.find('.calc-qty').val(1);
        $newRow.find('input[name="purchase_price[]"]').val($currentRow.find('input[name="purchase_price[]"]').val());
        $newRow.find('input[name="sale_price[]"]').val($currentRow.find('input[name="sale_price[]"]').val());
        
        $currentRow.after($newRow);
        updateSummary();
    });

    // Remove Row
    $(document).on('click', '.remove-row-btn', function() {
        if($('#variantsTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
            updateSummary();
        } else {
            Swal.fire('Warning', 'You must have at least one variant row.', 'warning');
        }
    });

    // Submit handler
    $('#quickPurchaseForm').on('submit', function(e) {
        e.preventDefault();

        if($('#vendor_id').val() === '' && $('#new_vendor_name').val().trim() === '') {
            Swal.fire('Error', 'Please select a vendor or type a new vendor name.', 'error');
            return;
        }

        var $btn = $('#submitBtn');
        var originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Saving...').prop('disabled', true);

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message
                    }).then(() => {
                        window.location.href = res.redirect_url;
                    });
                }
            },
            error: function(err) {
                $btn.html(originalHtml).prop('disabled', false);
                var errors = err.responseJSON && err.responseJSON.errors ? err.responseJSON.errors : null;
                var msg = 'Something went wrong.';
                if(errors) {
                    msg = Object.values(errors).map(e => e[0]).join('<br>');
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Subcategory load
    $('#category_id').on('change', function() {
        var cid = $(this).val();
        if (cid) {
            $.get('/get-subcategories/' + cid, function(d) {
                $('#sub_category_id').empty().append('<option value="">-- Select Subcategory --</option>');
                $.each(d, function(_, v) {
                    $('#sub_category_id').append('<option value="' + v.id + '">' + v.name + '</option>');
                });
            });
        } else {
            $('#sub_category_id').empty().append('<option value="">-- Select Subcategory --</option>');
        }
    });

    // Init Summary
    updateSummary();
});
</script>
@endpush
