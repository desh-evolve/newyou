{{-- resources/views/admin/notifications/index.blade.php --}}

@extends('layouts.admin')

@section('title', 'Notifications')

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-bell mr-2"></i>Notifications
            @if($unreadCount > 0)
                <span class="badge badge-danger">{{ $unreadCount }} unread</span>
            @endif
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Notifications</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Notifications</h3>
                <div class="card-tools">
                    @if($unreadCount > 0)
                        <button type="button" class="btn btn-success btn-sm" id="markAllReadBtn">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                    @endif
                </div>
            </div>
            
            <div class="card-body p-0">
                <!-- Filters -->
                <div class="p-3 border-bottom bg-light">
                    <form action="{{ route('admin.notifications.index') }}" method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <label class="mr-2">Type:</label>
                            <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="appointment_created" {{ request('type') == 'appointment_created' ? 'selected' : '' }}>Appointment Created</option>
                                <option value="appointment_confirmed" {{ request('type') == 'appointment_confirmed' ? 'selected' : '' }}>Appointment Confirmed</option>
                                <option value="appointment_cancelled" {{ request('type') == 'appointment_cancelled' ? 'selected' : '' }}>Appointment Cancelled</option>
                                <option value="appointment_reminder" {{ request('type') == 'appointment_reminder' ? 'selected' : '' }}>Reminder</option>
                                <option value="appointment_completed" {{ request('type') == 'appointment_completed' ? 'selected' : '' }}>Completed</option>
                                <option value="payment_received" {{ request('type') == 'payment_received' ? 'selected' : '' }}>Payment Received</option>
                                <option value="payment_failed" {{ request('type') == 'payment_failed' ? 'selected' : '' }}>Payment Failed</option>
                            </select>
                        </div>
                        <div class="form-group mr-3">
                            <label class="mr-2">Status:</label>
                            <select name="read" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">All</option>
                                <option value="unread" {{ request('read') == 'unread' ? 'selected' : '' }}>Unread</option>
                                <option value="read" {{ request('read') == 'read' ? 'selected' : '' }}>Read</option>
                            </select>
                        </div>
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </form>
                </div>

                <!-- Notifications List -->
                @if($notifications->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($notifications as $notification)
                            <li class="list-group-item {{ $notification->is_read ? '' : 'bg-light' }}" 
                                id="notification-{{ $notification->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex">
                                        <div class="mr-3">
                                            <i class="{{ $notification->type_icon }} fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">
                                                {{ $notification->title }}
                                                @if(!$notification->is_read)
                                                    <span class="badge badge-primary ml-2">New</span>
                                                @endif
                                            </h6>
                                            <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                @if($notification->appointment)
                                                    <span class="mx-2">|</span>
                                                    <a href="{{ route('admin.appointments.show', $notification->appointment_id) }}">
                                                        <i class="fas fa-calendar-alt mr-1"></i>View Appointment
                                                    </a>
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        @if(!$notification->is_read)
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-sm mark-read-btn" 
                                                    data-id="{{ $notification->id }}"
                                                    title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-outline-danger btn-sm delete-notification-btn" 
                                                data-id="{{ $notification->id }}"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                        <h4>No Notifications</h4>
                        <p class="text-muted">You don't have any notifications yet.</p>
                    </div>
                @endif
            </div>
            
            @if($notifications->hasPages())
                <div class="card-footer">
                    {{ $notifications->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mark single notification as read
    $('.mark-read-btn').click(function() {
        var btn = $(this);
        var notificationId = btn.data('id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '/admin/notifications/' + notificationId + '/mark-read',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    var listItem = $('#notification-' + notificationId);
                    listItem.removeClass('bg-light');
                    listItem.find('.badge-primary').remove();
                    btn.remove();
                    
                    // Update unread count in header if exists
                    updateUnreadCount();
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Failed to mark as read.');
            }
        });
    });
    
    // Mark all as read
    $('#markAllReadBtn').click(function() {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '{{ route("admin.notifications.mark-all-read") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="fas fa-check-double"></i> Mark All as Read');
                alert('Failed to mark all as read.');
            }
        });
    });
    
    // Delete notification
    $('.delete-notification-btn').click(function() {
        if (!confirm('Are you sure you want to delete this notification?')) {
            return;
        }
        
        var btn = $(this);
        var notificationId = btn.data('id');
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: '/admin/notifications/' + notificationId,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    $('#notification-' + notificationId).fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if list is empty
                        if ($('.list-group-item').length === 0) {
                            location.reload();
                        }
                    });
                    
                    updateUnreadCount();
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Failed to delete notification.');
            }
        });
    });
    
    // Update unread count in navbar
    function updateUnreadCount() {
        $.get('{{ route("admin.notifications.unread-count") }}', function(response) {
            var count = response.count || 0;
            $('.notification-count').text(count);
            
            if (count === 0) {
                $('#markAllReadBtn').hide();
            }
        });
    }
});
</script>
@endpush