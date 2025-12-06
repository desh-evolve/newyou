{{-- resources/views/admin/time-slots/calendar.blade.php --}}

@extends('layouts.admin')

@section('title', 'Time Slots Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
        min-height: 600px;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 5px;
        font-size: 11px;
        border-radius: 3px;
    }
    .fc-timegrid-slot {
        height: 30px;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        font-size: 13px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        margin-right: 5px;
    }
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
</style>
@endpush

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Time Slots Calendar</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.time-slots.index') }}">Time Slots</a></li>
            <li class="breadcrumb-item active">Calendar</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-calendar-alt mr-2"></i>Slots Calendar View
        </h3>
        <div class="card-tools">
            <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary btn-sm mr-2">
                <i class="fas fa-list"></i> List View
            </a>
            <a href="{{ route('admin.time-slots.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Generate Slots
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label>Select Coach <span class="text-danger">*</span></label>
                    <select id="coachFilter" class="form-control">
                        <option value="">-- Select Coach --</option>
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}" {{ ($selectedCoach ?? '') == $coach->id ? 'selected' : '' }}>
                                {{ $coach->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-8">
                <!-- Legend -->
                <div class="form-group mb-0">
                    <label>Legend</label>
                    <div>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #28a745;"></span>
                            Available
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #ffc107;"></span>
                            Locked
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #17a2b8;"></span>
                            Booked
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #dc3545;"></span>
                            Blocked
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debug Info -->
        <div id="debugInfo" class="alert alert-info d-none">
            <strong>Debug:</strong> <span id="debugMessage"></span>
        </div>

        <!-- Calendar Container -->
        <div id="calendarContainer" style="position: relative;">
            <div id="selectCoachMessage" class="text-center py-5">
                <i class="fas fa-user-tie fa-4x text-muted mb-3"></i>
                <h4>Select a Coach</h4>
                <p class="text-muted">Please select a coach to view their time slots.</p>
            </div>
            <div id="calendarWrapper" class="d-none">
                <div id="loadingOverlay" class="loading-overlay d-none">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-3x mb-2"></i>
                        <p>Loading slots...</p>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Slot Details Modal -->
<div class="modal fade" id="slotModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-clock mr-2"></i>Time Slot Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="slotModalBody">
                <!-- Content loaded dynamically -->
            </div>
            <div class="modal-footer" id="slotModalFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var coachFilter = document.getElementById('coachFilter');
    var calendar = null;
    
    function showDebug(message) {
        console.log('Debug:', message);
        document.getElementById('debugInfo').classList.remove('d-none');
        document.getElementById('debugMessage').textContent = message;
    }
    
    function hideDebug() {
        document.getElementById('debugInfo').classList.add('d-none');
    }
    
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('d-none');
    }
    
    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('d-none');
    }
    
    // Initialize calendar
    function initCalendar() {
        console.log('Initializing calendar...');
        
        if (calendar) {
            calendar.destroy();
        }
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '06:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            allDaySlot: false,
            navLinks: true,
            selectable: true,
            selectMirror: true,
            editable: false,
            dayMaxEvents: true,
            weekends: true,
            nowIndicator: true,
            
            // Event source
            events: function(info, successCallback, failureCallback) {
                var coachId = coachFilter.value;
                
                console.log('Fetching events for coach:', coachId);
                console.log('Date range:', info.startStr, 'to', info.endStr);
                
                if (!coachId) {
                    console.log('No coach selected');
                    successCallback([]);
                    return;
                }
                
                showLoading();
                
                var url = '{{ route("admin.time-slots.calendar.events") }}';
                var params = new URLSearchParams({
                    coach_id: coachId,
                    start: info.startStr,
                    end: info.endStr
                });
                
                console.log('Fetching from:', url + '?' + params.toString());
                
                fetch(url + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(function(data) {
                    console.log('Received data:', data);
                    console.log('Number of events:', data.length);
                    
                    hideLoading();
                    
                    if (data.length === 0) {
                        showDebug('No time slots found for this coach in the selected date range.');
                    } else {
                        hideDebug();
                        showDebug('Found ' + data.length + ' time slots.');
                    }
                    
                    successCallback(data);
                })
                .catch(function(error) {
                    console.error('Error fetching events:', error);
                    hideLoading();
                    showDebug('Error loading slots: ' + error.message);
                    failureCallback(error);
                });
            },
            
            // Event click handler
            eventClick: function(info) {
                console.log('Event clicked:', info.event);
                showSlotDetails(info.event);
            },
            
            // Date select handler (for creating new slots)
            select: function(info) {
                if (!coachFilter.value) {
                    alert('Please select a coach first.');
                    if (calendar) calendar.unselect();
                    return;
                }
                
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                
                if (info.start < today) {
                    alert('Cannot create slots in the past.');
                    if (calendar) calendar.unselect();
                    return;
                }
                
                // Open quick create modal or redirect
                console.log('Selected:', info.startStr, 'to', info.endStr);
                if (calendar) calendar.unselect();
            },
            
            // Loading indicator
            loading: function(isLoading) {
                console.log('Calendar loading:', isLoading);
            },
            
            // Event render
            eventDidMount: function(info) {
                var status = info.event.extendedProps.status || 'available';
                var duration = info.event.extendedProps.duration || 0;
                
                // Add Bootstrap tooltip
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $(info.el).tooltip({
                        title: status.charAt(0).toUpperCase() + status.slice(1) + ' (' + duration + ' min)',
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
            }
        });
        
        calendar.render();
        console.log('Calendar rendered');
    }
    
    // Show/hide calendar based on coach selection
    function toggleCalendarVisibility() {
        var coachId = coachFilter.value;
        console.log('Toggle visibility, coach:', coachId);
        
        if (coachId) {
            document.getElementById('selectCoachMessage').classList.add('d-none');
            document.getElementById('calendarWrapper').classList.remove('d-none');
            
            if (!calendar) {
                initCalendar();
            } else {
                calendar.refetchEvents();
            }
        } else {
            document.getElementById('selectCoachMessage').classList.remove('d-none');
            document.getElementById('calendarWrapper').classList.add('d-none');
            hideDebug();
        }
    }
    
    // Coach filter change event
    coachFilter.addEventListener('change', function() {
        console.log('Coach changed to:', this.value);
        toggleCalendarVisibility();
    });
    
    // Show slot details in modal
    function showSlotDetails(event) {
        var props = event.extendedProps;
        var status = props.status || 'available';
        
        var startTime = event.start ? event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'N/A';
        var endTime = event.end ? event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : 'N/A';
        var dateStr = event.start ? event.start.toLocaleDateString([], {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A';
        
        var statusBadge = {
            'available': '<span class="badge badge-success">Available</span>',
            'locked': '<span class="badge badge-warning">Locked</span>',
            'booked': '<span class="badge badge-info">Booked</span>',
            'blocked': '<span class="badge badge-danger">Blocked</span>'
        }[status] || '<span class="badge badge-secondary">Unknown</span>';
        
        var html = '<table class="table table-borderless mb-0">' +
            '<tr><th style="width: 120px;">Date:</th><td>' + dateStr + '</td></tr>' +
            '<tr><th>Time:</th><td>' + startTime + ' - ' + endTime + '</td></tr>' +
            '<tr><th>Duration:</th><td>' + (props.duration || 0) + ' minutes</td></tr>' +
            '<tr><th>Status:</th><td>' + statusBadge + '</td></tr>' +
            '</table>';
        
        document.getElementById('slotModalBody').innerHTML = html;
        
        // Update footer with actions
        var footerHtml = '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>';
        
        if (status === 'available') {
            footerHtml += ' <a href="{{ url("admin/appointments/create") }}?slot_id=' + event.id + '" class="btn btn-success">' +
                '<i class="fas fa-calendar-plus"></i> Book</a>';
        }
        
        document.getElementById('slotModalFooter').innerHTML = footerHtml;
        
        // Show modal
        if (typeof $ !== 'undefined') {
            $('#slotModal').modal('show');
        }
    }
    
    // Initial check - if coach is pre-selected
    if (coachFilter.value) {
        console.log('Coach pre-selected:', coachFilter.value);
        toggleCalendarVisibility();
    }
});
</script>
@endpush