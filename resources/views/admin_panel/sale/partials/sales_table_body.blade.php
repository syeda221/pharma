@foreach ($sales as $sale)
    @php
        $pNames = 'N/A';
        if ($sale->items && $sale->items->count() > 0) {
            $pNames = $sale->items
                ->map(fn($item) => optional($item->product)->item_name ?? '?')
                ->implode(', ');
        } elseif ($sale->product) {
            $pNames = $sale->product;
        }

        $statusBadge = '<span class="badge badge-warning text-dark border border-warning">Draft</span>';
        $isExchange = \Illuminate\Support\Str::startsWith($sale->reference, 'Exchange for');
        
        if ($sale->sale_status === 'posted') {
            if ($sale->is_booking) {
                $statusBadge = '<span class="badge badge-success border border-success"><i class="fas fa-check-circle me-1"></i>Confirmed Booking</span>';
            } elseif ($isExchange) {
                $statusBadge = '<span class="badge badge-info text-white border border-info"><i class="fas fa-exchange-alt me-1"></i>Exchange</span>';
            } else {
                $statusBadge = '<span class="badge badge-success border border-success">Posted</span>';
            }
        } elseif ($sale->sale_status === 'booked') {
            $statusBadge = '<span class="badge badge-warning text-dark border border-warning"><i class="fas fa-bookmark me-1"></i>Booked</span>';
        } elseif ($sale->sale_status === 'returned') {
            $statusBadge = '<span class="badge badge-danger border border-danger">Returned</span>';
        } elseif ($sale->sale_status == 1) {
            $statusBadge = '<span class="badge badge-danger border border-danger">Return</span>';
        } elseif ($sale->sale_status === null) {
            $statusBadge = '<span class="badge badge-success border border-success">Sale</span>';
        }

        if (($sale->returns && $sale->returns->count() > 0) && $sale->sale_status !== 'returned' && $sale->sale_status != 1) {
            $statusBadge .= '<br><small class="badge badge-danger border border-danger mt-1"><i class="fas fa-undo-alt me-1"></i> Partial Return</small>';
        }

        $inline_val = $sale->items ? $sale->items->sum('discount_amount') : 0;
        $bill_amount = $sale->total_bill_amount > 0 ? $sale->total_bill_amount : (float) $sale->per_total;
        $gross_subtotal = $bill_amount + $inline_val;
        $inline_pct = $gross_subtotal > 0 ? ($inline_val / $gross_subtotal) * 100 : 0;

        $collected = $sale->cash - $sale->change;
        $refunded = 0;
        if (isset($isExchange) && $isExchange && $collected <= 0) {
            $refundPayment = \App\Models\CustomerPayment::where('note', 'Refund Paid for POS Exchange #'.$sale->invoice_no)->first();
            if ($refundPayment) {
                $refunded = $refundPayment->amount;
            }
        }

        $origQty = $sale->total_items > 0 ? (float)$sale->total_items : (float)$sale->qty;
        $retQty = isset($sale->returned_qty) ? $sale->returned_qty : ($sale->returns ? $sale->returns->flatMap->items->sum('qty') : 0);
        $effQty = isset($sale->effective_qty) ? $sale->effective_qty : max(0, $origQty - $retQty);

        $retNet = isset($sale->total_returned) ? $sale->total_returned : ($sale->returns ? (float)$sale->returns->sum('net_amount') : 0);
        if (isset($sale->effective_net)) {
            $effNet = $sale->effective_net;
        } elseif ($sale->sale_status === 'returned' || $sale->sale_status == 1) {
            $effNet = $retNet > 0 ? max(0, (float)$sale->total_net - $retNet) : 0;
        } else {
            $effNet = max(0, (float)$sale->total_net - $retNet);
        }
    @endphp

    {{-- Table Row --}}
    <tr class="border-bottom-0">
        <td class="ps-3 fw-bold font-monospace">
            @if ($sale->invoice_no)
                <span class="text-primary fw-bold">{{ $sale->invoice_no }}</span>
                <small class="text-muted d-block" style="font-size: 11px;">#{{ $sale->id }}</small>
            @else
                <span class="text-muted">#{{ $sale->id }}</span>
            @endif
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-info-subtle text-info me-2 fw-bold d-flex align-items-center justify-content-center rounded-circle"
                    style="width: 32px; height: 32px; font-size: 14px; background-color: #e0f2fe; color: #0369a1;">
                    {{ strtoupper(substr(optional($sale->customer_relation)->customer_name ?? 'C', 0, 1)) }}
                </div>
                <span class="fw-medium text-dark">{{ optional($sale->customer_relation)->customer_name ?? 'N/A' }}</span>
            </div>
        </td>
        <td class="font-monospace text-dark">{{ $sale->reference ?? '-' }}</td>
        <td title="{{ $pNames }}" class="text-muted small">
            {{ \Illuminate\Support\Str::limit($pNames, 40) }}
        </td>
        <td class="text-center font-monospace">
            @if ($sale->sale_status === 'returned' || $sale->sale_status == 1)
                @if ($effQty > 0)
                    <span class="fw-bold">{{ number_format($effQty, 2) }}</span>
                    <small class="text-danger d-block" style="font-size: 10px;">(-{{ number_format($retQty > 0 ? $retQty : ($origQty - $effQty), 2) }} ret)</small>
                @else
                    <span class="text-danger fw-bold">0.00</span>
                    <small class="text-muted d-block text-decoration-line-through" style="font-size: 10px;">{{ number_format($origQty, 2) }}</small>
                @endif
            @elseif ($retQty > 0)
                <span class="fw-bold">{{ number_format($effQty, 2) }}</span>
                <small class="text-danger d-block" style="font-size: 10px;">(-{{ number_format($retQty, 2) }} ret)</small>
            @else
                {{ number_format($origQty, 2) }}
            @endif
        </td>
        <td class="text-end fw-bold text-dark font-monospace">
            Rs. {{ number_format($gross_subtotal, 2) }}
        </td>
        <td class="text-end text-dark font-monospace">
            Rs. {{ number_format($inline_val, 2) }}
            @if ($inline_val > 0)
                <div class="text-muted small mt-1" style="font-size: 10px;">({{ number_format($inline_pct, 1) }}%)</div>
            @endif
        </td>
        <td class="text-end text-dark font-monospace">
            @if ($sale->total_extradiscount > 0)
                @php
                    $add_val = $sale->total_extradiscount;
                    $add_pct = $bill_amount > 0 ? ($add_val / $bill_amount) * 100 : 0;
                @endphp
                <span class="badge rounded-pill border px-2 py-1" style="background-color: #fff8e1; color: #b78103; border-color: #ffe082 !important; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="fas fa-tag" style="font-size: 10px;"></i> Rs. {{ number_format($add_val, 2) }}
                </span>
                <div class="text-muted small mt-1" style="font-size: 10px;">({{ number_format($add_pct, 1) }}%)</div>
            @else
                <span class="text-muted">Rs. 0.00</span>
            @endif
        </td>
        <td class="text-end fw-bold font-monospace">
            @if (isset($isExchange) && $isExchange)
                @if ($collected > 0)
                    <span class="text-success">Rs. {{ number_format($collected, 2) }}</span>
                @elseif ($refunded > 0)
                    <span class="text-danger">-Rs. {{ number_format($refunded, 2) }}</span>
                @else
                    <span class="text-muted">Rs. 0.00</span>
                @endif
                <br><span class="badge badge-info text-white border border-info px-1 py-0 mt-1" style="font-size: 10px;"><i class="fas fa-exchange-alt me-1"></i>Exchange</span>
            @elseif ($sale->sale_status === 'returned' || $sale->sale_status == 1)
                @if ($effNet > 0)
                    <span class="text-success">Rs. {{ number_format($effNet, 2) }}</span>
                    <small class="text-danger d-block" style="font-size: 10px;">(-Rs. {{ number_format($retNet > 0 ? $retNet : ((float)$sale->total_net - $effNet), 2) }} ret)</small>
                @else
                    <span class="text-danger">Rs. 0.00</span>
                    <small class="text-muted d-block text-decoration-line-through" style="font-size: 10px;">(Rs. {{ number_format($sale->total_net, 2) }})</small>
                @endif
            @elseif ($retNet > 0)
                <span class="text-success">Rs. {{ number_format($effNet, 2) }}</span>
                <small class="text-danger d-block" style="font-size: 10px;">(-Rs. {{ number_format($retNet, 2) }} ret)</small>
            @else
                <span class="text-success">Rs. {{ number_format($sale->total_net, 2) }}</span>
            @endif
        </td>
        <td class="text-nowrap small text-muted">
            {{ $sale->created_at->format('d/m/Y') }}
        </td>
        <td>{!! $statusBadge !!}</td>
        <td class="pe-3 text-center">
            <div class="dropdown">
                <button class="btn btn-premium-action dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-expanded="false">
                    <i class="fas fa-ellipsis-v small me-1"></i> Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-right border-0 shadow-lg rounded-3">
                    @can('sales.edit')
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.edit', $sale->id) }}">
                                <i class="fas fa-edit text-primary fa-fw"></i> Edit (Simple)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('pos.index') }}?edit_id={{ $sale->id }}">
                                <i class="fas fa-cash-register text-success fa-fw"></i> Edit (POS Sale)
                            </a>
                        </li>
                    @endcan

                    @if ($sale->sale_status === 'draft' || $sale->sale_status === 'booked')
                        @can('sales.create')
                            <li>
                                <form action="{{ route('sales.confirm', $sale->id) }}" method="POST" class="confirm-booking-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-success d-flex align-items-center gap-2 py-2">
                                        <i class="fas fa-check-circle fa-fw"></i> Confirm Booking
                                    </button>
                                </form>
                            </li>
                        @endcan
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    @can('sales.view')
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.invoice', $sale->id) }}" target="_blank">
                                <i class="fas fa-file-invoice text-info fa-fw"></i> View Invoice
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.invoice', ['id' => $sale->id, 'type' => 'estimate']) }}" target="_blank">
                                <i class="fas fa-calculator text-secondary fa-fw"></i> View Estimate
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.dc', $sale->id) }}" target="_blank">
                                <i class="fas fa-shipping-fast text-warning fa-fw"></i> Delivery Challan (DC)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.dc_thermal', $sale->id) }}" target="_blank">
                                <i class="fas fa-truck text-muted fa-fw"></i> DC Thermal
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('sales.receipt', $sale->id) }}" target="_blank">
                                <i class="fas fa-receipt text-success fa-fw"></i> Receipt
                            </a>
                        </li>
                    @endcan

                    @if ($sale->sale_status !== 'returned')
                        @can('sales.create')
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger" href="{{ route('sale.return.show', $sale->id) }}">
                                    <i class="fas fa-undo fa-fw"></i> Return Sale
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>
            </div>
        </td>
    </tr>
@endforeach
