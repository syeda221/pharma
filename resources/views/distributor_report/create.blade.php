@extends('admin_panel.layout.app')
@section('content')
<div class="container-fluid">
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow border-0" style="border-radius: 10px;">
                <div class="card-header bg-white text-dark d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #0d6efd;">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i> Distributor Sales Report</h5>
                    <a href="{{ route('distributor-reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to List</a>
                </div>
                <div class="card-body" style="background-color: #f8f9fa;">
                    
                    <!-- Summary Cards -->
                    <div class="row mb-4" id="summaryCards" style="display:none;">
                        <div class="col-md-4 mb-2">
                            <div class="card border-primary shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-box fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Total Items Holding</h6>
                                        <h4 class="mb-0 fw-bold text-dark" id="totalStockGiven">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="card border-success shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-check-circle fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Total Items Sold</h6>
                                        <h4 class="mb-0 fw-bold text-dark" id="totalPiecesSold">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="card border-info shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                                <div class="card-body d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="fas fa-money-bill-wave fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Current Sale Revenue</h6>
                                        <h4 class="mb-0 fw-bold text-dark">Rs. <span id="estRevenue">0</span></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="reportForm" class="bg-white p-4 shadow-sm" style="border-radius: 8px; border: 1px solid #dee2e6;">
                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Report Details</h6>
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="fw-bold text-secondary">Filter by Zone</label>
                                <select id="zone_filter" class="form-control select2" style="border-radius: 6px;">
                                    <option value="">All Zones</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->zone }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-secondary">Select Distributor <span class="text-danger">*</span></label>
                                <select id="distributor_id" class="form-control select2" required style="border-radius: 6px;">
                                    <option value="">-- Choose a Distributor --</option>
                                    @foreach($distributors as $distributor)
                                        <option value="{{ $distributor->id }}" data-zone="{{ $distributor->zone }}">{{ $distributor->customer_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-secondary">Report Date <span class="text-danger">*</span></label>
                                <input type="date" id="report_date" class="form-control" value="{{ date('Y-m-d') }}" required style="border-radius: 6px;">
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold text-secondary">Remarks / Notes</label>
                                <input type="text" id="notes" class="form-control" placeholder="Any details..." style="border-radius: 6px;">
                            </div>
                        </div>

                        <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 mt-4">Stock Sales Entry</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="stockTable">
                                <thead class="bg-secondary text-white text-center">
                                    <tr>
                                        <th style="width: 35%;">Product Name</th>
                                        <th style="width: 8%;">PPB</th>
                                        <th style="width: 15%;">Given Stock</th>
                                        <th style="width: 12%;">Sold Qty</th>
                                        <th style="width: 15%;">Remaining Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="stockBody">
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 text-light"></i><br>
                                            Please select a distributor to load holding stock.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" id="btnSave" style="border-radius: 6px;">
                                <i class="fas fa-save me-2"></i> Submit Sales Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentStocks = [];

    // Initialize select2 if available
    if($.fn.select2) {
        $('.select2').select2({ width: '100%' });
    }

    // Save original options
    let distributorOptions = $('#distributor_id option').clone();

    $('#zone_filter').on('change', function() {
        let zoneId = $(this).val();
        
        if($.fn.select2) {
            $('#distributor_id').select2('destroy');
        }
        
        $('#distributor_id').html('');
        $('#distributor_id').append(distributorOptions.clone());
        
        if (zoneId) {
            $('#distributor_id option').each(function() {
                if ($(this).val() !== "" && $(this).data('zone') != zoneId) {
                    $(this).remove();
                }
            });
        }
        
        $('#distributor_id').val('');
        if($.fn.select2) {
            $('#distributor_id').select2({ width: '100%' });
        }
        $('#distributor_id').trigger('change');
    });

    $('#distributor_id').on('change', function() {
        let distId = $(this).val();
        let tbody = $('#stockBody');
        
        if (!distId) {
            tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-box-open fa-3x mb-3 text-light"></i><br>Please select a distributor to load holding stock.</td></tr>');
            currentStocks = [];
            $('#summaryCards').fadeOut();
            return;
        }

        tbody.html('<tr><td colspan="5" class="text-center py-4 text-primary"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading stock...</td></tr>');
        $('#summaryCards').hide();

        $.ajax({
            url: '/api/distributor-stocks/' + distId,
            type: 'GET',
            success: function(res) {
                if (res.status && res.data.length > 0) {
                    currentStocks = res.data;
                    let rows = '';
                    currentStocks.forEach((item, index) => {
                        let prodName = item.product ? item.product.item_name : 'Unknown Product';
                        let ppb = parseInt(item.pieces_per_box) || 1;
                        let givenCartons = item.cartons_given;
                        let givenPieces = item.pieces_given;

                        rows += `
                            <tr data-index="${index}">
                                <td class="fw-bold text-dark">${prodName}</td>
                                <td class="text-center text-muted">${ppb}</td>
                                <td class="text-center bg-light">
                                    <span class="badge bg-secondary mb-1">Ctn: ${givenCartons}</span><br>
                                    <span class="badge bg-secondary">Pcs: ${givenPieces}</span>
                                    <input type="hidden" class="given-total" value="${item.current_quantity}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="number" class="form-control sold-qty fw-bold text-center" min="0" value="0" style="flex: 1;">
                                        <button type="button" class="btn btn-sm btn-outline-primary unit-toggle px-2 py-1" data-unit="ctn" style="font-weight: 700; border-radius: 4px; flex-shrink: 0; min-width: 40px;">
                                            Ctn
                                        </button>
                                    </div>
                                    <input type="hidden" class="calc-sold-total" value="0">
                                </td>
                                <td class="text-center bg-light">
                                    <span class="badge bg-success mb-1 rem-cartons">Ctn: ${givenCartons}</span><br>
                                    <span class="badge bg-success rem-pieces">Pcs: ${givenPieces}</span>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.html(rows);
                    $('#summaryCards').fadeIn();
                    updateSummary();
                } else {
                    currentStocks = [];
                    tbody.html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>No stock available with this distributor.</td></tr>');
                    $('#summaryCards').fadeOut();
                }
            },
            error: function() {
                tbody.html('<tr><td colspan="5" class="text-center py-4 text-danger">Failed to fetch stock data.</td></tr>');
            }
        });
    });

    $(document).on('click', '.unit-toggle', function() {
        let currentUnit = $(this).data('unit');
        if(currentUnit === 'ctn') {
            $(this).data('unit', 'pcs').text('Pcs').removeClass('btn-outline-primary').addClass('btn-outline-success');
        } else {
            $(this).data('unit', 'ctn').text('Ctn').removeClass('btn-outline-success').addClass('btn-outline-primary');
        }
        $(this).closest('tr').find('.sold-qty').trigger('input');
    });

    $(document).on('input', '.sold-qty', function() {
        let row = $(this).closest('tr');
        let index = row.data('index');
        let item = currentStocks[index];
        
        let ppb = parseInt(item.pieces_per_box) || 1;
        let givenTotal = parseInt(item.current_quantity) || 0;
        
        let qty = parseFloat(row.find('.sold-qty').val()) || 0;
        if(qty < 0) { row.find('.sold-qty').val(0); qty = 0; }
        
        let unit = row.find('.unit-toggle').data('unit');
        let soldTotal = 0;
        
        if (unit === 'ctn') {
            soldTotal = Math.round(qty * ppb); // allow decimals if they sell half carton
        } else {
            soldTotal = Math.round(qty);
        }
        
        if (soldTotal > givenTotal) {
            alert('Total sold quantity cannot exceed given stock!');
            row.find('.sold-qty').val(0);
            soldTotal = 0;
        }
        
        row.find('.calc-sold-total').val(soldTotal);
        
        let remTotal = givenTotal - soldTotal;
        let remCartons = Math.floor(remTotal / ppb);
        let remPieces = remTotal % ppb;
        
        row.find('.rem-cartons').text('Ctn: ' + remCartons);
        row.find('.rem-pieces').text('Pcs: ' + remPieces);

        updateSummary();
    });

    function updateSummary() {
        let totalGiven = 0;
        let totalSold = 0;
        let totalRevenue = 0;
        
        currentStocks.forEach((item, index) => {
            totalGiven += parseInt(item.current_quantity) || 0;
            
            let row = $('#stockBody tr').eq(index);
            let itemTotalSold = parseInt(row.find('.calc-sold-total').val()) || 0;
            totalSold += itemTotalSold;
            
            totalRevenue += itemTotalSold * (parseFloat(item.price_per_piece) || 0);
        });

        $('#totalStockGiven').text(totalGiven);
        $('#totalPiecesSold').text(totalSold);
        $('#estRevenue').text(totalRevenue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    $('#reportForm').on('submit', function(e) {
        e.preventDefault();
        
        let distId = $('#distributor_id').val();
        if (!distId || currentStocks.length === 0) return;

        let btn = $('#btnSave');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        let items = [];
        let hasError = false;

        $('#stockBody tr').each(function() {
            let index = $(this).data('index');
            let item = currentStocks[index];
            let givenTotal = parseInt(item.current_quantity) || 0;
            let soldTotal = parseInt($(this).find('.calc-sold-total').val()) || 0;

            if (soldTotal > givenTotal) {
                hasError = true;
            }
            
            items.push({
                product_id: item.product_id,
                given_quantity: givenTotal,
                sold_quantity: soldTotal
            });
        });

        if(hasError) {
            alert('Invalid quantities detected. Please fix them before submitting.');
            btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Sales Report');
            return;
        }

        let payload = {
            distributor_id: distId,
            report_date: $('#report_date').val(),
            notes: $('#notes').val(),
            items: items
        };

        $.ajax({
            url: '/api/distributor-reports',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            success: function(res) {
                if (res.status) {
                    alert('Report saved successfully!');
                    window.location.href = "{{ route('distributor-reports.index') }}";
                } else {
                    alert('Error: ' + res.message);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Sales Report');
                }
            },
            error: function(err) {
                alert('Something went wrong. Please check console.');
                console.error(err);
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i> Submit Sales Report');
            }
        });
    });
});
</script>
@endsection
