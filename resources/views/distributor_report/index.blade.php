@extends('admin_panel.layout.app')
@section('content')
<style>
    @media print {
        .no-print, header, footer, nav, .rt_nav_header, .sidebar, .main-sidebar, .main-footer {
            display: none !important;
        }
        .card, .card-body, #printArea {
            box-shadow: none !important;
            border: none !important;
            background-color: #fff !important;
            padding: 0 !important;
        }
        
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #000 !important;
        }
        .table th {
            background-color: #e9ecef !important;
            color: #000 !important;
        }
        .table-responsive {
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
        }
        @page {
            size: auto;
            margin: 10mm;
        }
    }
</style>
<div class="container-fluid">
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center no-print">
            <h5 class="mb-0">Distributor Sales Reports</h5>
            <div>
                <button type="button" class="btn btn-secondary btn-sm me-2" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                <a href="{{ route('distributor-reports.create') }}" class="btn btn-primary btn-sm">Add New Report</a>
            </div>
        </div>
        <div class="card-body bg-light">
            <!-- Summary Cards -->
            <div class="row mb-4 no-print">
                <div class="col-md-3">
                    <div class="card border-primary shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fas fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.9rem;">Total Reports</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ $reports->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fas fa-box-open fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.9rem;">Total Items Sold</h6>
                                <h4 class="mb-0 fw-bold text-dark">{{ $reports->sum(function($r) { return $r->items->sum('sold_quantity'); }) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info shadow-sm h-100" style="border-left: 4px solid #0dcaf0 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fas fa-money-bill-wave fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.9rem;">Total Revenue</h6>
                                @php
                                    $rev = 0;
                                    $profit = 0;
                                    foreach($reports as $r) {
                                        foreach($r->items as $item) {
                                            $sale_price = $item->product ? $item->product->sale_price_per_piece : 0;
                                            $purchase_price = $item->product ? $item->product->purchase_price_per_piece : 0;
                                            $qty = $item->sold_quantity;
                                            $rev += ($qty * $sale_price);
                                            $profit += ($qty * ($sale_price - $purchase_price));
                                        }
                                    }
                                @endphp
                                <h5 class="mb-0 fw-bold text-dark">Rs. {{ number_format($rev) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1" style="font-size: 0.9rem;">Est. Profit</h6>
                                <h5 class="mb-0 fw-bold text-dark">Rs. {{ number_format($profit) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Form -->
            <form method="GET" action="{{ route('distributor-reports.index') }}" class="row mb-4 no-print align-items-end p-3 bg-white shadow-sm" style="border-radius: 8px; border: 1px solid #dee2e6;">
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2 w-100">Filter Reports</h6>
                <div class="col-md-2 mb-2">
                    <label class="fw-bold text-secondary">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="fw-bold text-secondary">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="fw-bold text-secondary">Zone</label>
                    <select name="zone_id" id="zone_filter_idx" class="form-control select2">
                        <option value="">All Zones</option>
                        @foreach(App\Models\Zone::all() as $z)
                            <option value="{{ $z->id }}" {{ request('zone_id') == $z->id ? 'selected' : '' }}>{{ $z->zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="fw-bold text-secondary">Distributor</label>
                    <select name="distributor_id" id="distributor_id_idx" class="form-control select2">
                        <option value="">All Distributors</option>
                        @foreach($distributors as $distributor)
                            <option value="{{ $distributor->id }}" data-zone="{{ $distributor->zone }}" {{ request('distributor_id') == $distributor->id ? 'selected' : '' }}>
                                {{ $distributor->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
            </form>
            
            <div id="printArea" class="bg-white p-3 shadow-sm" style="border-radius: 8px;">
                <!-- Print Header (Hidden on screen) -->
                <div class="d-none d-print-block mb-4 text-center">
                    <h3 class="fw-bold mb-1">THREE STARS MEDICAL</h3>
                    <h5 class="text-secondary mb-3">Distributor Sales Report</h5>
                    <div class="d-flex justify-content-between text-start" style="font-size: 14px; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px;">
                        <div>
                            <strong>From Date:</strong> {{ request('from_date') ?: 'N/A' }} <br>
                            <strong>To Date:</strong> {{ request('to_date') ?: 'N/A' }}
                        </div>
                        <div>
                            @php
                                $printZone = request('zone_id') ? \App\Models\Zone::find(request('zone_id'))->zone : 'All Zones';
                                $printDist = request('distributor_id') ? \App\Models\Customer::find(request('distributor_id'))->customer_name : 'All Distributors';
                            @endphp
                            <strong>Zone:</strong> {{ $printZone }} <br>
                            <strong>Distributor:</strong> {{ $printDist }}
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center" id="reportsTable">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th style="width: 10%;">Report ID</th>
                                <th style="width: 12%;">Date</th>
                                <th style="width: 20%;">Distributor</th>
                                <th style="width: 12%;">Total Items Sold</th>
                                <th style="width: 15%;">Revenue</th>
                                <th style="width: 11%;">Zone</th>
                                <th style="width: 20%;">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalItemsPrint = 0;
                                $totalRevPrint = 0;
                            @endphp
                            @forelse($reports as $report)
                                @php
                                    $r_rev = 0;
                                    $r_items = $report->items->sum('sold_quantity');
                                    foreach($report->items as $i) {
                                        $p = $i->product ? $i->product->sale_price_per_piece : 0;
                                        $r_rev += ($i->sold_quantity * $p);
                                    }
                                    $totalItemsPrint += $r_items;
                                    $totalRevPrint += $r_rev;
                                @endphp
                                <tr>
                                    <td class="fw-bold">#{{ $report->id }}</td>
                                    <td>{{ date('d M, Y', strtotime($report->report_date)) }}</td>
                                    <td class="fw-bold text-dark">{{ $report->distributor ? $report->distributor->customer_name : 'N/A' }}</td>
                                    <td class="fw-bold text-dark" style="font-size: 15px;">{{ $r_items }}</td>
                                    <td class="text-primary fw-bold">Rs. {{ number_format($r_rev, 2) }}</td>
                                    <td>{{ $report->zone ? $report->zone->zone : 'N/A' }}</td>
                                    <td>{{ $report->notes }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2 text-light"></i><br>
                                        No reports found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Grand Total:</td>
                                <td style="font-size: 15px;">{{ $totalItemsPrint }}</td>
                                <td class="text-primary" style="font-size: 15px;">Rs. {{ number_format($totalRevPrint, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Print Footer -->
                <div class="d-none d-print-block mt-5 pt-5">
                    <div class="d-flex justify-content-between">
                        <div class="text-center" style="width: 200px; border-top: 1px solid #000;">Prepared By</div>
                        <div class="text-center" style="width: 200px; border-top: 1px solid #000;">Authorized Signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let distributorOptionsIdx = $('#distributor_id_idx option').clone();

    $('#zone_filter_idx').on('change', function() {
        let zoneId = $(this).val();
        let selectedDist = $('#distributor_id_idx').val();
        $('#distributor_id_idx').html(distributorOptionsIdx);
        
        if (zoneId) {
            $('#distributor_id_idx option').each(function() {
                if ($(this).val() !== "" && $(this).data('zone') != zoneId) {
                    $(this).remove();
                }
            });
        }
        
        // Restore selection if it still exists
        if($('#distributor_id_idx option[value="'+selectedDist+'"]').length > 0) {
            $('#distributor_id_idx').val(selectedDist);
        } else {
            $('#distributor_id_idx').val('');
        }
    });

    // trigger on load if zone is selected
    if($('#zone_filter_idx').val()) {
        $('#zone_filter_idx').trigger('change');
    }
});
</script>
@endsection
