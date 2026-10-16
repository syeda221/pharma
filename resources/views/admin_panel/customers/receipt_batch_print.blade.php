<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Recovery Sheet - {{ $batch_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/all.min.css') }}">
    <style>
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 10mm;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 25px 0;
            color: #000;
        }

        .page-container {
            width: 210mm; /* A4 width */
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
        }

        .company-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .company-title {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
            color: #0f172a;
        }
        .company-sub {
            font-size: 11px;
            color: #475569;
            margin: 0;
        }

        .sheet-badge {
            background: #0f172a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 15px;
        }
        .meta-item {
            font-size: 12px;
        }
        .meta-label {
            font-weight: 700;
            color: #64748b;
            font-size: 10.5px;
            text-transform: uppercase;
            display: block;
        }
        .meta-val {
            font-weight: 700;
            color: #0f172a;
            font-size: 12.5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }
        .data-table th, 
        .data-table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            vertical-align: middle;
        }
        .data-table thead th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.5px;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .amount-col {
            text-align: right;
            font-family: monospace;
            font-weight: 700;
            font-size: 12px;
        }

        .signatures-area {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-top: 50px;
            padding-top: 10px;
        }
        .sig-box {
            text-align: center;
            border-top: 1.5px solid #000;
            padding-top: 5px;
            font-size: 11px;
            font-weight: 700;
        }

        .print-toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }
        .btn-tool {
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
        }
        .btn-close-tool {
            background: #64748b;
            color: #fff;
        }
    </style>
</head>
<body>

    @php
        $companyName = \App\Models\Setting::get('company_name', 'TrendHub Pharma');
        $companyAddress = \App\Models\Setting::get('company_address', '');
        $companyPhone = \App\Models\Setting::get('company_phone', '+92 317 3836223');
    @endphp

    {{-- Toolbar (Hidden in Print) --}}
    <div class="print-toolbar no-print">
        <button class="btn-tool btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Print Sheet (A4)
        </button>
        <button class="btn-tool btn-close-tool" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div class="page-container">

        {{-- Header --}}
        <div class="company-header">
            <div>
                <h1 class="company-title">{{ $companyName }}</h1>
                @if($companyAddress)<p class="company-sub">{{ $companyAddress }}</p>@endif
                <p class="company-sub">Tel: {{ $companyPhone }}</p>
            </div>
            <div class="text-end">
                <div class="sheet-badge">Daily Customer Recovery Sheet</div>
                <div style="font-size: 11px; font-weight:700; margin-top: 4px; color:#475569;">
                    Printed: {{ date('d-M-Y h:i A') }}
                </div>
            </div>
        </div>

        {{-- Metadata Grid --}}
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Batch Reference</span>
                <span class="meta-val font-monospace">{{ $batch_no }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Recovery Date</span>
                <span class="meta-val">{{ \Carbon\Carbon::parse($first->payment_date)->format('d-M-Y') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Payment Accounts</span>
                <span class="meta-val">{{ $payments->pluck('account.title')->filter()->unique()->implode(', ') ?: 'Cash / Bank' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Recovery Officer / Cashier</span>
                <span class="meta-val">{{ $first->user->name ?? 'Admin / Staff' }}</span>
            </div>
        </div>

        {{-- Recovery Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px; text-align: center;">#</th>
                    <th style="width: 105px;">RECEIPT #</th>
                    <th>CUSTOMER NAME & CODE</th>
                    <th style="width: 85px;">CONTACT</th>
                    <th style="width: 100px; text-align: right;">PREV. BAL</th>
                    <th style="width: 115px; text-align: right; background: #1e3a8a;">RECEIVED (PKR)</th>
                    <th style="width: 100px; text-align: right;">NEW BAL</th>
                    <th style="width: 70px; text-align: center;">TYPE</th>
                    <th style="width: 105px;">DEPOSIT ACC</th>
                    <th style="width: 45px; text-align: center;">SLIP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $idx => $p)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $p->receipt_no }}</td>
                    <td>
                        <strong>{{ $p->customer->customer_name ?? 'N/A' }}</strong>
                        <div style="font-size: 9.5px; color:#555;">{{ $p->customer->customer_id ?? '' }}</div>
                    </td>
                    <td>{{ $p->customer->mobile ?? '-' }}</td>
                    <td class="amount-col">{{ number_format($p->prev_balance, 2) }}</td>
                    <td class="amount-col" style="color: #1e3a8a; font-size: 13px;">{{ number_format($p->amount, 2) }}</td>
                    <td class="amount-col" style="color: {{ $p->new_balance == 0 ? '#15803d' : '#000' }};">
                        {{ number_format($p->new_balance, 2) }}
                    </td>
                    <td style="text-align: center;">{{ $p->payment_method ?: 'Cash' }}</td>
                    <td>{{ $p->account->title ?? '-' }}</td>
                    <td style="text-align: center;">
                        @if($p->attachment)
                            <span title="Slip Attached" style="color:#15803d; font-weight: bold;">✓</span>
                        @else
                            <span style="color:#94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #0f172a;">
                    <td colspan="4" style="text-align: right; text-transform: uppercase;">Total Batch Collection ({{ count($payments) }} Customers):</td>
                    <td class="amount-col">{{ number_format($payments->sum('prev_balance'), 2) }}</td>
                    <td class="amount-col" style="font-size: 14px; color:#1e3a8a;">PKR {{ number_format($totalAmount, 2) }}</td>
                    <td class="amount-col">{{ number_format($payments->sum('new_balance'), 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        {{-- Amount in words --}}
        <div style="font-size: 11.5px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 7px 10px; margin-bottom: 20px;">
            <strong>Total Amount in Words:</strong> {{ $amountInWords }}
        </div>

        @if(!empty($first->note))
        <div style="font-size: 11px; color:#475569; margin-bottom: 25px;">
            <strong>Batch Remarks / Route Note:</strong> {{ $first->note }}
        </div>
        @endif

        {{-- Formal Signatures --}}
        <div class="signatures-area">
            <div class="sig-box">
                <div>Recovery Officer / Collector</div>
                <div style="font-size: 9.5px; color:#666; font-weight: normal; margin-top:2px;">Signature & Stamp</div>
            </div>
            <div class="sig-box">
                <div>Receiving Cashier</div>
                <div style="font-size: 9.5px; color:#666; font-weight: normal; margin-top:2px;">Signature & Stamp</div>
            </div>
            <div class="sig-box">
                <div>Accounts Manager / Auditor</div>
                <div style="font-size: 9.5px; color:#666; font-weight: normal; margin-top:2px;">Approved By</div>
            </div>
        </div>

    </div>

</body>
</html>
