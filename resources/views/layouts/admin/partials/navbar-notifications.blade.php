{{-- resources/views/layouts/admin/partials/navbar-notifications.blade.php --}}

<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" id="notificationsDropdown">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge notification-count">0</span>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        <span class="dropdown-header">
            <span class="notification-count">0</span> Notifications
        </span>
        <div class="dropdown-divider"></div>
        
        <div id="notificationDropdownList">
            <a href="#" class="dropdown-item text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading...
            </a>
        </div>
        
        <div class="dropdown-divider"></div>
        <a href="{{ route('admin.notifications.index') }}" class="dropdown-item dropdown-footer">
            <i class="fas fa-bell mr-1"></i> See All Notifications
        </a>
    </div>
</li>

@push('scripts')
<script>
$(document).ready(function() {
    // Load notifications on page load
    loadNotifications();
    
    // Refresh every 60 seconds
    setInterval(loadNotifications, 60000);
    
    // Load when dropdown is opened
    $('#notificationsDropdown').on('click', function() {
        loadNotifications();
    });
    
    function loadNotifications() {
        $.ajax({
            url: '{{ route("admin.notifications.dropdown") }}',
            type: 'GET',
            success: function(response) {
                // Update count
                $('.notification-count').text(response.unread_count);
                
                // Update dropdown list
                var html = '';
                
                if (response.notifications.length === 0) {
                    html = '<div class="dropdown-item text-center text-muted py-3">' +
                           '<i class="fas fa-bell-slash mb-2"></i><br>No notifications</div>';
                } else {
                    response.notifications.forEach(function(notification) {
                        var bgClass = notification.is_read ? '' : 'bg-light';
                        html += '<a href="' + (notification.appointment_id ? '/admin/appointments/' + notification.appointment_id : '#') + '" ' +
                                'class="dropdown-item ' + bgClass + '">' +
                                '<i class="' + notification.type_icon + ' mr-2"></i>' +
                                '<span class="text-truncate" style="max-width: 200px; display: inline-block;">' + notification.title + '</span>' +
                                '<span class="float-right text-muted text-sm">' + notification.time_ago + '</span>' +
                                '</a>' +
                                '<div class="dropdown-divider"></div>';
                    });
                }
                
                $('#notificationDropdownList').html(html);
            },
            error: function() {
                $('#notificationDropdownList').html(
                    '<div class="dropdown-item text-center text-danger py-3">' +
                    '<i class="fas fa-exclamation-circle"></i> Error loading notifications</div>'
                );
            }
        });
    }
});
</script>
@endpush