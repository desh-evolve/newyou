@extends('layouts.admin')

@section('title', 'Edit Post')

@push('styles')
<style>
    .note-editor .note-toolbar { background: #f4f6f9; }
    .note-editor .note-editing-area { background: #fff; }
    .featured-image-preview { max-width: 100%; max-height: 250px; border-radius: 4px; }
    .featured-image-container { position: relative; display: inline-block; }
    .featured-image-container .remove-btn { position: absolute; top: 5px; right: 5px; }
</style>
@endpush

@section('content-header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Edit Post</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.blog.posts.index') }}">Posts</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <!-- Info Banner -->
    <div class="callout callout-info">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex flex-wrap">
                    <div class="mr-4">
                        <small class="text-muted d-block">Created</small>
                        <strong>{{ $post->created_at->format('M d, Y H:i') }}</strong>
                    </div>
                    <div class="mr-4">
                        <small class="text-muted d-block">Updated</small>
                        <strong>{{ $post->updated_at->format('M d, Y H:i') }}</strong>
                    </div>
                    <div class="mr-4">
                        <small class="text-muted d-block">Author</small>
                        <strong>{{ $post->author->name ?? 'Unknown' }}</strong>
                    </div>
                    <div class="mr-4">
                        <small class="text-muted d-block">Views</small>
                        <strong>{{ number_format($post->views) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-right">
                @switch($post->status)
                    @case('published')
                        <span class="badge badge-success badge-lg p-2">
                            <i class="fas fa-check-circle mr-1"></i> Published
                        </span>
                        @break
                    @case('draft')
                        <span class="badge badge-warning badge-lg p-2">
                            <i class="fas fa-edit mr-1"></i> Draft
                        </span>
                        @break
                    @case('scheduled')
                        <span class="badge badge-primary badge-lg p-2">
                            <i class="fas fa-clock mr-1"></i> Scheduled
                        </span>
                        @break
                @endswitch
            </div>
        </div>
    </div>

    <form action="{{ route('admin.blog.posts.update', $post) }}" method="POST" enctype="multipart/form-data" id="postForm">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Title & Slug -->
                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ url('/blog') }}/</span>
                                </div>
                                <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $post->slug) }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="generateSlug">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            @error('slug')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Content Editor -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Content</h3>
                    </div>
                    <div class="card-body p-0">
                        <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <span class="text-danger small p-2">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Excerpt -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-align-left mr-2"></i>Excerpt</h3>
                    </div>
                    <div class="card-body">
                        <textarea name="excerpt" id="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="3" maxlength="500">{{ old('excerpt', $post->excerpt) }}</textarea>
                        <small class="text-muted"><span id="excerptCount">0</span>/500 characters</small>
                        @error('excerpt')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-search mr-2"></i>SEO Settings</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="meta_title">Meta Title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $post->meta_title) }}" maxlength="70">
                            <small class="text-muted"><span id="metaTitleCount">0</span>/70 characters</small>
                            @error('meta_title')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3" maxlength="160">{{ old('meta_description', $post->meta_description) }}</textarea>
                            <small class="text-muted"><span id="metaDescCount">0</span>/160 characters</small>
                            @error('meta_description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" name="meta_keywords" id="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $post->meta_keywords) }}">
                            @error('meta_keywords')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- SEO Preview -->
                        <div class="mt-4">
                            <label><i class="fab fa-google mr-1"></i> Search Preview</label>
                            <div class="border rounded p-3 bg-white">
                                <div class="text-primary" style="font-size: 18px;" id="seoPreviewTitle">{{ $post->meta_title ?? $post->title }}</div>
                                <div class="text-success small" id="seoPreviewUrl">{{ url('/blog/' . $post->slug) }}</div>
                                <div class="text-muted small" id="seoPreviewDesc">{{ $post->meta_description ?? Str::limit($post->excerpt, 160) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publish Box -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Publish</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="scheduled" {{ old('status', $post->status) == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" id="publishDateGroup" style="{{ old('status', $post->status) == 'scheduled' ? '' : 'display: none;' }}">
                            <label for="published_at">Publish Date</label>
                            <input type="datetime-local" name="published_at" id="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_featured" id="is_featured" class="custom-control-input" value="1" {{ old('is_featured', $post->is_featured) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_featured">
                                    <i class="fas fa-star text-warning mr-1"></i> Featured Post
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="allow_comments" id="allow_comments" class="custom-control-input" value="1" {{ old('allow_comments', $post->allow_comments) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="allow_comments">
                                    <i class="fas fa-comments mr-1"></i> Allow Comments
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save mr-1"></i> Update Post
                        </button>
                        <div class="btn-group btn-block mt-2">
                            <a href="{{ route('admin.blog.posts.show', $post) }}" class="btn btn-default">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </div>

                <!-- Category -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-folder mr-2"></i>Category</h3>
                    </div>
                    <div class="card-body">
                        <select name="blog_category_id" id="blog_category_id" class="form-control select2 @error('blog_category_id') is-invalid @enderror" data-placeholder="Select category...">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('blog_category_id', $post->blog_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('blog_category_id')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Tags -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Tags</h3>
                    </div>
                    <div class="card-body">
                        <select name="tags[]" id="tags" class="form-control select2 @error('tags') is-invalid @enderror" multiple data-placeholder="Select tags...">
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ in_array($tag->id, old('tags', $post->tags->pluck('id')->toArray())) ? 'selected' : '' }}>
                                    {{ $tag->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('tags')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror

                        <!-- Quick Add Tag -->
                        <div class="mt-3">
                            <div class="input-group input-group-sm">
                                <input type="text" id="newTagName" class="form-control" placeholder="New tag name...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" id="addTagBtn">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-image mr-2"></i>Featured Image</h3>
                    </div>
                    <div class="card-body">
                        <!-- Current Image -->
                        @if($post->featured_image)
                            <div id="currentFeaturedImage" class="mb-3 text-center">
                                <div class="featured-image-container">
                                    <img src="{{ Storage::url($post->featured_image) }}" class="featured-image-preview">
                                    <button type="button" class="btn btn-danger btn-sm remove-btn" id="removeCurrentImage">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="remove_featured_image" id="remove_featured_image" value="0">
                            </div>
                        @endif

                        <div id="featuredImagePreview" class="mb-3 text-center d-none">
                            <div class="featured-image-container">
                                <img src="" id="previewImg" class="featured-image-preview">
                                <button type="button" class="btn btn-danger btn-sm remove-btn" id="removeFeaturedImage">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="custom-file" id="featuredImageUpload">
                            <input type="file" name="featured_image" id="featured_image" class="custom-file-input @error('featured_image') is-invalid @enderror" accept="image/*">
                            <label class="custom-file-label" for="featured_image">Choose new image...</label>
                        </div>
                        <small class="text-muted d-block mt-2">Recommended: 1200x630px. Max: 5MB</small>
                        @error('featured_image')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Danger Zone</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Permanently delete this post.</p>
                        <button type="button" class="btn btn-outline-danger btn-block" onclick="confirmDelete()">
                            <i class="fas fa-trash mr-1"></i> Delete Post
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Form -->
    <form id="deleteForm" action="{{ route('admin.blog.posts.destroy', $post) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        allowClear: true
    });

    // Initialize Summernote
    $('#content').summernote({
        height: 400,
        placeholder: 'Write your post content here...',
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
            onImageUpload: function(files) {
                uploadImage(files[0]);
            }
        }
    });

    // Upload image from Summernote
    function uploadImage(file) {
        let formData = new FormData();
        formData.append('image', file);

        $.ajax({
            url: '{{ route("admin.blog.posts.upload-image") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#content').summernote('insertImage', response.url);
            },
            error: function(xhr) {
                alert('Image upload failed.');
            }
        });
    }

    // Generate slug
    $('#generateSlug').on('click', function() {
        let title = $('#title').val();
        let slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        $('#slug').val(slug);
        updateSeoPreview();
    });

    // Status change
    $('#status').on('change', function() {
        if ($(this).val() === 'scheduled') {
            $('#publishDateGroup').show();
        } else {
            $('#publishDateGroup').hide();
        }
    });

    // Character counters
    $('#excerpt').on('input', function() {
        $('#excerptCount').text($(this).val().length);
    });

    $('#meta_title').on('input', function() {
        $('#metaTitleCount').text($(this).val().length);
        updateSeoPreview();
    });

    $('#meta_description').on('input', function() {
        $('#metaDescCount').text($(this).val().length);
        updateSeoPreview();
    });

    $('#title, #slug').on('input', updateSeoPreview);

    function updateSeoPreview() {
        let title = $('#meta_title').val() || $('#title').val();
        let slug = $('#slug').val();
        let desc = $('#meta_description').val() || $('#excerpt').val();
        
        $('#seoPreviewTitle').text(title);
        $('#seoPreviewUrl').text('{{ url("/blog") }}/' + slug);
        $('#seoPreviewDesc').text(desc ? desc.substring(0, 160) : '');
    }

    // Featured Image Preview
    $('#featured_image').on('change', function() {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#featuredImagePreview').removeClass('d-none');
                $('#currentFeaturedImage').addClass('d-none');
            };
            reader.readAsDataURL(file);
            $(this).next('.custom-file-label').text(file.name);
        }
    });

    $('#removeFeaturedImage').on('click', function() {
        $('#featured_image').val('');
        $('#featuredImagePreview').addClass('d-none');
        $('.custom-file-label').text('Choose new image...');
    });

    $('#removeCurrentImage').on('click', function() {
        $('#remove_featured_image').val('1');
        $('#currentFeaturedImage').addClass('d-none');
    });

    // Quick Add Tag
    $('#addTagBtn').on('click', function() {
        let tagName = $('#newTagName').val().trim();
        if (!tagName) return;

        $.ajax({
            url: '{{ route("admin.blog.tags.store") }}',
            method: 'POST',
            data: { name: tagName },
            success: function(response) {
                if (response.success) {
                    let newOption = new Option(response.tag.name, response.tag.id, true, true);
                    $('#tags').append(newOption).trigger('change');
                    $('#newTagName').val('');
                }
            },
            error: function() {
                alert('Failed to create tag.');
            }
        });
    });

    // Initialize counters
    $('#excerpt, #meta_title, #meta_description').trigger('input');
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endpush