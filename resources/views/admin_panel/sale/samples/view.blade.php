<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Challan • {{ $sample->sample_no }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 24px 0;
        }
        .challan-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            padding: 32px;
            position: relative;
        }
        .watermark-banner {
            background: #fdf2f8;
            border: 1.5px dashed #ec4899;
            color: #be185d;
            font-weight: 800;
            letter-spacing: 1px;
            padding: 8px 16px;
            border-radius: 8px;
            text-align: center;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .challan-header {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .badge-sample {
            background: #8b5cf6;
            color: #ffffff;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
        }
        .sample-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
        }
        .sample-table td {
            padding: 10px 12px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            margin-top: 20px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            padding-top: 6px;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-align: center;
            margin-top: 60px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .challan-container {
                box-shadow: none;
                border: none;
                padding: 16px;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="challan-container">
        <!-- Print / Close Controls -->
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <button onclick="window.history.back()" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </button>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="fas fa-print me-1"></i> Print Challan
                </button>
            </div>
        </div>

        <!-- FREE SAMPLE WATERMARK BANNER -->
        <div class="watermark-banner">
            <i class="fas fa-certificate me-2"></i> PHYSICIAN SAMPLE &bull; FREE OF COST &bull; NOT FOR SALE
        </div>

        <!-- HEADER -->
        <div class="challan-header d-flex justify-content-between align-items-start">
            <div>
                <h4 class="fw-bold mb-1 text-primary">MEDICAL SAMPLE CHALLAN</h4>
                <div class="text-muted small">Promotional Samples Distribution Record</div>
            </div>
            <div class="text-end">
                <div class="badge-sample mb-1 d-inline-block">
                    <i class="fas fa-hashtag me-1"></i> {{ $sample->sample_no }}
                </div>
                <div class="text-muted small">Date: <strong>{{ $sample->sample_date ? $sample->sample_date->format('d/m/Y') : date('d/m/Y') }}</strong></div>
            </div>
        </div>

        <!-- DETAILS ROW -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="p-3 bg-light rounded border">
                    <div class="text-uppercase small fw-bold text-muted mb-1">Delivered To:</div>
                    <div class="fw-bold fs-6 text-dark">{{ $sample->doctor_name ?: (optional($sample->customer)->customer_name ?: 'Doctor / Chemist') }}</div>
                    @if(optional($sample->customer)->customer_id)
                        <div class="small text-muted">Customer Code: {{ $sample->customer->customer_id }}</div>
                    @endif
                    @if(optional($sample->customer)->zone)
                        <div class="small text-muted">Area / Town: {{ $sample->customer->zone }}</div>
                    @endif
                </div>
            </div>
            <div class="col-6">
                <div class="p-3 bg-light rounded border">
                    <div class="text-uppercase small fw-bold text-muted mb-1">Dispatch Details:</div>
                    <div class="small">Warehouse: <strong>{{ optional($sample->warehouse)->name ?: 'Main Warehouse' }}</strong></div>
                    @if($sample->mr_name)
                        <div class="small">MR / Field Rep: <strong>{{ $sample->mr_name }}</strong></div>
                    @endif
                    @if($sample->remarks)
                        <div class="small text-muted mt-1">Remarks: <em>{{ $sample->remarks }}</em></div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SAMPLE ITEMS TABLE -->
        <table class="table sample-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Product Name</th>
                    <th class="text-center" style="width: 100px;">Pack</th>
                    <th class="text-center" style="width: 120px;">Sample Qty</th>
                    <th class="text-end" style="width: 120px;">Rate</th>
                </tr>
            </thead>
            <tbody>
                @php $totalPcs = 0; @endphp
                @foreach($sample->items as $idx => $it)
                    @php 
                        $totalPcs += (float)$it->qty; 
                        $p = $it->product;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-dark">{{ $p ? $p->item_name : 'Product' }}</strong>
                            @if($p && $p->item_code)
                                <small class="text-muted d-block font-monospace">{{ $p->item_code }}</small>
                            @endif
                        </td>
                        <td class="text-center text-muted">{{ $p ? ($p->pack_size ?: ($p->size_val ?: "1'S")) : "1'S" }}</td>
                        <td class="text-center fw-bold text-primary font-monospace">{{ number_format($it->qty, 0) }} Pcs</td>
                        <td class="text-end text-success fw-bold font-monospace">FREE (Rs. 0)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTALS & VALUE SUMMARY -->
        <div class="summary-box d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted small">Total Samples Given:</span>
                <span class="fs-6 fw-bold ms-2 text-dark">{{ number_format($totalPcs, 0) }} Pieces</span>
            </div>
            <div class="text-end">
                <span class="text-muted small">Amount Payable:</span>
                <span class="fs-5 fw-bold text-success ms-2 font-monospace">Rs. 0.00</span>
            </div>
        </div>

        <!-- SIGNATURES -->
        <div class="row mt-5 pt-3">
            <div class="col-4">
                <div class="sig-line">Prepared By (Dispatcher)</div>
            </div>
            <div class="col-4">
                <div class="sig-line">Delivered By (Medical Rep)</div>
            </div>
            <div class="col-4">
                <div class="sig-line">Received By (Doctor / Chemist Stamp)</div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
