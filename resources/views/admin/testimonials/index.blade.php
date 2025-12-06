@extends('layouts.admin')

@section('title', 'Testimonials Management')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Testimonials Management</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Testimonials</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Testimonials</h3>
            <div class="card-tools">
                <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Add Testimonial
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.testimonials.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by name, company..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="approval_status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="60">Image</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Website</th>
                            <th>Order</th>
                            <th>Submitted By</th>
                            <th>Date</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testimonials as $testimonial)
                            <tr>
                                <td>
                                    <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" class="img-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                </td>
                                <td>
                                    <strong>{{ $testimonial->name }}</strong>
                                    @if($testimonial->designation)
                                        <br><small class="text-muted">{{ $testimonial->designation }}</small>
@endif
</td>
<td>{{ $testimonial->company ?? '-' }}</td>
<td>
@for($i = 1; $i <= 5; $i++)
<i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
@endfor
</td>
<td>{!! $testimonial->approval_status_badge !!}</td>
<td>
<div class="custom-control custom-switch">
<input type="checkbox" class="custom-control-input toggle-visibility"
id="visibility-{{ $testimonial->id }}"
data-id="{{ $testimonial->id }}"
{{ $testimonial->show_on_website ? 'checked' : '' }}
{{ $testimonial->approval_status !== 'approved' ? 'disabled' : '' }}>
<label class="custom-control-label" for="visibility-{{ $testimonial->id }}"></label>
</div>
</td>
<td>
<input type="number" class="form-control form-control-sm order-input" 
                                        style="width: 60px;" 
                                        value="{{ $testimonial->display_order }}"
                                        data-id="{{ $testimonial->id }}">
</td>
<td>
{{ $testimonial->user->name ?? 'N/A' }}
</td>
<td>{{ $testimonial->created_at->format('M d, Y') }}</td>
<td>
<div class="btn-group">
@if($testimonial->approval_status === 'pending')
<button class="btn btn-sm btn-success approve-btn" data-id="{{ $testimonial->id }}" title="Approve">
<i class="fas fa-check"></i>
</button>
<button class="btn btn-sm btn-danger reject-btn" data-id="{{ $testimonial->id }}" title="Reject">
<i class="fas fa-times"></i>
</button>
@endif
<a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="btn btn-sm btn-primary" title="Edit">
<i class="fas fa-edit"></i>
</a>
<button class="btn btn-sm btn-info view-btn" 
                                             data-testimonial="{{ $testimonial->testimonial }}"
                                             data-name="{{ $testimonial->name }}"
                                             data-notes="{{ $testimonial->admin_notes }}"
                                             title="View Details">
<i class="fas fa-eye"></i>
</button>
<form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST" style="display: inline;">
@csrf
@method('DELETE')
<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')" title="Delete">
<i class="fas fa-trash"></i>
</button>
</form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="10" class="text-center">No testimonials found.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
        <!-- Pagination -->
        <div class="mt-3">
            {{ $testimonials->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Testimonial Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <h6>Testimonial:</h6>
                <p id="modal-testimonial"></p>
                <h6>Admin Notes:</h6>
                <p id="modal-notes" class="text-muted"></p>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Testimonial</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Reason for Rejection:</label>
                    <textarea class="form-control" id="reject-notes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-reject">Reject</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    // Approve testimonial
    $('.approve-btn').click(function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to approve this testimonial?')) {
            $.post(`/admin/testimonials/${id}/approve`, {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                location.reload();
            }).fail(function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            });
        }
    });

    // Reject testimonial
    let rejectId = null;
    $('.reject-btn').click(function() {
        rejectId = $(this).data('id');
        $('#rejectModal').modal('show');
    });

    $('#confirm-reject').click(function() {
        const notes = $('#reject-notes').val();
        $.post(`/admin/testimonials/${rejectId}/reject`, {
            _token: '{{ csrf_token() }}',
            admin_notes: notes
        }).done(function(response) {
            location.reload();
        }).fail(function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
        });
    });

    // Toggle visibility
    $('.toggle-visibility').change(function() {
        const id = $(this).data('id');
        $.post(`/admin/testimonials/${id}/toggle-visibility`, {
            _token: '{{ csrf_token() }}'
        }).done(function(response) {
            if (typeof toastr !== 'undefined') {
                toastr.success(response.message);
            }
        }).fail(function(xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            location.reload();
        });
    });

    // Update order on blur
    $('.order-input').on('blur', function() {
        const orders = [];
        $('.order-input').each(function() {
            orders.push({
                id: $(this).data('id'),
                order: $(this).val()
            });
        });

        $.post('/admin/testimonials/update-order', {
            _token: '{{ csrf_token() }}',
            orders: orders
        }).done(function(response) {
            if (typeof toastr !== 'undefined') {
                toastr.success(response.message);
            }
        });
    });

    // View details
    $('.view-btn').click(function() {
        $('#modal-testimonial').text($(this).data('testimonial'));
        $('#modal-notes').text($(this).data('notes') || 'No notes');
        $('#viewModal').modal('show');
    });
});
</script>
@endpush