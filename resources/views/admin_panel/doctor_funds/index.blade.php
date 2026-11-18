@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid py-4">

            {{-- Custom Premium Executive Styling --}}
            <style>
                :root {
                    --df-primary: #3b82f6;
                    --df-primary-dark: #1d4ed8;
                    --df-success: #10b981;
                    --df-success-dark: #059669;
                    --df-danger: #ef4444;
                    --df-danger-dark: #dc2626;
                    --df-indigo: #6366f1;
                    --df-slate-900: #0f172a;
                    --df-slate-800: #1e293b;
                    --df-slate-700: #334155;
                    --df-slate-100: #f1f5f9;
                    --df-slate-50: #f8fafc;
                }

                .df-card {
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
                    transition: all 0.25s ease;
                }
                .df-card:hover {
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
                }

                .kpi-stat-card {
                    padding: 1.35rem 1.25rem;
                    border-radius: 14px;
                    position: relative;
                    overflow: hidden;
                    border: 1px solid #e2e8f0;
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                }
                .kpi-stat-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 20px rgba(0,0,0,0.06);
                }
                .kpi-stat-card .kpi-icon {
                    width: 48px;
                    height: 48px;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 20px;
                }
                .kpi-stat-card .kpi-title {
                    font-size: 0.8rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #64748b;
                    margin-bottom: 4px;
                }
                .kpi-stat-card .kpi-val {
                    font-size: 1.45rem;
                    font-weight: 800;
                    color: #0f172a;
                    line-height: 1.2;
                }

                .card-inflow {
                    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
                    border-left: 5px solid #10b981;
                }
                .card-inflow .kpi-icon {
                    background: #d1fae5;
                    color: #059669;
                }

                .card-outflow {
                    background: linear-gradient(135deg, #fff1f2 0%, #fef2f2 100%);
                    border-left: 5px solid #ef4444;
                }
                .card-outflow .kpi-icon {
                    background: #ffe4e6;
                    color: #e11d48;
                }

                .card-balance {
                    background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
                    border-left: 5px solid #3b82f6;
                }
                .card-balance .kpi-icon {
                    background: #dbeafe;
                    color: #2563eb;
                }

                .card-doctors {
                    background: linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%);
                    border-left: 5px solid #8b5cf6;
                }
                .card-doctors .kpi-icon {
                    background: #ede9fe;
                    color: #7c3aed;
                }

                .badge-inflow {
                    background-color: #d1fae5;
                    color: #065f46;
                    border: 1px solid #a7f3d0;
                    font-weight: 600;
                    padding: 5px 10px;
                    border-radius: 20px;
                    font-size: 11px;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }
                .badge-outflow {
                    background-color: #ffe4e6;
                    color: #9f1239;
                    border: 1px solid #fecdd3;
                    font-weight: 600;
                    padding: 5px 10px;
                    border-radius: 20px;
                    font-size: 11px;
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                }

                .table-ledger {
                    border-collapse: separate;
                    border-spacing: 0;
                    width: 100%;
                }
                .table-ledger thead th {
                    background-color: #0f172a;
                    color: #f8fafc;
                    font-weight: 600;
                    font-size: 12px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    padding: 12px 14px;
                    border: none;
                    white-space: nowrap;
                }
                .table-ledger thead th:first-child {
                    border-top-left-radius: 10px;
                }
                .table-ledger thead th:last-child {
                    border-top-right-radius: 10px;
                }
                .table-ledger tbody td {
                    padding: 12px 14px;
                    vertical-align: middle;
                    font-size: 13px;
                    border-top: 1px solid #f1f5f9;
                    color: #1e293b;
                }
                .table-ledger tbody tr:hover {
                    background-color: #f8fafc;
                }

                .btn-gradient-success {
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: #ffffff !important;
                    font-weight: 600;
                    border: none;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                    border-radius: 8px;
                    padding: 9px 18px;
                    transition: all 0.2s ease;
                }
                .btn-gradient-success:hover {
                    background: linear-gradient(135deg, #059669 0%, #047857 100%);
                    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
                    transform: translateY(-1px);
                }

                .btn-gradient-danger {
                    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
                    color: #ffffff !important;
                    font-weight: 600;
                    border: none;
                    box-shadow: 0 4px 12px rgba(244, 63, 94, 0.3);
                    border-radius: 8px;
                    padding: 9px 18px;
                    transition: all 0.2s ease;
                }
                .btn-gradient-danger:hover {
                    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
                    box-shadow: 0 6px 16px rgba(244, 63, 94, 0.4);
                    transform: translateY(-1px);
                }

                .btn-gradient-dark {
                    background: linear-gradient(135deg, #334155 0%, #0f172a 100%);
                    color: #ffffff !important;
                    font-weight: 600;
                    border: none;
                    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
                    border-radius: 8px;
                    padding: 9px 16px;
                }

                .filter-box {
                    background: #ffffff;
                    border: 1px solid #e2e8f0;
                    border-radius: 12px;
                    padding: 18px 20px;
                    margin-bottom: 24px;
                }

                .nav-pills .nav-link.active {
                    background-color: #0f172a !important;
                    color: #ffffff !important;
                    border-radius: 8px;
                    font-weight: 600;
                }
                .nav-pills .nav-link {
                    color: #475569;
                    font-weight: 600;
                    border-radius: 8px;
                    padding: 8px 18px;
                }
            </style>

            {{-- Notifications --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px; background-color: #ecfdf5; color: #065f46; border-left: 5px solid #10b981;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-lg mr-2"></i>
                        <span><strong>Success:</strong> {{ session('success') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 10px; background-color: #fff1f2; color: #9f1239; border-left: 5px solid #ef4444;">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-lg mr-2"></i>
                        <span><strong>Error:</strong> {{ session('error') }}</span>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Top Header & Action Controls --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="font-weight-bold mb-1" style="color: #0f172a; font-size: 1.75rem; letter-spacing: -0.5px;">
                        <i class="fas fa-hand-holding-dollar text-primary mr-2"></i> Doctor Fund & Zone Ledger
                    </h2>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                        Isolated bank fund management: Track distributor recoveries and doctor marketing investments without affecting standard company accounts.
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2 mt-3 mt-md-0" style="gap: 10px;">
                    @can('doctor.funds.create')
                    <a href="{{ route('doctor-funds.receiving.create') }}" class="btn btn-gradient-success shadow-sm">
                        <i class="fas fa-arrow-down mr-1"></i> Receive Fund (Distributor)
                    </a>
                    <a href="{{ route('doctor-funds.investment.create') }}" class="btn btn-gradient-danger shadow-sm">
                        <i class="fas fa-arrow-up mr-1"></i> Add Doctor Investment
                    </a>
                    @endcan
                    <button type="button" class="btn btn-gradient-dark shadow-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print Statement
                    </button>
                </div>
            </div>

            {{-- KPI Summary Cards --}}
            <div class="row mb-4">
                {{-- Total Inflow --}}
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-stat-card card-inflow shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title">Total Inflow (Received)</div>
                                <div class="kpi-val text-success">PKR {{ number_format($totalInflow, 2) }}</div>
                                <small class="text-muted"><i class="fas fa-building mr-1"></i> From Distributors / Zones</small>
                            </div>
                            <div class="kpi-icon">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total Outflow / Investments --}}
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-stat-card card-outflow shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title">Total Doctor Investment</div>
                                <div class="kpi-val text-danger">PKR {{ number_format($totalOutflow, 2) }}</div>
                                <small class="text-muted"><i class="fas fa-user-md mr-1"></i> Spent on Doctors</small>
                            </div>
                            <div class="kpi-icon">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Available Balance --}}
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-stat-card card-balance shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title">Net Available Balance</div>
                                <div class="kpi-val {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }}">
                                    PKR {{ number_format($netBalance, 2) }}
                                </div>
                                <small class="{{ $netBalance >= 0 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                    <i class="fas {{ $netBalance >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' }} mr-1"></i>
                                    {{ $netBalance >= 0 ? 'In Surplus' : 'Fund Deficit' }}
                                </small>
                            </div>
                            <div class="kpi-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Doctor & Zone Coverage --}}
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="kpi-stat-card card-doctors shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="kpi-title">Doctors & Zones</div>
                                <div class="kpi-val" style="color: #7c3aed;">
                                    {{ $totalDoctorsCount }} <span style="font-size: 0.95rem; font-weight: 500; color: #64748b;">Doctors</span>
                                </div>
                                <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> Across {{ $totalZonesCount }} Active Zones</small>
                            </div>
                            <div class="kpi-icon">
                                <i class="fas fa-user-doctor"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advanced Filter Bar --}}
            <div class="filter-box shadow-sm mb-4">
                <form method="GET" action="{{ route('doctor-funds.index') }}" id="filterForm">
                    <div class="row align-items-end">
                        {{-- Zone / Distributor Filter --}}
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-map-marked-alt text-primary mr-1"></i> Zone / Distributor
                            </label>
                            <select name="zone_id" id="filter_zone_id" class="form-control select2" style="border-radius: 8px;">
                                <option value="">-- All Zones (Distributors) --</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}" {{ request('zone_id') == $z->id ? 'selected' : '' }}>
                                        {{ $z->zone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Doctor Filter --}}
                        <div class="col-lg-3 col-md-6 mb-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-user-md text-info mr-1"></i> Doctor (Customer)
                            </label>
                            <select name="customer_id" id="filter_customer_id" class="form-control select2" style="border-radius: 8px;">
                                <option value="">-- All Doctors --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}" {{ request('customer_id') == $doc->id ? 'selected' : '' }}>
                                        {{ $doc->customer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Type Filter --}}
                        <div class="col-lg-2 col-md-4 mb-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-filter text-secondary mr-1"></i> Type
                            </label>
                            <select name="type" class="form-control" style="border-radius: 8px;">
                                <option value="">All Transactions</option>
                                <option value="receiving" {{ request('type') == 'receiving' ? 'selected' : '' }}>Inflow (Received)</option>
                                <option value="investment" {{ request('type') == 'investment' ? 'selected' : '' }}>Outflow (Invested)</option>
                            </select>
                        </div>

                        {{-- Date Range From --}}
                        <div class="col-lg-2 col-md-4 mb-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-calendar-alt text-secondary mr-1"></i> From Date
                            </label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" style="border-radius: 8px;">
                        </div>

                        {{-- Date Range To --}}
                        <div class="col-lg-2 col-md-4 mb-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-calendar-alt text-secondary mr-1"></i> To Date
                            </label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" style="border-radius: 8px;">
                        </div>

                        {{-- Search & Action Buttons --}}
                        <div class="col-lg-4 col-md-6 mb-2 mt-2">
                            <label class="font-weight-bold text-dark mb-1" style="font-size: 12px; text-transform: uppercase;">
                                <i class="fas fa-search text-secondary mr-1"></i> Search Keyword
                            </label>
                            <input type="text" name="search" class="form-control" placeholder="Search Voucher, Ref, Bank, Remarks..." value="{{ request('search') }}" style="border-radius: 8px;">
                        </div>

                        <div class="col-lg-8 col-md-6 mb-2 mt-2 d-flex justify-content-end align-items-center" style="gap: 8px;">
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-filter mr-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('doctor-funds.index') }}" class="btn btn-outline-secondary px-3" style="border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Main Content View (Tabs) --}}
            <div class="df-card p-4">
                <ul class="nav nav-pills mb-4" id="ledgerTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-ledger-link" data-toggle="pill" href="#tab-ledger" role="tab">
                            <i class="fas fa-list-ol mr-2"></i> Running Ledger Statement
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-zones-link" data-toggle="pill" href="#tab-zones" role="tab">
                            <i class="fas fa-chart-pie mr-2"></i> Zone (Distributor) Summary
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="ledgerTabsContent">
                    {{-- Tab 1: Detailed Running Ledger Statement --}}
                    <div class="tab-pane fade show active" id="tab-ledger" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-ledger" id="fundTable">
                                <thead>
                                    <tr>
                                        <th>Voucher No</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Distributor (Zone)</th>
                                        <th>Doctor (Customer)</th>
                                        <th>Bank / Mode</th>
                                        <th>Purpose / Remarks</th>
                                        <th class="text-right">Inflow (+)</th>
                                        <th class="text-right">Outflow (-)</th>
                                        <th class="text-right">Balance</th>
                                        <th class="text-center">Attachment</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ledgerEntries as $entry)
                                        <tr>
                                            {{-- Voucher No --}}
                                            <td>
                                                <span class="font-weight-bold text-dark">{{ $entry->voucher_no }}</span>
                                                @if($entry->reference_no)
                                                    <br><small class="text-muted">Ref: {{ $entry->reference_no }}</small>
                                                @endif
                                            </td>

                                            {{-- Date --}}
                                            <td class="text-nowrap">
                                                <i class="far fa-calendar-alt text-muted mr-1"></i>
                                                {{ $entry->transaction_date->format('d M, Y') }}
                                            </td>

                                            {{-- Type --}}
                                            <td>
                                                @if($entry->type === 'receiving')
                                                    <span class="badge-inflow">
                                                        <i class="fas fa-arrow-down"></i> Received
                                                    </span>
                                                @else
                                                    <span class="badge-outflow">
                                                        <i class="fas fa-arrow-up"></i> Invested
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Zone / Distributor --}}
                                            <td>
                                                <span class="font-weight-bold" style="color: #0f172a;">
                                                    <i class="fas fa-building text-secondary mr-1"></i>
                                                    {{ $entry->zone ? $entry->zone->zone : 'N/A' }}
                                                </span>
                                            </td>

                                            {{-- Doctor (Customer) --}}
                                            <td>
                                                @if($entry->customer)
                                                    <span class="font-weight-bold text-primary">
                                                        <i class="fas fa-user-md mr-1"></i> {{ $entry->customer->customer_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted font-italic">General Zone Pool</span>
                                                @endif
                                            </td>

                                            {{-- Bank & Payment Mode --}}
                                            <td>
                                                <div style="font-size: 12px; font-weight: 600; color: #334155;">
                                                    {{ $entry->bank_account_name }}
                                                </div>
                                                <small class="badge badge-light border">
                                                    {{ $entry->payment_method }}
                                                </small>
                                            </td>

                                            {{-- Purpose & Remarks --}}
                                            <td style="max-width: 200px;">
                                                <span class="badge badge-info" style="font-size: 10.5px;">{{ $entry->purpose_tag }}</span>
                                                @if($entry->narration)
                                                    <div class="text-muted small mt-1 text-truncate" title="{{ $entry->narration }}">
                                                        {{ $entry->narration }}
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- Inflow --}}
                                            <td class="text-right text-nowrap font-weight-bold text-success">
                                                @if($entry->type === 'receiving')
                                                    +{{ number_format($entry->amount, 2) }}
                                                @else
                                                    <span class="text-muted font-weight-normal">-</span>
                                                @endif
                                            </td>

                                            {{-- Outflow --}}
                                            <td class="text-right text-nowrap font-weight-bold text-danger">
                                                @if($entry->type === 'investment')
                                                    -{{ number_format($entry->amount, 2) }}
                                                @else
                                                    <span class="text-muted font-weight-normal">-</span>
                                                @endif
                                            </td>

                                            {{-- Running Balance --}}
                                            <td class="text-right text-nowrap font-weight-bold" style="color: #0f172a; font-size: 13.5px;">
                                                PKR {{ number_format($entry->running_balance, 2) }}
                                            </td>

                                            {{-- Attachment --}}
                                            <td class="text-center">
                                                @if($entry->attachment)
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-view-attachment p-1 px-2"
                                                        data-id="{{ $entry->id }}"
                                                        data-url="{{ $entry->attachment_url }}"
                                                        data-title="{{ $entry->voucher_no }}"
                                                        data-is-pdf="{{ $entry->is_pdf ? '1' : '0' }}"
                                                        style="border-radius: 6px; font-size: 11px;">
                                                        <i class="fas fa-paperclip mr-1"></i> View Slip
                                                    </button>
                                                @else
                                                    <span class="text-muted small">None</span>
                                                @endif
                                            </td>

                                            {{-- Actions --}}
                                            <td class="text-center text-nowrap">
                                                <button type="button" class="btn btn-sm btn-outline-info btn-view-details p-1 px-2 mr-1"
                                                    data-id="{{ $entry->id }}"
                                                    title="View Full Details"
                                                    style="border-radius: 6px;">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                @can('doctor.funds.delete')
                                                <form action="{{ route('doctor-funds.destroy', $entry->id) }}" method="POST" class="d-inline form-delete-tx">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger p-1 px-2 btn-delete-confirm"
                                                        data-voucher="{{ $entry->voucher_no }}"
                                                        title="Delete Entry"
                                                        style="border-radius: 6px;">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-5 text-muted">
                                                <div class="my-4">
                                                    <i class="fas fa-folder-open fa-3x text-secondary mb-3"></i>
                                                    <h5 class="text-dark font-weight-bold">No Transactions Found</h5>
                                                    <p class="text-muted small">No fund receiving or doctor investments recorded yet for the selected filters.</p>
                                                    @can('doctor.funds.create')
                                                    <a href="{{ route('doctor-funds.receiving.create') }}" class="btn btn-sm btn-gradient-success mr-2">
                                                        <i class="fas fa-plus mr-1"></i> Receive Fund
                                                    </a>
                                                    <a href="{{ route('doctor-funds.investment.create') }}" class="btn btn-sm btn-gradient-danger">
                                                        <i class="fas fa-plus mr-1"></i> Add Doctor Investment
                                                    </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Tab 2: Zone (Distributor) Summary Breakdown --}}
                    <div class="tab-pane fade" id="tab-zones" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-ledger">
                                <thead>
                                    <tr>
                                        <th>Zone / Distributor</th>
                                        <th class="text-center">Total Entries</th>
                                        <th class="text-center">Doctors Covered</th>
                                        <th class="text-right">Total Inflow Received</th>
                                        <th class="text-right">Total Invested on Doctors</th>
                                        <th class="text-right">Net Margin / Balance</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($zoneSummaries as $zs)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold" style="color: #0f172a; font-size: 14px;">
                                                    <i class="fas fa-building text-primary mr-2"></i>
                                                    {{ $zs->zone ? $zs->zone->zone : 'Unknown Zone' }}
                                                </span>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $zs->total_entries }}</td>
                                            <td class="text-center font-weight-bold text-info">{{ $zs->doctors_count }} Doctors</td>
                                            <td class="text-right text-success font-weight-bold">+PKR {{ number_format($zs->total_received, 2) }}</td>
                                            <td class="text-right text-danger font-weight-bold">-PKR {{ number_format($zs->total_invested, 2) }}</td>
                                            <td class="text-right font-weight-bold {{ $zs->net_balance >= 0 ? 'text-primary' : 'text-danger' }}" style="font-size: 14px;">
                                                PKR {{ number_format($zs->net_balance, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('doctor-funds.index', ['zone_id' => $zs->zone_id]) }}" class="btn btn-sm btn-outline-dark p-1 px-3" style="border-radius: 6px;">
                                                    <i class="fas fa-search mr-1"></i> View Statement
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No zone summaries available yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ========================================================
     ATTACHMENT VIEWER MODAL
   ======================================================== --}}
<div class="modal fade" id="modalViewAttachment" tabindex="-1" role="dialog" aria-labelledby="modalViewAttachmentTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white p-3 px-4">
                <h5 class="modal-title font-weight-bold" id="modalViewAttachmentTitle">
                    <i class="fas fa-paperclip mr-2 text-primary"></i> Attachment Document Preview
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="attachmentModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading document...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <a href="#" id="btnDownloadAttachment" target="_blank" download class="btn btn-primary px-3" style="border-radius: 8px;">
                    <i class="fas fa-download mr-1"></i> Download File
                </a>
                <a href="#" id="btnOpenNewTabAttachment" target="_blank" class="btn btn-outline-dark px-3" style="border-radius: 8px;">
                    <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                </a>
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 8px;">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================
     TRANSACTION FULL DETAILS MODAL
   ======================================================== --}}
<div class="modal fade" id="modalViewDetails" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-primary text-white p-3 px-4">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-info-circle mr-2"></i> Transaction Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="detailsModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light p-3">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 8px;">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true,
                width: '100%'
            });
        }

        // View Attachment Slip Preview Modal
        $(document).on('click', '.btn-view-attachment', function(e) {
            e.preventDefault();
            var url = $(this).data('url');
            var title = $(this).data('title') || 'Attachment Preview';
            var isPdf = $(this).data('is-pdf') == '1';

            $('#modalViewAttachmentTitle').html('<i class="fas fa-paperclip mr-2 text-primary"></i> Attachment: ' + title);
            $('#btnDownloadAttachment').attr('href', url);
            $('#btnOpenNewTabAttachment').attr('href', url);

            if (isPdf) {
                $('#attachmentModalBody').html(`
                    <div class="w-100 text-center">
                        <iframe src="${url}" style="width:100%; height:600px; border:1px solid #cbd5e1; border-radius:10px;"></iframe>
                    </div>
                `);
            } else {
                $('#attachmentModalBody').html(`
                    <div class="w-100 text-center">
                        <img src="${url}" class="img-fluid rounded border shadow-sm" style="max-height: 600px; max-width: 100%; object-fit: contain;" alt="Attachment Document">
                    </div>
                `);
            }

            $('#modalViewAttachment').modal('show');
        });

        // View Full Details Modal
        $(document).on('click', '.btn-view-details', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#detailsModalBody').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
            $('#modalViewDetails').modal('show');

            $.ajax({
                url: "{{ url('/doctor-funds/details') }}/" + id,
                type: "GET",
                dataType: "json",
                success: function(res) {
                    var typeBadge = res.type === 'receiving'
                        ? '<span class="badge-inflow"><i class="fas fa-arrow-down"></i> Received (Inflow)</span>'
                        : '<span class="badge-outflow"><i class="fas fa-arrow-up"></i> Invested (Outflow)</span>';

                    var html = `
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tr><th style="width: 40%; background: #f8fafc;">Voucher No</th><td class="font-weight-bold text-dark">${res.voucher_no}</td></tr>
                                <tr><th style="background: #f8fafc;">Transaction Type</th><td>${typeBadge}</td></tr>
                                <tr><th style="background: #f8fafc;">Date</th><td>${res.transaction_date}</td></tr>
                                <tr><th style="background: #f8fafc;">Amount</th><td class="font-weight-bold ${res.type === 'receiving' ? 'text-success' : 'text-danger'}" style="font-size: 16px;">PKR ${res.amount}</td></tr>
                                <tr><th style="background: #f8fafc;">Distributor (Zone)</th><td class="font-weight-bold">${res.zone}</td></tr>
                                <tr><th style="background: #f8fafc;">Doctor</th><td>${res.customer}</td></tr>
                                <tr><th style="background: #f8fafc;">Dedicated Account</th><td>${res.bank_account_name}</td></tr>
                                <tr><th style="background: #f8fafc;">Payment Method</th><td>${res.payment_method}</td></tr>
                                <tr><th style="background: #f8fafc;">Reference No</th><td>${res.reference_no}</td></tr>
                                <tr><th style="background: #f8fafc;">Purpose Tag</th><td><span class="badge badge-info">${res.purpose_tag}</span></td></tr>
                                <tr><th style="background: #f8fafc;">Remarks / Notes</th><td>${res.narration}</td></tr>
                                <tr><th style="background: #f8fafc;">Recorded By</th><td>${res.created_by}</td></tr>
                            </table>
                        </div>
                    `;

                    if (res.attachment_url) {
                        html += `
                            <div class="mt-3 text-center">
                                <a href="${res.attachment_url}" target="_blank" class="btn btn-sm btn-outline-primary px-3" style="border-radius: 6px;">
                                    <i class="fas fa-paperclip mr-1"></i> View Attachment Document
                                </a>
                            </div>
                        `;
                    }

                    $('#detailsModalBody').html(html);
                },
                error: function() {
                    $('#detailsModalBody').html('<div class="alert alert-danger">Error loading transaction details.</div>');
                }
            });
        });

        // Delete confirmation with SweetAlert2
        $(document).on('click', '.btn-delete-confirm', function(e) {
            e.preventDefault();
            var voucher = $(this).data('voucher');
            var form = $(this).closest('form');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Entry?',
                    text: 'Are you sure you want to permanently delete transaction [' + voucher + ']?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel',
                    borderRadius: '12px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete transaction [' + voucher + ']?')) {
                    form.submit();
                }
            }
        });
    });
</script>
@endpush
@endsection
