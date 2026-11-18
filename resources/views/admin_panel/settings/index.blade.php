@extends('admin_panel.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ERP Settings</h3>
                    </div>
                    <div class="card-body">
                        <!-- Advanced Settings & Navigation -->
                        <div class="mb-4 pb-3 border-bottom">
                            <h6 class="text-muted mb-3 font-weight-bold text-uppercase"
                                style="font-size: 0.8rem; letter-spacing: 1px;">Advanced Actions</h6>
                            <div class="d-flex flex-wrap">
                                <a href="{{ route('settings.return-policy') }}"
                                    class="btn btn-outline-primary mr-2 mb-2 shadow-sm">
                                    <i class="fas fa-undo-alt mr-2"></i> Return Policy
                                </a>
                                <a href="{{ route('settings.return-approvers') }}"
                                    class="btn btn-outline-info mr-2 mb-2 shadow-sm">
                                    <i class="fas fa-user-shield mr-2"></i> Return Approvers
                                </a>
                                <a href="#" class="btn btn-outline-dark mr-2 mb-2 shadow-sm">
                                    <i class="fas fa-exchange-alt mr-2"></i> Switch Account
                                </a>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="company-tab" data-toggle="tab" href="#company"
                                    role="tab">
                                    <i class="fas fa-building"></i> Company
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="sales-tab" data-toggle="tab" href="#sales" role="tab">
                                    <i class="fas fa-shopping-cart"></i> Sales
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="inventory-tab" data-toggle="tab" href="#inventory" role="tab">
                                    <i class="fas fa-boxes"></i> Inventory
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="accounting-tab" data-toggle="tab" href="#accounting" role="tab">
                                    <i class="fas fa-calculator"></i> Accounting
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="doctor-funds-tab" data-toggle="tab" href="#doctor_funds" role="tab">
                                    <i class="fas fa-user-md text-success"></i> Doctor Fund
                                </a>
                            </li>
                        </ul>

                        @php
                            $canEditSettings = auth()->user()->hasAnyPermission(['settings.edit', 'settings.update']);
                        @endphp
                        <form id="settingsForm" class="mt-4">
                            @csrf
                            <div class="tab-content" id="settingsTabContent">
                                <!-- Company Tab -->
                                <div class="tab-pane fade show active" id="company" role="tabpanel">
                                    @if (isset($settings['company']))
                                        @foreach ($settings['company'] as $setting)
                                            <div class="form-group">
                                                <label>{{ $setting['label'] }}</label>
                                                @if ($setting['type'] === 'text')
                                                    <textarea name="settings[{{ $setting['key'] }}]" class="form-control" rows="3" {{ !$canEditSettings ? 'disabled' : '' }}>{{ $setting['value'] }}</textarea>
                                                @else
                                                    <input type="text" name="settings[{{ $setting['key'] }}]"
                                                        class="form-control" value="{{ $setting['value'] }}" {{ !$canEditSettings ? 'disabled' : '' }}>
                                                @endif
                                                @if ($setting['description'])
                                                    <small
                                                        class="form-text text-muted">{{ $setting['description'] }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Sales Tab -->
                                <div class="tab-pane fade" id="sales" role="tabpanel">
                                    @if (isset($settings['sales']))
                                        @foreach ($settings['sales'] as $setting)
                                            <div class="form-group">
                                                <label>{{ $setting['label'] }}</label>
                                                @if ($setting['type'] === 'text')
                                                    <textarea name="settings[{{ $setting['key'] }}]" class="form-control" rows="3" {{ !$canEditSettings ? 'disabled' : '' }}>{{ $setting['value'] }}</textarea>
                                                @elseif($setting['type'] === 'integer')
                                                    <input type="number" name="settings[{{ $setting['key'] }}]"
                                                        class="form-control" value="{{ $setting['value'] }}" {{ !$canEditSettings ? 'disabled' : '' }}>
                                                @else
                                                    <input type="text" name="settings[{{ $setting['key'] }}]"
                                                        class="form-control" value="{{ $setting['value'] }}" {{ !$canEditSettings ? 'disabled' : '' }}>
                                                @endif
                                                @if ($setting['description'])
                                                    <small
                                                        class="form-text text-muted">{{ $setting['description'] }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Inventory Tab -->
                                <div class="tab-pane fade" id="inventory" role="tabpanel">
                                    @if (isset($settings['inventory']))
                                        @foreach ($settings['inventory'] as $setting)
                                            <div class="form-group">
                                                <label>{{ $setting['label'] }}</label>
                                                <input type="number" name="settings[{{ $setting['key'] }}]"
                                                    class="form-control" value="{{ $setting['value'] }}" {{ !$canEditSettings ? 'disabled' : '' }}>
                                                @if ($setting['description'])
                                                    <small
                                                        class="form-text text-muted">{{ $setting['description'] }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                <!-- Accounting Tab -->
                                <div class="tab-pane fade" id="accounting" role="tabpanel">
                                    @if (isset($settings['accounting']) && count($settings['accounting']) > 0)
                                        @foreach ($settings['accounting'] as $setting)
                                            <div class="form-group">
                                                <label>{{ $setting['label'] }}</label>
                                                <input type="text" name="settings[{{ $setting['key'] }}]"
                                                    class="form-control" value="{{ $setting['value'] }}" {{ !$canEditSettings ? 'disabled' : '' }}>
                                                @if ($setting['description'])
                                                    <small
                                                        class="form-text text-muted">{{ $setting['description'] }}</small>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-4 text-center border rounded bg-light my-3">
                                            <i class="fas fa-calculator text-muted fa-2x mb-2"></i>
                                            <p class="text-muted mb-2">No general accounting parameters are currently configured.</p>
                                            <p class="mb-0">
                                                To configure the <strong>Doctor Fund Dedicated Bank Account</strong>, please click here:
                                                <a href="javascript:void(0)" onclick="$('#doctor-funds-tab').tab('show');" class="btn btn-sm btn-outline-success ml-2">
                                                    <i class="fas fa-user-md mr-1"></i> Open Doctor Fund Settings
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Doctor Fund Tab -->
                                <div class="tab-pane fade" id="doctor_funds" role="tabpanel">
                                    <div class="alert alert-light border mb-4" style="border-left: 4px solid #059669 !important;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hand-holding-medical text-success fa-2x mr-3"></i>
                                            <div>
                                                <h6 class="font-weight-bold mb-1 text-dark">Doctor Fund & Zone Ledger Settings</h6>
                                                <p class="text-muted mb-0 small">Manage default dedicated bank account and preferences for the isolated Doctor Fund module.</p>
                                            </div>
                                        </div>
                                    </div>

                                    @php
                                        $dfRow = \App\Models\Setting::where('key', 'doctor_fund_account_name')->first();
                                        $dfAccountVal = $dfRow ? $dfRow->value : 'SHAHZAIB RANA';
                                    @endphp

                                    <div class="form-group">
                                        <label class="font-weight-bold text-dark">Doctor Fund Dedicated Account / Bank Name</label>
                                        <div class="input-group" style="max-width: 650px;">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-university text-muted"></i></span>
                                            </div>
                                            <input type="text" list="df_account_suggestions" name="settings[doctor_fund_account_name]"
                                                class="form-control" value="{{ $dfAccountVal }}" {{ !$canEditSettings ? 'disabled' : '' }}
                                                placeholder="e.g. SHAHZAIB RANA or Meezan Doctor Fund">
                                        </div>
                                        <datalist id="df_account_suggestions">
                                            <option value="SHAHZAIB RANA">
                                            <option value="Doctor Promotion Fund Account">
                                            <option value="Meezan Bank - Doctor Fund">
                                            <option value="Cash in Hand">
                                            <option value="Bank Alfalah - Doctor Fund">
                                            <option value="Habib Bank Limited (HBL)">
                                        </datalist>
                                        <small class="form-text text-muted mt-2 d-block">
                                            <i class="fas fa-check-circle text-success mr-1"></i>
                                            Dedicated account name used for Doctor Fund recoveries and investments. This account name will automatically pre-fill on all Receiving and Investment vouchers.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                @if($canEditSettings)
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">
                                        <i class="fas fa-lock"></i> Save Settings (Read Only)
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#settingsForm').on('submit', function(e) {
                    e.preventDefault();

                    $.ajax({
                        url: '{{ route('settings.update') }}',
                        method: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to update settings',
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
