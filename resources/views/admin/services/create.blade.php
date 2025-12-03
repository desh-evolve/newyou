@extends('layouts.admin')

@section('title', 'Create Service')
@section('page-title', 'Create Service')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.services.index') }}">Services</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <form action="{{ route('admin.services.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="name">Service Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="slug">Slug</label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror" 
                                       id="slug" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated">
                                @error('slug')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="price">Price ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" 
                                       id="price" name="price" value="{{ old('price', '0.00') }}" required>
                                @error('price')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="duration">Duration (minutes)</label>
                                <input type="number" min="1" class="form-control @error('duration') is-invalid @enderror" 
                                       id="duration" name="duration" value="{{ old('duration') }}" placeholder="e.g., 60">
                                @error('duration')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" 
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                                @error('sort_order')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="icon">Icon Class (FontAwesome)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="icon-preview">
                                            <i class="fas fa-icons"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control @error('icon') is-invalid @enderror" 
                                           id="icon" name="icon" value="{{ old('icon') }}" placeholder="fas fa-star">
                                </div>
                                <small class="form-text text-muted">E.g., fas fa-star, fas fa-heart</small>
                                @error('icon')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">Service Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 2MB</small>
                        @error('image')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                        <div id="image-preview-container" class="mt-3" style="display: none;">
                            <img id="image-preview" src="" alt="Preview" class="image-preview">
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Service
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tips</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Name should be descriptive and unique</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Slug is auto-generated from name if left empty</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Duration is in minutes (e.g., 60 = 1 hour)</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Use FontAwesome icons (v6)</li>
                    <li><i class="fas fa-info-circle text-info"></i> Lower sort order appears first</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('blur', function() {
        if ($('#slug').val() === '') {
            const slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
            $('#slug').val(slug);
        }
    });

    // Icon preview
    $('#icon').on('input', function() {
        const iconClass = $(this).val();
        if (iconClass) {
            $('#icon-preview').html('<i class="' + iconClass + '"></i>');
        } else {
            $('#icon-preview').html('<i class="fas fa-icons"></i>');
        }
    });

    // Image preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#image-preview-container').show();
            }
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').text(file.name);
        } else {
            $('#image-preview-container').hide();
            $(this).next('.custom-file-label').text('Choose file');
        }
    });
});
</script>
@endpush