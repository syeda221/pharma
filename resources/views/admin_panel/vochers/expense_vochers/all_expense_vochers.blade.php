@extends('admin_panel.layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ asset('assets/vendors/bootstrap-icons/css/bootstrap-icons.min.css') }}">

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">Expense Vouchers</h4>
                        <p class="text-muted mb-0 small">View and manage all expense vouchers</p>
                    </div>
                    @can('expense.voucher.create')
                        <a class="btn btn-primary shadow-sm fw-bold d-inline-flex align-items-center" href="{{ route('expense_vochers') }}" style="height: 38px; border-radius: 6px;">
                            <i class="fas fa-plus mr-1" style="margin-right: 5px;"></i> Add Expense Voucher
                        </a>
                    @endcan
                </div>
                <div class="card shadow border-0 rounded-4">
                    <div class="card-body">
                        <div class="table-responsive mt-2 mb-4">
                            <table id="example" class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Voucher No</th>
                                        <th>Entry Date</th>
                                        <th>Type</th>
                                        <th>Party</th>
                                        <th>Reference No</th>
                                        <th>Remarks</th>
                                        <th>Amount</th>
                                        <th>Total Amount</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receipts as $item)
                                        @php
                                            // JSON decode for fields that are stored as arrays
                                            $amounts = json_decode($item->amount, true);
                                            $amount = is_array($amounts)
                                                ? (float) ($amounts[0] ?? 0)
                                                : (float) $item->amount;

                                            $refs = json_decode($item->reference_no, true);
                                            $reference = is_array($refs) ? implode(', ', $refs) : $item->reference_no;

                                            $narrations = json_decode($item->narration_id, true);
                                            $narration = is_array($narrations)
                                                ? implode(', ', $narrations)
                                                : $item->narration_id;
                                        @endphp
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->evid }}</td>
                                            <td>{{ $item->entry_date }}</td>
                                            <td>{{ $item->type_label }}</td>
                                            <td>{{ $item->party_name }}</td>
                                            <td>{{ $reference }}</td>
                                            <td>{{ $item->remarks }}</td>
                                            <td>{{ number_format($amount, 2) }}</td>
                                            <td>{{ number_format((float) $item->total_amount, 2) }}</td>
                                            <td>{{ $item->created_at }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <a href="{{ route('expenseprint', $item->id) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" title="Print">
                                                        <i class="bi bi-printer"></i>
                                                    </a>
                                                    @canany(['expense.voucher.edit', 'expense.voucher.create'])
                                                    <a href="{{ route('expense_vochers.edit', $item->id) }}"
                                                        class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    @endcanany
                                                    @can('expense.voucher.delete')
                                                    <form action="{{ route('expense_vouchers.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable({
                order: [[0, 'desc']]
            });
        }

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete this Expense Voucher?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
