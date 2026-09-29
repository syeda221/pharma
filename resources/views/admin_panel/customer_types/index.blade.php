@extends('admin_panel.layout.app')
@section('content')

@if (session('success'))
    <script>
    $('.modal').on('hide.bs.modal', function () {
        if (document.activeElement) {
            document.activeElement.blur();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#4f46e5'
        });
    });
    </script>
@endif

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    .customertype-page {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #1e293b;
        padding-bottom: 40px;
    }

    /* Page Header */
    .customertype-header {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 20px 24px;
        border-radius: 16px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .customertype-title {
        font-weight: 800;
        font-size: 1.35rem;
        color: #0f172a;
        margin-bottom: 2px;
        letter-spacing: -0.02em;
    }
    .customertype-sub {
        font-size: 0.82rem;
        color: #64748b;
    }

    /* Stat Cards */
    .customertype-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 16px;
        height: 100%;
    }
    .customertype-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .customertype-stat-val {
        font-weight: 800;
        font-size: 1.25rem;
        color: #0f172a;
        line-height: 1.2;
    }
    .customertype-stat-lbl {
        font-size: 0.78rem;
        color: #64748b;
        font-weight: 500;
    }

    /* Primary Gradient Button */
    .btn-customertype-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #ffffff !important;
        border: none;
        padding: 10px 20px;
        font-size: 0.86rem;
        font-weight: 600;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.22);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .btn-customertype-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.35);
    }

    /* Card & Table */
    .customertype-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        overflow: hidden;
    }
    .customertype-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .customertype-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    #default-datatable {
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    #default-datatable thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 20px;
    }
    #default-datatable tbody td {
        padding: 14px 20px;
        vertical-align: middle;
        font-size: 0.88rem;
        border-bottom: 1px solid #f1f5f9;
    }
    #default-datatable tbody tr:hover {
        background-color: #f8fafc;
    }

    /* ID Badge */
    .customertype-id-badge {
        background: #f1f5f9;
        color: #475569;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 6px;
        font-family: monospace;
    }

    /* Mobile Cards View */
    .mobile-customertype-cards {
        display: none;
        padding: 14px;
        flex-direction: column;
        gap: 12px;
    }
    .customertype-mcard {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.03);
    }
    .customertype-mcard-hdr {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    .customertype-mcard-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
    }
    .customertype-mcard-desc {
        font-size: 0.82rem;
        color: #64748b;
    }
    .customertype-mcard-actions {
        display: flex;
        gap: 8px;
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        justify-content: flex-end;
    }

    /* Responsive Breakpoints */
    @media (max-width: 768px) {
        .customertype-card-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 16px;
        }
        .btn-customertype-primary {
            width: 100%;
            justify-content: center;
            height: 42px;
        }
        .customertype-table-wrap {
            display: none !important;
        }
        .mobile-customertype-cards {
            display: flex;
        }
    }
</style>

<div class="customertype-page container-fluid px-3 px-md-4 pt-3">
    
    {{-- Header Row --}}
    <div class="customertype-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h3 class="customertype-title"><i class="fas fa-tags text-primary me-2"></i>Customer Type Management</h3>
            <div class="customertype-sub">Manage customer groups, credits, and transaction categories</div>
        </div>
        @can('customer_types.create')
            <button type="button" class="btn-customertype-primary" data-toggle="modal" data-target="#customerTypeModal" id="reset">
                <i class="fas fa-plus"></i> Create Customer Type
            </button>
        @endcan
    </div>

    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="customertype-stat-card">
                <div class="customertype-stat-icon"><i class="fas fa-layer-group"></i></div>
                <div>
                    <div class="customertype-stat-val">{{ count($types) }}</div>
                    <div class="customertype-stat-lbl">Total Customer Types</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="customertype-card">
        <div class="customertype-card-header">
            <div class="fw-bold text-dark"><i class="fas fa-list me-1 text-muted"></i> All Customer Types</div>
            <div class="text-muted small">Showing {{ count($types) }} entries</div>
        </div>

        {{-- Desktop Table View --}}
        <div class="customertype-table-wrap">
            <table id="default-datatable" class="table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">Id</th>
                        <th class="text-start">Type Name</th>
                        <th class="text-center" style="width: 260px;">Discount Tiers (%)</th>
                        <th class="text-start">Description</th>
                        <th class="text-start" style="width: 140px;">Status</th>
                        <th class="text-end pe-4" style="width: 160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td class="text-center id"><span class="customertype-id-badge">#{{ $type->id }}</span></td>
                            <td class="text-start name fw-semibold text-dark">{{ $type->name }}</td>
                            <td class="text-center discount_pct">
                                <div class="d-flex justify-content-center gap-1 flex-wrap">
                                    <span class="badge bg-info text-white px-2 py-1" title="Medical Discount" style="font-size: 0.75rem;">
                                        <i class="fas fa-clinic-medical me-1"></i>Med: {{ number_format($type->discount_medical ?? 0, 1) }}%
                                    </span>
                                    <span class="badge bg-success text-white px-2 py-1" title="Doctor Discount" style="font-size: 0.75rem;">
                                        <i class="fas fa-user-md me-1"></i>Doc: {{ number_format($type->discount_doctor > 0 ? $type->discount_doctor : ($type->discount_percentage ?? 0), 1) }}%
                                    </span>
                                    <span class="badge bg-primary text-white px-2 py-1" title="Distribution Discount" style="font-size: 0.75rem;">
                                        <i class="fas fa-boxes me-1"></i>Dist: {{ number_format($type->discount_distribution ?? 0, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="text-start description text-muted">{{ $type->description ?: 'N/A' }}</td>
                            <td class="text-start">
                                @if($type->is_static)
                                    <span class="badge bg-danger text-white"><i class="fas fa-lock me-1"></i> Static System</span>
                                @else
                                    <span class="badge bg-success text-white"><i class="fas fa-cog me-1"></i> Dynamic</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @can('customer_types.edit')
                                    <button class="btn btn-primary btn-sm edit-btn" 
                                            data-id="{{ $type->id }}" 
                                            data-static="{{ $type->is_static ? 1 : 0 }}" 
                                            data-med="{{ $type->discount_medical ?? 0 }}"
                                            data-doc="{{ $type->discount_doctor > 0 ? $type->discount_doctor : ($type->discount_percentage ?? 0) }}"
                                            data-dist="{{ $type->discount_distribution ?? 0 }}"
                                            data-discount="{{ $type->discount_percentage ?? 0 }}">Edit</button>
                                @endcan
                                @if(!$type->is_static)
                                    @can('customer_types.delete')
                                        <button class="btn btn-danger btn-sm delete-btn" data-url="{{ route('customer-types.destroy', $type->id) }}" data-msg="Are you sure you want to delete this customer type?" data-method="get" onclick="logoutAndDeleteFunction(this)">Delete</button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (< 768px) --}}
        <div class="mobile-customertype-cards">
            @foreach ($types as $type)
                <div class="customertype-mcard">
                    <div class="customertype-mcard-hdr">
                        <span class="customertype-id-badge">#{{ $type->id }}</span>
                        @if($type->is_static)
                            <span class="badge bg-danger text-white"><i class="fas fa-lock me-1"></i> Static</span>
                        @else
                            <span class="badge bg-success text-white"><i class="fas fa-cog me-1"></i> Dynamic</span>
                        @endif
                    </div>
                    <div class="customertype-mcard-title mb-1 name">{{ $type->name }}</div>
                    <div class="d-flex flex-column gap-1 mb-2">
                        <span class="small text-muted fw-bold">Discount Tiers:</span>
                        <div class="d-flex gap-1 flex-wrap">
                            <span class="badge bg-info text-white">Med: {{ number_format($type->discount_medical ?? 0, 1) }}%</span>
                            <span class="badge bg-success text-white">Doc: {{ number_format($type->discount_doctor > 0 ? $type->discount_doctor : ($type->discount_percentage ?? 0), 1) }}%</span>
                            <span class="badge bg-primary text-white">Dist: {{ number_format($type->discount_distribution ?? 0, 1) }}%</span>
                        </div>
                    </div>
                    <div class="customertype-mcard-desc description">{{ $type->description ?: 'N/A' }}</div>
                    <div class="customertype-mcard-actions">
                        @can('customer_types.edit')
                            <button class="btn btn-primary btn-sm edit-btn" 
                                    data-id="{{ $type->id }}" 
                                    data-static="{{ $type->is_static ? 1 : 0 }}" 
                                    data-med="{{ $type->discount_medical ?? 0 }}"
                                    data-doc="{{ $type->discount_doctor > 0 ? $type->discount_doctor : ($type->discount_percentage ?? 0) }}"
                                    data-dist="{{ $type->discount_distribution ?? 0 }}"
                                    data-discount="{{ $type->discount_percentage ?? 0 }}">Edit</button>
                        @endcan
                        @if(!$type->is_static)
                            @can('customer_types.delete')
                                <button class="btn btn-danger btn-sm delete-btn" data-url="{{ route('customer-types.destroy', $type->id) }}" data-msg="Are you sure you want to delete this customer type?" data-method="get" onclick="logoutAndDeleteFunction(this)">Delete</button>
                            @endcan
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Add/Edit Customer Type Modal --}}
<div class="modal fade" id="customerTypeModal" tabindex="-1" aria-labelledby="customerTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-white px-4 py-3 border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="customerTypeModalLabel"><i class="fas fa-tags text-primary me-2"></i><span id="modalTitleText">Add Customer Type</span></h5>
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="myform" action="{{ route('customer-types.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="edit_id" id="edit_id" />
                    
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold text-dark small">Customer Type Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control px-3 py-2" id="name" placeholder="Enter type name (e.g. Doctor, Staff, Wholesale, Retailer)..." required style="border-radius: 10px; border: 1.5px solid #cbd5e1;" />
                    </div>

                    {{-- 3-Tier Discount Structure --}}
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-percentage text-primary"></i> 3-Tier Discount Options (%)
                        </label>
                        <p class="text-muted small mb-3">Is customer type ke liye teeno discount rates set karein. Sale screen par aap 1-click toggle se koi bhi discount apply kar sakenge:</p>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="discount_medical" class="form-label fw-semibold text-dark small d-flex align-items-center gap-1">
                                    <i class="fas fa-clinic-medical text-info"></i> Medical Discount
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="discount_medical" class="form-control text-end fw-bold" id="discount_medical" placeholder="0.00" value="0" style="border-radius: 8px 0 0 8px;" />
                                    <span class="input-group-text bg-info text-white fw-bold" style="border-radius: 0 8px 8px 0;">%</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Medical store / pharmacy rate</small>
                            </div>

                            <div class="col-md-4">
                                <label for="discount_doctor" class="form-label fw-semibold text-dark small d-flex align-items-center gap-1">
                                    <i class="fas fa-user-md text-success"></i> Doctor Discount
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="discount_doctor" class="form-control text-end fw-bold" id="discount_doctor" placeholder="0.00" value="0" style="border-radius: 8px 0 0 8px;" />
                                    <span class="input-group-text bg-success text-white fw-bold" style="border-radius: 0 8px 8px 0;">%</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Doctor / Clinic default rate</small>
                            </div>

                            <div class="col-md-4">
                                <label for="discount_distribution" class="form-label fw-semibold text-dark small d-flex align-items-center gap-1">
                                    <i class="fas fa-boxes text-primary"></i> Distribution Discount
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" name="discount_distribution" class="form-control text-end fw-bold" id="discount_distribution" placeholder="0.00" value="0" style="border-radius: 8px 0 0 8px;" />
                                    <span class="input-group-text bg-primary text-white fw-bold" style="border-radius: 0 8px 8px 0;">%</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Distributor / Wholesale rate</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold text-dark small">Description</label>
                        <textarea name="description" class="form-control px-3 py-2" id="description" rows="2" placeholder="Enter description..." style="border-radius: 10px; border: 1.5px solid #cbd5e1;"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-dismiss="modal" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                    @can('customer_types.create')
                        <button type="submit" class="btn btn-primary px-4 fw-bold save-btn" style="border-radius: 8px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
                            <i class="fas fa-check me-1"></i> Save Customer Type
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ asset('assets/js/mycode.js') }}"></script>
<script>
    // Fix ARIA focus warning on modal close
    $('.modal').on('hide.bs.modal', function () {
        if (document.activeElement) {
            document.activeElement.blur();
        }
    });

    $(document).on('submit', '.myform', function(e) {
        e.preventDefault();
        var formdata = new FormData(this);
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        $(this).find(':submit').attr('disabled', true);
        myAjax(url, formdata, method);
    });

    $(document).on('click', '.edit-btn', function() {
        var tr = $(this).closest("tr, .customertype-mcard");
        var id = $(this).data('id');
        var isStatic = $(this).data('static');
        var medDisc = $(this).data('med') || 0;
        var docDisc = $(this).data('doc') || $(this).data('discount') || 0;
        var distDisc = $(this).data('dist') || 0;
        var name = tr.find(".name").text().trim();
        var description = tr.find(".description").text().trim();
        if (description === 'N/A') description = '';

        $('#edit_id').val(id);
        $('#name').val(name);
        if (isStatic == 1) {
            $('#name').prop('readonly', true);
        } else {
            $('#name').prop('readonly', false);
        }
        $('#discount_medical').val(medDisc);
        $('#discount_doctor').val(docDisc);
        $('#discount_distribution').val(distDisc);
        $('#description').val(description);
        $('#modalTitleText').text('Edit Customer Type');
        $("#customerTypeModal").modal("show");
    });

    $('#reset').on('click', function() {
        $('#edit_id').val('');
        $('#name').val('').prop('readonly', false);
        $('#discount_medical').val(0);
        $('#discount_doctor').val(0);
        $('#discount_distribution').val(0);
        $('#description').val('');
        $('#modalTitleText').text('Add Customer Type');
    });

    $(document).ready(function() {
        $('#default-datatable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "order": [[0, 'desc']],
            "language": {
                "search": "Search Customer Type:",
                "lengthMenu": "Show _MENU_ entries"
            }
        });
    });
</script>
@endsection
