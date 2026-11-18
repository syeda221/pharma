@extends('admin_panel.layout.app')

@section('content')
<style>
    .period-closing-wrap {
        padding: 24px 28px;
        background: #f8fafc;
        min-height: calc(100vh - 120px);
    }
    .period-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .period-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .month-box {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        padding: 16px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.2s ease;
    }
    .month-box.is-closed {
        background: #fef2f2;
        border-color: #fecaca;
    }
    .month-box.is-open {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }
    .badge-closed {
        background-color: #dc2626;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
    }
    .badge-open {
        background-color: #16a34a;
        color: #ffffff;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
    }
</style>

<div class="period-closing-wrap">
    {{-- Breadcrumb & Title --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-lock text-primary me-2"></i>Monthly Sales Closing & Period Lock
            </h4>
            <p class="text-muted small mb-0">Freeze closed months to prevent unauthorized edits/deletions while allowing cross-month returns.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('period-closing.index') }}" class="d-flex align-items-center gap-2">
                <label class="form-label small text-muted mb-0 fw-bold">Select Year:</label>
                <select name="year" class="form-select form-select-sm fw-bold" style="width: 120px;" onchange="this.form.submit()">
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-triangle fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $closedCount = collect($months)->where('is_closed', true)->count();
        $openCount = 12 - $closedCount;
        $latestClosed = collect($months)->where('is_closed', true)->last();
    @endphp

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="period-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #fee2e2; color: #dc2626;">
                    <i class="fas fa-lock fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Closed Months</div>
                    <div class="fs-4 fw-bold text-dark">{{ $closedCount }} / 12</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="period-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <i class="fas fa-lock-open fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Active / Open Months</div>
                    <div class="fs-4 fw-bold text-dark">{{ $openCount }} / 12</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="period-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #e0e7ff; color: #4f46e5;">
                    <i class="fas fa-calendar-check fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase">Latest Locked Month</div>
                    <div class="fs-5 fw-bold text-dark">{{ $latestClosed ? $latestClosed['name'] . ' ' . $latestClosed['year'] : 'None' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Months Grid --}}
    <div class="row g-3">
        @foreach($months as $m)
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <div class="month-box {{ $m['is_closed'] ? 'is-closed' : 'is-open' }}">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $m['name'] }}</h5>
                                <small class="text-muted">{{ $m['start_date'] }} - {{ $m['end_date'] }}</small>
                            </div>
                            @if($m['is_closed'])
                                <span class="badge-closed"><i class="fas fa-lock me-1"></i>Closed</span>
                            @else
                                <span class="badge-open"><i class="fas fa-lock-open me-1"></i>Open</span>
                            @endif
                        </div>

                        <hr class="my-2 opacity-25">

                        <div class="small mb-3">
                            @if($m['is_closed'] && $m['closing'])
                                <div class="text-danger fw-semibold">
                                    <i class="fas fa-user-shield me-1"></i>Closed by: {{ optional($m['closing']->closer)->name ?? 'Admin' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="far fa-clock me-1"></i>{{ $m['closing']->closed_at ? $m['closing']->closed_at->format('d M Y, h:i A') : 'N/A' }}
                                </div>
                                @if($m['closing']->remarks)
                                    <div class="text-muted mt-1 fst-italic" style="font-size: 0.75rem;">
                                        "{{ Str::limit($m['closing']->remarks, 50) }}"
                                    </div>
                                @endif
                            @elseif(!$m['is_closed'] && $m['closing'] && $m['closing']->reopened_at)
                                <div class="text-success fw-semibold">
                                    <i class="fas fa-history me-1"></i>Reopened by: {{ optional($m['closing']->reopener)->name ?? 'Admin' }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="far fa-clock me-1"></i>{{ $m['closing']->reopened_at->format('d M Y, h:i A') }}
                                </div>
                                @if($m['closing']->reopen_reason)
                                    <div class="text-muted mt-1 fst-italic" style="font-size: 0.75rem;">
                                        Reason: "{{ Str::limit($m['closing']->reopen_reason, 50) }}"
                                    </div>
                                @endif
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-check-circle text-success me-1"></i>Active period. Sales can be created, edited, and deleted.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if($m['is_closed'])
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold" onclick="openReopenModal('{{ $m['year'] }}', '{{ $m['number'] }}', '{{ $m['name'] }} {{ $m['year'] }}')">
                                <i class="fas fa-unlock me-1"></i> Re-Open Month
                            </button>
                        @else
                            <button type="button" class="btn btn-dark btn-sm w-100 fw-bold" onclick="openCloseModal('{{ $m['year'] }}', '{{ $m['number'] }}', '{{ $m['name'] }} {{ $m['year'] }}')">
                                <i class="fas fa-lock me-1"></i> Close &amp; Lock Month
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- MODAL 1: Close Month (Password Protected) --}}
<div class="modal fade" id="closeMonthModal" tabindex="-1" aria-labelledby="closeMonthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('period-closing.close') }}" class="modal-content border-0 shadow-lg rounded-3">
            @csrf
            <input type="hidden" name="year" id="close_year">
            <input type="hidden" name="month" id="close_month">
            <input type="hidden" name="module" value="{{ $module }}">

            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="closeMonthModalLabel">
                    <i class="fas fa-lock me-2 text-warning"></i>Close &amp; Lock Month
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-warning d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-shield-alt fs-4 me-2"></i>
                    <div class="small">
                        Target Month: <strong id="close_month_display"></strong>.<br>
                        Is mahine ki tamam sales <strong>freeze</strong> ho jayengi. Koi edit ya delete nahi ho sakega.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Admin Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required placeholder="Apna login password darj karein" autocomplete="current-password">
                    <small class="text-muted">Authorization ke liye aapka password zaroori hai.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Remarks (Optional)</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. November accounts finalized"></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">
                    <i class="fas fa-lock me-1"></i> Confirm &amp; Lock Month
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL 2: Re-Open Month (Password + Reason Protected) --}}
<div class="modal fade" id="reopenMonthModal" tabindex="-1" aria-labelledby="reopenMonthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('period-closing.reopen') }}" class="modal-content border-0 shadow-lg rounded-3">
            @csrf
            <input type="hidden" name="year" id="reopen_year">
            <input type="hidden" name="month" id="reopen_month">
            <input type="hidden" name="module" value="{{ $module }}">

            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="reopenMonthModalLabel">
                    <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Emergency Re-Open Month
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-danger d-flex align-items-center mb-3 py-2">
                    <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                    <div class="small">
                        Target Month: <strong id="reopen_month_display"></strong>.<br>
                        Mahina re-open karne se is period ki invoices dobara editable ho jayengi.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Admin Password <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" required placeholder="Apna login password darj karein" autocomplete="current-password">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Re-Open Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Wajah likhein ke mahina kyun re-open kiya ja raha hai (Audit log mein save hogi)"></textarea>
                    <small class="text-muted">Audit compliance ke liye reason lazmi hai.</small>
                </div>
            </div>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning btn-sm fw-bold px-3 text-dark">
                    <i class="fas fa-unlock me-1"></i> Authorize &amp; Re-Open
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCloseModal(year, month, monthName) {
        document.getElementById('close_year').value = year;
        document.getElementById('close_month').value = month;
        document.getElementById('close_month_display').innerText = monthName;

        let modalEl = document.getElementById('closeMonthModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else if (typeof $ !== 'undefined') {
            $('#closeMonthModal').modal('show');
        }
    }

    function openReopenModal(year, month, monthName) {
        document.getElementById('reopen_year').value = year;
        document.getElementById('reopen_month').value = month;
        document.getElementById('reopen_month_display').innerText = monthName;

        let modalEl = document.getElementById('reopenMonthModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            let modal = new bootstrap.Modal(modalEl);
            modal.show();
        } else if (typeof $ !== 'undefined') {
            $('#reopenMonthModal').modal('show');
        }
    }
</script>
@endsection
