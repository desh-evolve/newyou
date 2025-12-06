{{-- resources/views/admin/appointments/today.blade.php --}}

@extends('layouts.admin')

@section('title', "Today's Appointments")

@section('content-header')
<div class="row mb-2">
    <div class="col-sm-6">
        <h1 class="m-0">
            <i class="fas fa-calendar-day mr-2"></i>Today's Appointments
            <small class="text-muted">({{ now()->format('l, F j, Y') }})</small>
        </h1>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
            <li class="breadcrumb-item active">Today</li>
        </ol>
    </div>
</div>
@endsection

@section('content')
<!-- Quick Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $appointments->count() }}</h3>
                <p>Total Today</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $appointments->where('appointment_status', 'pending')->count() }}</h3>
                <p>Pending</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $appointments->where('appointment_status', 'confirmed')->count() }}</h3>
                <p>Confirmed</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $appointments->where('appointment_status', 'completed')->count() }}</h3>
                <p>Completed</p>
            </div>
            <div class="icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
</div>

<!-- Filter by Coach -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form action="{{ route('admin.appointments.today') }}" method="GET" class="form-inline">
            <label class="mr-2">Filter by Coach:</label>
            <select name="coach_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All Coaches</option>
                @foreach($coaches as $coach)
                    <option value="{{ $coach->id }}" {{ request('coach_id') == $coach->id ? 'selected' : '' }}>
                        {{ $coach->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<!-- Timeline View -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Schedule Timeline</h3>
        <div class="card-tools">
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-tool">
                <i class="fas fa-calendar"></i> Calendar View
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($appointments->count() > 0)
            <div class="timeline">
                @php
                    $currentHour = null;
                @endphp
                
                @foreach($appointments as $appointment)
                    @php
                        $hour = \Carbon\Carbon::parse($appointment->start_time)->format('g A');
                    @endphp
                    
                    @if($currentHour !== $hour)
                        @php $currentHour = $hour; @endphp
                        <div class="time-label">
                            <span class="bg-primary">{{ $hour }}</span>
                        </div>
                    @endif
                    
                    <div>
                        @switch($appointment->appointment_status)
                            @case('pending')
                                <i class="fas fa-clock bg-warning"></i>
                                @break
                            @case('confirmed')
                                <i class="fas fa-check bg-info"></i>
                                @break
                            @case('in_progress')
                                <i class="fas fa-play bg-primary"></i>
                                @break
                            @case('completed')
                                <i class="fas fa-check-double bg-success"></i>
                                @break
                            @case('cancelled')
                                <i class="fas fa-times bg-danger"></i>
                                @break
                            @default
                                <i class="fas fa-calendar bg-secondary"></i>
                        @endswitch
                        
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $appointment->formatted_time }}
                            </span>
                            <h3 class="timeline-header">
                                <a href="{{ route('admin.appointments.show', $appointment) }}">
                                    {{ $appointment->appointment_number }}
                                </a>
                                {!! $appointment->status_badge !!}
                                {!! $appointment->payment_badge !!}
                            </h3>
                            <div class="timeline-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-user mr-1"></i> Client:</strong><br>
                                        <a href="{{ route('admin.clients.show', $appointment->client) }}">
                                            {{ $appointment->client->full_name }}
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-user-tie mr-1"></i> Coach:</strong><br>
                                        {{ $appointment->coach->name }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong><i class="fas fa-tag mr-1"></i> Type:</strong><br>
                                        {!! $appointment->type_badge !!}
                                    </div>
                                </div>
                                
                                @if($appointment->package || $appointment->service)
                                    <div class="mt-2">
                                        <strong>Package/Service:</strong>
                                        @if($appointment->package)
                                            <span class="badge badge-primary">{{ $appointment->package->name }}</span>
                                        @endif
                                        @if($appointment->service)
                                            <span class="badge badge-secondary">{{ $appointment->service->name }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="timeline-footer">
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                @if($appointment->appointment_status === 'pending')
                                    <form action="{{ route('admin.appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                    </form>
                                @endif
                                
                                @if($appointment->appointment_status === 'confirmed' && $appointment->can_start)
                                    <form action="{{ route('admin.appointments.start', $appointment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                    </form>
                                @endif
                                
                                @if($appointment->appointment_status === 'in_progress')
                                    <form action="{{ route('admin.appointments.complete', $appointment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check-circle"></i> Complete
                                        </button>
                                    </form>
                                @endif
                                
                                @if($appointment->meeting_link && in_array($appointment->appointment_status, ['confirmed', 'in_progress']))
                                    <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn btn-dark btn-sm">
                                        <i class="fas fa-video"></i> Join
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div>
                    <i class="fas fa-clock bg-gray"></i>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                <h4>No Appointments Today</h4>
                <p class="text-muted">There are no appointments scheduled for today.</p>
                <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Appointment
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline-item {
        border-radius: 5px;
    }
    .timeline > div > .timeline-item {
        margin-left: 60px;
        margin-right: 15px;
    }
</style>
@endpush