<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
    <!-- Summernote -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
    
    <style>
        .content-wrapper { background-color: #f4f6f9; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); border: 0; }
        .select2-container--bootstrap4 .select2-selection { border-radius: 4px; }
        .note-editor { border-radius: 4px; }
        .note-editor .note-toolbar { background: #f8f9fa; }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    @include('layouts.partials.navbar')

    <!-- Sidebar -->
    @include('layouts.partials.sidebar')

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                @yield('content-header')
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    <!-- Footer -->
    @include('layouts.partials.footer')

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

<script>
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            allowClear: true
        });
    });
</script>
@stack('scripts')
</body>
</html>