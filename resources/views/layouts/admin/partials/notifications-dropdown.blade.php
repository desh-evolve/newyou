{{-- resources/views/layouts/admin/partials/notifications-dropdown.blade.php --}}

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationDropdown">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge notification-count">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-item dropdown-header">
            <span class="notification-count">0</span> Notifications
        </span>
        <div class="dropdown-divider"></div>
        
        <div id="notificationList">
            <a href="#" class="dropdown-item text-center text-muted">
                Loading...
            </a>
        </div>
        
        <div class="dropdown-divider"></div>
        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item dropdown-footer">
            See All Notifications
        </a>
    </div>
</li>

@push('scripts')
<script>
$(document).ready(function() {
    // Load notifications
    function loadNotifications() {
        $.get('{{ route("admin.notifications.dropdown") }}', function(data) {
            $('.notification-count').text(data.unread_count);
            
            let html = '';
            if (data.notifications.length === 0) {
                html = '<a href="#" class="dropdown-item text-center text-muted">No notifications</a>';
            } else {
                data.notifications.forEach(function(notification) {
                    html += `
                        <a href="#" class="dropdown-item notification-item ${notification.is_read ? '' : 'bg-light'}" 
                           data-id="${notification.id}">
                            <i class="${notification.type_icon} mr-2"></i>
                            <span class="text-truncate">${notification.title}</span>
                            <span class="float-right text-muted text-sm">${notification.time_ago}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                });
            }
            
            $('#notificationList').html(html);
        });
    }
    
    // Initial load
    loadNotifications();
    
    // Refresh every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Mark as read on click
    $(document).on('click', '.notification-item', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        $.post(`/admin/notifications/${id}/mark-read`, {
            _token: '{{ csrf_token() }}'
        }, function() {
            loadNotifications();
        });
    });
});
</script>
@endpush