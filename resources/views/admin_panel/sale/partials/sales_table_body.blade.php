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
        <td class="ps-2 fw-bold font-monospace text-nowrap">
            @if ($sale->invoice_no)
                <span class="text-primary fw-bold">{{ $sale->invoice_no }}</span>
                <small class="text-muted d-block" style="font-size: 10px;">#{{ $sale->id }}</small>
            @else
                <span class="text-muted">#{{ $sale->id }}</span>
            @endif
        </td>
        <td class="text-nowrap">
            <div class="d-flex align-items-center">
                <div class="avatar-circle bg-info-subtle text-info me-1.5 fw-bold d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                    style="width: 24px; height: 24px; font-size: 11px; background-color: #e0f2fe; color: #0369a1;">
                    {{ strtoupper(substr(optional($sale->customer_relation)->customer_name ?? 'C', 0, 1)) }}
                </div>
                <span class="fw-medium text-dark text-truncate d-inline-block" style="max-width: 105px; vertical-align: middle;" title="{{ optional($sale->customer_relation)->customer_name }}">{{ optional($sale->customer_relation)->customer_name ?? 'N/A' }}</span>
            </div>
        </td>
        <td class="font-monospace text-muted text-center small text-nowrap">{{ $sale->reference ?? '-' }}</td>
        <td title="{{ $pNames }}" class="text-muted small text-truncate" style="max-width: 95px;">
            {{ \Illuminate\Support\Str::limit($pNames, 18) }}
        </td>
        <td class="text-center font-monospace text-nowrap">
            @php
                $rowBonus = isset($sale->bonus_qty) ? (float)$sale->bonus_qty : 0;
                $rowSold = isset($sale->effective_sold_qty) ? (float)$sale->effective_sold_qty : max(0, $effQty - $rowBonus);
            @endphp
            @if ($sale->sale_status === 'returned' || $sale->sale_status == 1)
                @if ($effQty > 0)
                    <span class="fw-bold">{{ number_format($effQty, 0) }}</span>
                    <small class="text-danger d-block" style="font-size: 9.5px;">(-{{ number_format($retQty > 0 ? $retQty : ($origQty - $effQty), 0) }} ret)</small>
                @else
                    <span class="text-danger fw-bold">0</span>
                    <small class="text-muted d-block text-decoration-line-through" style="font-size: 9.5px;">{{ number_format($origQty, 0) }}</small>
                @endif
            @elseif ($retQty > 0)
                <span class="fw-bold">{{ number_format($effQty, 0) }}</span>
                <small class="text-danger d-block" style="font-size: 9.5px;">(-{{ number_format($retQty, 0) }} ret)</small>
            @elseif ($rowBonus > 0)
                <span class="fw-bold text-dark">{{ number_format($rowSold, 0) }} <span class="text-primary fw-bold" title="Bonus">/ {{ number_format($rowBonus, 0) }}</span></span>
                <small class="d-block text-muted" style="font-size: 9px;">(Sold / Bns)</small>
            @else
                {{ number_format($origQty, 0) }}
            @endif
        </td>
        <td class="text-right fw-bold text-dark font-monospace text-nowrap" style="text-align: right !important; white-space: nowrap;">
            @php
                $effGross = isset($sale->effective_gross) ? $sale->effective_gross : $gross_subtotal;
            @endphp
            @if ($sale->sale_status === 'returned' || $sale->sale_status == 1)
                @if ($effGross > 0)
                    <span class="d-block">Rs. {{ number_format($effGross, 2) }}</span>
                    <small class="text-danger d-block" style="font-size: 9.5px;">(-Rs. {{ number_format($gross_subtotal - $effGross, 2) }} ret)</small>
                @else
                    <span class="text-danger d-block">Rs. 0.00</span>
                    <small class="text-muted d-block text-decoration-line-through" style="font-size: 9.5px;">(Rs. {{ number_format($gross_subtotal, 2) }})</small>
                @endif
            @elseif ($retNet > 0 && $effGross < $gross_subtotal)
                <span class="d-block">Rs. {{ number_format($effGross, 2) }}</span>
                <small class="text-danger d-block" style="font-size: 9.5px;">(-Rs. {{ number_format($gross_subtotal - $effGross, 2) }} ret)</small>
            @else
                <span>Rs. {{ number_format($gross_subtotal, 2) }}</span>
            @endif
        </td>
        @php
            // Pharmacy, Doctor, and Distribution tier discounts
            if (isset($sale->tier_medical_discount) && isset($sale->tier_doctor_discount) && isset($sale->tier_distribution_discount)) {
                $tMedAmt = (float)$sale->tier_medical_discount;
                $tDocAmt = (float)$sale->tier_doctor_discount;
                $tDistAmt = (float)$sale->tier_distribution_discount;
            } else {
                $tMedAmt = 0;
                $tDocAmt = 0;
                $tDistAmt = 0;
                if ($gross_subtotal > 0 && $inline_val > 0) {
                    $tiers = optional($sale->customer_relation)->discount_tiers ?? [];
                    $savedTierStr = $sale->discount_tier ?? '';
                    $activeTiersArray = !empty($savedTierStr) ? array_map('trim', explode(',', $savedTierStr)) : [];
                    if (empty($activeTiersArray) && !empty($tiers)) {
                        $mVal = (float)($tiers['medical'] ?? 0);
                        $dVal = (float)($tiers['doctor'] ?? 0);
                        $distVal = (float)($tiers['distribution'] ?? 0);
                        $targetDisc = round($inline_pct, 2);
                        if (round($mVal + $dVal + $distVal, 2) === $targetDisc && $mVal > 0 && $dVal > 0 && $distVal > 0) {
                            $activeTiersArray = ['medical', 'doctor', 'distribution'];
                        } elseif (round($mVal + $distVal, 2) === $targetDisc && $mVal > 0 && $distVal > 0) {
                            $activeTiersArray = ['medical', 'distribution'];
                        } elseif (round($mVal + $dVal, 2) === $targetDisc && $mVal > 0 && $dVal > 0) {
                            $activeTiersArray = ['medical', 'doctor'];
                        } elseif (round($dVal + $distVal, 2) === $targetDisc && $dVal > 0 && $distVal > 0) {
                            $activeTiersArray = ['doctor', 'distribution'];
                        } elseif (round($dVal, 2) === $targetDisc && $dVal > 0) {
                            $activeTiersArray = ['doctor'];
                        } elseif (round($mVal, 2) === $targetDisc && $mVal > 0) {
                            $activeTiersArray = ['medical'];
                        } elseif (round($distVal, 2) === $targetDisc && $distVal > 0) {
                            $activeTiersArray = ['distribution'];
                        }
                    }
                    if (!empty($activeTiersArray)) {
                        foreach ($activeTiersArray as $t) {
                            $pct = (float)($tiers[$t] ?? 0);
                            if ($pct > 0) {
                                $amt = round(($gross_subtotal * $pct) / 100, 2);
                                if ($t === 'medical') $tMedAmt += $amt;
                                elseif ($t === 'doctor') $tDocAmt += $amt;
                                elseif ($t === 'distribution') $tDistAmt += $amt;
                            }
                        }
                    } else {
                        $tDocAmt = $inline_val;
                    }
                }
            }
            $tMedPct = $gross_subtotal > 0 ? ($tMedAmt / $gross_subtotal) * 100 : 0;
            $tDocPct = $gross_subtotal > 0 ? ($tDocAmt / $gross_subtotal) * 100 : 0;
            $tDistPct = $gross_subtotal > 0 ? ($tDistAmt / $gross_subtotal) * 100 : 0;
        @endphp

        {{-- Pharmacy Disc (Medical) --}}
        <td class="text-right font-monospace align-middle" style="text-align: right !important; white-space: nowrap;">
            @if ($tMedAmt > 0)
                <div class="d-inline-flex flex-column align-items-end justify-content-center">
                    <span class="fw-bold text-dark font-monospace" style="font-size: 12px; line-height: 1.2;">Rs. {{ number_format($tMedAmt, 2) }}</span>
                    @if ($tMedPct > 0)
                        <span class="badge rounded-pill fw-semibold mt-1" style="font-size: 9.5px; padding: 1.5px 6px; background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">-{{ number_format($tMedPct, 1) }}%</span>
                    @endif
                </div>
            @else
                <span class="text-muted fw-bold d-block pe-1" style="font-size: 13px; opacity: 0.35;">—</span>
            @endif
        </td>

        {{-- Doctor Disc --}}
        <td class="text-right font-monospace align-middle" style="text-align: right !important; white-space: nowrap;">
            @if ($tDocAmt > 0)
                <div class="d-inline-flex flex-column align-items-end justify-content-center">
                    <span class="fw-bold text-dark font-monospace" style="font-size: 12px; line-height: 1.2;">Rs. {{ number_format($tDocAmt, 2) }}</span>
                    @if ($tDocPct > 0)
                        <span class="badge rounded-pill fw-semibold mt-1" style="font-size: 9.5px; padding: 1.5px 6px; background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">-{{ number_format($tDocPct, 1) }}%</span>
                    @endif
                </div>
            @else
                <span class="text-muted fw-bold d-block pe-1" style="font-size: 13px; opacity: 0.35;">—</span>
            @endif
        </td>

        {{-- Dist Disc (Distribution) --}}
        <td class="text-right font-monospace align-middle" style="text-align: right !important; white-space: nowrap;">
            @if ($tDistAmt > 0)
                <div class="d-inline-flex flex-column align-items-end justify-content-center">
                    <span class="fw-bold text-dark font-monospace" style="font-size: 12px; line-height: 1.2;">Rs. {{ number_format($tDistAmt, 2) }}</span>
                    @if ($tDistPct > 0)
                        <span class="badge rounded-pill fw-semibold mt-1" style="font-size: 9.5px; padding: 1.5px 6px; background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe;">-{{ number_format($tDistPct, 1) }}%</span>
                    @endif
                </div>
            @else
                <span class="text-muted fw-bold d-block pe-1" style="font-size: 13px; opacity: 0.35;">—</span>
            @endif
        </td>

        {{-- Extra Disc --}}
        <td class="text-right font-monospace align-middle" style="text-align: right !important; white-space: nowrap;">
            @if ($sale->total_extradiscount > 0)
                @php
                    $add_val = $sale->total_extradiscount;
                    $add_pct = $bill_amount > 0 ? ($add_val / $bill_amount) * 100 : 0;
                @endphp
                <div class="d-inline-flex flex-column align-items-end justify-content-center">
                    <span class="fw-bold text-dark font-monospace" style="font-size: 12px; line-height: 1.2;">Rs. {{ number_format($add_val, 2) }}</span>
                    @if ($add_pct > 0)
                        <span class="badge rounded-pill fw-semibold mt-1" style="font-size: 9.5px; padding: 1.5px 6px; background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">-{{ number_format($add_pct, 1) }}%</span>
                    @endif
                </div>
            @else
                <span class="text-muted fw-bold d-block pe-1" style="font-size: 13px; opacity: 0.35;">—</span>
            @endif
        </td>
        <td class="text-right fw-bold font-monospace align-middle text-nowrap" style="text-align: right !important; white-space: nowrap;">
            @if (isset($isExchange) && $isExchange)
                @if ($collected > 0)
                    <span class="text-success d-block">Rs. {{ number_format($collected, 2) }}</span>
                @elseif ($refunded > 0)
                    <span class="text-danger d-block">-Rs. {{ number_format($refunded, 2) }}</span>
                @else
                    <span class="text-muted d-block">Rs. 0.00</span>
                @endif
                <span class="badge badge-info text-white border border-info px-1 py-0 mt-0.5" style="font-size: 9.5px;"><i class="fas fa-exchange-alt me-1"></i>Exchange</span>
            @elseif ($sale->sale_status === 'returned' || $sale->sale_status == 1)
                @if ($effNet > 0)
                    <span class="text-success d-block">Rs. {{ number_format($effNet, 2) }}</span>
                    <small class="text-danger d-block" style="font-size: 9.5px;">(-Rs. {{ number_format($retNet > 0 ? $retNet : ((float)$sale->total_net - $effNet), 2) }} ret)</small>
                @else
                    <span class="text-danger d-block">Rs. 0.00</span>
                    <small class="text-muted d-block text-decoration-line-through" style="font-size: 9.5px;">(Rs. {{ number_format($sale->total_net, 2) }})</small>
                @endif
            @elseif ($retNet > 0)
                <span class="text-success d-block">Rs. {{ number_format($effNet, 2) }}</span>
                <small class="text-danger d-block" style="font-size: 9.5px;">(-Rs. {{ number_format($retNet, 2) }} ret)</small>
            @else
                <span class="text-success d-block">Rs. {{ number_format($sale->total_net, 2) }}</span>
            @endif
        </td>
        <td class="text-nowrap small text-muted text-center" style="font-size: 11px;">
            {{ $sale->created_at->format('d/m/Y') }}
        </td>
        <td class="text-center text-nowrap">{!! $statusBadge !!}</td>
        <td class="text-center text-nowrap px-1">
            <div class="dropdown">
                <button class="btn btn-premium-action dropdown-toggle" type="button" data-toggle="dropdown" data-display="static" aria-expanded="false">
                    <i class="fas fa-ellipsis-v me-1"></i> Action
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
