@extends('layouts.admin')

@section('title', isset($testimonial) ? 'Edit Testimonial' : 'Create Testimonial')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">{{ isset($testimonial) ? 'Edit' : 'Create' }} Testimonial</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.testimonials.index') }}">Testimonials</a></li>
                <li class="breadcrumb-item active">{{ isset($testimonial) ? 'Edit' : 'Create' }}</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ isset($testimonial) ? route('admin.testimonials.update', $testimonial->id) : route('admin.testimonials.store') }}" 
          method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($testimonial))
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Testimonial Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $testimonial->name ?? '') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="designation">Designation</label>
                                    <input type="text" name="designation" id="designation" 
                                           class="form-control @error('designation') is-invalid @enderror" 
                                           value="{{ old('designation', $testimonial->designation ?? '') }}">
                                    @error('designation')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="company">Company</label>
                                    <input type="text" name="company" id="company" 
                                           class="form-control @error('company') is-invalid @enderror" 
                                           value="{{ old('company', $testimonial->company ?? '') }}">
                                    @error('company')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="testimonial">Testimonial <span class="text-danger">*</span></label>
                            <textarea name="testimonial" id="testimonial" rows="5" 
                                      class="form-control @error('testimonial') is-invalid @enderror" 
                                      required>{{ old('testimonial', $testimonial->testimonial ?? '') }}</textarea>
                            @error('testimonial')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rating">Rating <span class="text-danger">*</span></label>
                                    <select name="rating" id="rating" class="form-control @error('rating') is-invalid @enderror" required>
                                        @for($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}" {{ old('rating', $testimonial->rating ?? 5) == $i ? 'selected' : '' }}>
                                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('rating')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_order">Display Order</label>
                                    <input type="number" name="display_order" id="display_order" 
                                           class="form-control @error('display_order') is-invalid @enderror" 
                                           value="{{ old('display_order', $testimonial->display_order ?? 0) }}">
                                    @error('display_order')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="image">Image</label>
                            @if(isset($testimonial) && $testimonial->image)
                                <div class="mb-2">
                                    <img src="{{ $testimonial->image_url }}" alt="Current" 
                                         class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                            <div class="custom-file">
                                <input type="file" name="image" id="image" 
                                       class="custom-file-input @error('image') is-invalid @enderror" 
                                       accept="image/*" onchange="previewImage(this)">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                            <img id="image-preview" class="mt-2 img-thumbnail d-none" style="max-height: 150px;">
                            @error('image')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                        </div>

                        <div class="form-group">
                            <label for="approval_status">Approval Status <span class="text-danger">*</span></label>
                            <select name="approval_status" id="approval_status" class="form-control @error('approval_status') is-invalid @enderror" required>
                                <option value="pending" {{ old('approval_status', $testimonial->approval_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('approval_status', $testimonial->approval_status ?? '') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('approval_status', $testimonial->approval_status ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('approval_status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="show_on_website" 
                                       name="show_on_website" value="1" 
                                       {{ old('show_on_website', $testimonial->show_on_website ?? false) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="show_on_website">Show on Website</label>
                            </div>
                            <small class="form-text text-muted">Only approved testimonials will be visible</small>
                        </div>

                        <div class="form-group">
                            <label for="admin_notes">Admin Notes</label>
                            <textarea name="admin_notes" id="admin_notes" rows="3" 
                                      class="form-control @error('admin_notes') is-invalid @enderror" 
                                      placeholder="Internal notes (not visible to client)">{{ old('admin_notes', $testimonial->admin_notes ?? '') }}</textarea>
                            @error('admin_notes')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> {{ isset($testimonial) ? 'Update' : 'Save' }} Testimonial
                        </button>
                        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
// Image preview
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const label = input.nextElementSibling;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
        label.textContent = input.files[0].name;
    }
}
</script>
@endpush