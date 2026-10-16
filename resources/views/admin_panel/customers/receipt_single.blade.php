<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Voucher - {{ $payment->receipt_no }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/font-awesome/css/all.min.css') }}">
    <style>
        /* Base Print Styles */
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                padding: 10px !important;
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

        .receipt-card {
            width: 85mm;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            position: relative;
        }

        .company-header {
            text-align: center;
            border-bottom: 1.5px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 3px 0;
        }
        .company-sub {
            font-size: 11px;
            color: #333;
            margin: 0;
            line-height: 1.3;
        }

        .receipt-title {
            font-size: 13px;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            border: 1.5px solid #000;
            padding: 3px 0;
            margin: 8px 0;
            border-radius: 4px;
            background: #f8fafc;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .info-label {
            font-weight: 700;
            color: #444;
        }
        .info-val {
            font-weight: 600;
            text-align: right;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .balance-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            margin: 8px 0;
        }

        .amount-highlight {
            font-size: 15px;
            font-weight: 800;
            color: #000;
        }

        .words-box {
            font-size: 11px;
            font-style: italic;
            background: #f1f5f9;
            padding: 6px 8px;
            border-radius: 4px;
            margin: 6px 0;
            border-left: 3px solid #000;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            padding-top: 8px;
        }
        .sig-block {
            width: 45%;
            text-align: center;
            border-top: 1px solid #000;
            font-size: 10px;
            font-weight: 700;
            padding-top: 3px;
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
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <button class="btn-tool btn-close-tool" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div class="receipt-card">

        {{-- Company Header --}}
        <div class="company-header">
            <h1 class="company-name">{{ $companyName }}</h1>
            @if($companyAddress)<p class="company-sub">{{ $companyAddress }}</p>@endif
            <p class="company-sub">Tel: {{ $companyPhone }}</p>
        </div>

        {{-- Receipt Title --}}
        <div class="receipt-title">
            Customer Payment Receipt Voucher
        </div>

        {{-- Meta Details --}}
        <div class="info-row">
            <span class="info-label">Receipt #:</span>
            <span class="info-val font-monospace"><strong>{{ $payment->receipt_no }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Batch Ref:</span>
            <span class="info-val font-monospace">{{ $payment->batch_no ?: '-' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Date & Time:</span>
            <span class="info-val">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-M-Y') }} {{ $payment->created_at ? $payment->created_at->format('h:i A') : '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Collected By:</span>
            <span class="info-val">{{ $payment->user->name ?? 'Admin / Cashier' }}</span>
        </div>

        <div class="divider"></div>

        {{-- Customer Info --}}
        <div class="info-row">
            <span class="info-label">Customer:</span>
            <span class="info-val" style="font-size: 13px;"><strong>{{ $payment->customer->customer_name ?? 'N/A' }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Customer Code:</span>
            <span class="info-val">{{ $payment->customer->customer_id ?? 'CUST-' . $payment->customer_id }}</span>
        </div>
        @if(!empty($payment->customer->mobile))
        <div class="info-row">
            <span class="info-label">Contact:</span>
            <span class="info-val">{{ $payment->customer->mobile }}</span>
        </div>
        @endif

        <div class="divider"></div>

        {{-- Payment Method & Ref --}}
        <div class="info-row">
            <span class="info-label">Payment Mode:</span>
            <span class="info-val"><strong>{{ $payment->payment_method ?: 'Cash' }}</strong></span>
        </div>
        @if(!empty($payment->cheque_no))
        <div class="info-row">
            <span class="info-label">Ref / Cheque #:</span>
            <span class="info-val">{{ $payment->cheque_no }}</span>
        </div>
        @endif
        @if($payment->account)
        <div class="info-row">
            <span class="info-label">Deposit To:</span>
            <span class="info-val">{{ $payment->account->title }}</span>
        </div>
        @endif

        {{-- Ledger Balance Breakdown Box --}}
        <div class="balance-box">
            <div class="info-row">
                <span class="info-label">Previous Balance:</span>
                <span class="info-val">PKR {{ number_format($previousBalance, 2) }}</span>
            </div>
            <div class="info-row" style="border-top: 1px dashed #cbd5e1; padding-top: 4px; margin-top: 4px;">
                <span class="info-label amount-highlight">AMOUNT RECEIVED:</span>
                <span class="info-val amount-highlight">PKR {{ number_format($currentPaid, 2) }}</span>
            </div>
            <div class="info-row" style="border-top: 1px solid #000; padding-top: 4px; margin-top: 4px;">
                <span class="info-label">Remaining Balance:</span>
                <span class="info-val" style="font-weight: 800; font-size: 13px;">
                    PKR {{ number_format($closingBalance, 2) }}
                </span>
            </div>
        </div>

        {{-- Amount in Words --}}
        <div class="words-box">
            <strong>Amount:</strong> {{ $amountInWords }}
        </div>

        @if(!empty($payment->note))
        <div class="info-row" style="font-size: 10.5px; color:#444; margin-top:4px;">
            <span><strong>Note:</strong> {{ $payment->note }}</span>
        </div>
        @endif

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-block">Customer Signature</div>
            <div class="sig-block">Cashier / Authorized</div>
        </div>

        <div style="text-align: center; font-size: 9px; color: #777; margin-top: 12px;">
            Software by ProWave Solutions &bull; Printed: {{ date('d-M-Y h:i A') }}
        </div>

    </div>

</body>
</html>
