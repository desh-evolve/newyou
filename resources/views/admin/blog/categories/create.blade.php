@extends('layouts.admin')

@section('title', 'Create Category')

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Create Category</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.categories.index') }}">Categories</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ route('admin.blog.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Category Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Auto-generated if empty">
                            @error('slug')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
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
                            <div class="custom-file">
                                <input type="file" name="image" id="image" class="custom-file-input @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(this)">
                                <label class="custom-file-label" for="image">Choose file</label>
                            </div>
                            <img id="image-preview" class="mt-2 img-thumbnail d-none" style="max-height: 150px;">
                            @error('image')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="order">Display Order</label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', 0) }}" min="0">
                            @error('order')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Category
                        </button>
                        <a href="{{ route('admin.blog.categories.index') }}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
// Auto-generate slug
$('#name').on('keyup blur', function() {
    if (!$('#slug').val()) {
        $('#slug').val($(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''));
    }
});

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