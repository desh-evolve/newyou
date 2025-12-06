{{-- resources/views/admin/appointments/calendar.blade.php --}}

@extends('layouts.admin')

@section('title', 'Appointment Calendar')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    #calendar {
        max-width: 100%;
        margin: 0 auto;
    }
    .fc-event {
        cursor: pointer;
        padding: 2px 5px;
        font-size: 12px;
    }
    .fc-event-title {
        font-weight: 500;
    }
    .appointment-tooltip {
        position: absolute;
        z-index: 10000;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 300px;
    }
</style>
@endpush

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">Appointment Calendar</h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
            <li class="breadcrumb-item active">Calendar</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Calendar View</h3>
        <div class="card-tools">
            <div class="d-inline-block mr-3">
                <select id="coachFilter" class="form-control form-control-sm">
                    <option value="">All Coaches</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}">{{ $coach->name }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-list"></i> List View
            </a>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Appointment
            </a>
        </div>
    </div>
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Appointment Detail Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" id="viewAppointmentBtn" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View Full Details
                </a>
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
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        navLinks: true,
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: function(info, successCallback, failureCallback) {
            var coachId = coachFilter.value;
            var url = '{{ route("admin.appointments.calendar.events") }}';
            url += '?start=' + info.startStr + '&end=' + info.endStr;
            if (coachId) {
                url += '&coach_id=' + coachId;
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;
            
            $('#viewAppointmentBtn').attr('href', '/admin/appointments/' + event.id);
            
            var html = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Appointment #:</th>
                                <td>${props.appointment_number}</td>
                            </tr>
                            <tr>
                                <th>Client:</th>
                                <td>${props.client_name}</td>
                            </tr>
                            <tr>
                                <th>Coach:</th>
                                <td>${props.coach_name}</td>
                            </tr>
                            <tr>
                                <th>Date & Time:</th>
                                <td>${event.start.toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th>Type:</th>
                                <td>${props.type.replace('_', ' ').toUpperCase()}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span class="badge badge-info">${props.status.replace('_', ' ').toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <th>Payment:</th>
                                <td><span class="badge badge-${props.payment_status === 'paid' ? 'success' : 'warning'}">${props.payment_status.toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <th>Package/Service:</th>
                                <td>${props.package || props.service || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
            
            $('#appointmentModalBody').html(html);
            $('#appointmentModal').modal('show');
        },
        select: function(info) {
            // Redirect to create appointment with pre-selected date
            window.location.href = '{{ route("admin.appointments.create") }}?date=' + info.startStr;
        },
        eventDidMount: function(info) {
            // Add tooltip
            $(info.el).tooltip({
                title: info.event.extendedProps.client_name + ' - ' + info.event.extendedProps.status,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });
    
    calendar.render();
    
    // Coach filter change
    coachFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });
});
</script>
@endpush