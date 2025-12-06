@extends('layouts.admin')

@section('title', 'My Testimonials')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">My Testimonials</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Testimonials</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')

    <div class="mb-3">
        <a href="{{ route('client.testimonials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Submit New Testimonial
        </a>
    </div>

    <div class="row">
        @forelse($testimonials as $testimonial)
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $testimonial->image_url }}" alt="{{ $testimonial->name }}" 
                                 class="img-circle mr-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h5 class="mb-0">{{ $testimonial->name }}</h5>
                                @if($testimonial->designation)
                                    <small class="text-muted">{{ $testimonial->designation }}</small>
                                @endif
                                @if($testimonial->company)
                                    <small class="text-muted"> at {{ $testimonial->company }}</small>
                                @endif
                            </div>
                        </div>

                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                        </div>

                        <p class="text-muted">{{ Str::limit($testimonial->testimonial, 150) }}</p>

                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                {!! $testimonial->approval_status_badge !!}
                                @if($testimonial->show_on_website && $testimonial->approval_status === 'approved')
                                    <span class="badge badge-success ml-1">
                                        <i class="fas fa-globe"></i> Visible
                                    </span>
                                @endif
                            </div>
                            <div>
                                <small class="text-muted">{{ $testimonial->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>

                        @if($testimonial->admin_notes && in_array($testimonial->approval_status, ['rejected']))
                            <div class="alert alert-warning mt-3 mb-0">
                                <strong>Admin Feedback:</strong><br>
                                {{ $testimonial->admin_notes }}
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        @if(in_array($testimonial->approval_status, ['pending', 'rejected']))
                            <a href="{{ route('client.testimonials.edit', $testimonial->id) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <button class="btn btn-sm btn-info view-details" 
                                data-testimonial="{{ $testimonial->testimonial }}"
                                data-name="{{ $testimonial->name }}">
                            <i class="fas fa-eye"></i> View Full
                        </button>
                        <form action="{{ route('client.testimonials.destroy', $testimonial->id) }}" 
                              method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this testimonial?')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't submitted any testimonials yet.
                    <a href="{{ route('client.testimonials.create') }}">Submit your first testimonial</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $testimonials->links() }}
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-name"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p id="modal-testimonial"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-details').click(function() {
        $('#modal-name').text($(this).data('name'));
        $('#modal-testimonial').text($(this).data('testimonial'));
        $('#viewModal').modal('show');
    });
});
</script>
@endpush