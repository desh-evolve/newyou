{{-- resources/views/client/appointments/index.blade.php --}}

@extends('layouts.client')

@section('title', 'My Appointments')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>My Appointments</h2>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('client.appointments.calendar') }}" class="btn btn-info">
                <i class="fas fa-calendar-alt"></i> Calendar View
            </a>
            <a href="{{ route('client.appointments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Book Appointment
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $upcomingCount }}</h4>
                            <p class="mb-0">Upcoming</p>
                        </div>
                        <i class="fas fa-calendar-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ $completedCount }}</h4>
                            <p class="mb-0">Completed</p>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !request('view') ? 'active' : '' }}" 
               href="{{ route('client.appointments.index') }}">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('view') === 'upcoming' ? 'active' : '' }}" 
               href="{{ route('client.appointments.index', ['view' => 'upcoming']) }}">Upcoming</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('view') === 'past' ? 'active' : '' }}" 
               href="{{ route('client.appointments.index', ['view' => 'past']) }}">Past</a>
        </li>
    </ul>

    <!-- Appointments List -->
    <div class="row">
        @forelse($appointments as $appointment)
            <div class="col-md-6 mb-4">
                <div class="card h-100 {{ $appointment->is_today ? 'border-primary' : '' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            @if($appointment->is_today)
                                <span class="badge badge-primary">Today</span>
                            @endif
                            {{ $appointment->appointment_number }}
                        </span>
                        {!! $appointment->status_badge !!}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><i class="fas fa-calendar text-primary mr-2"></i>{{ $appointment->formatted_date }}</p>
                                <p class="mb-1"><i class="fas fa-clock text-primary mr-2"></i>{{ $appointment->formatted_time }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><i class="fas fa-user-tie text-primary mr-2"></i>{{ $appointment->coach->name }}</p>
                                <p class="mb-1">{!! $appointment->type_badge !!}</p>
                            </div>
                        </div>
                        
                        @if($appointment->package)
                            <p class="mt-2 mb-0">
                                <span class="badge badge-info">{{ $appointment->package->name }}</span>
                            </p>
                        @elseif($appointment->service)
                            <p class="mt-2 mb-0">
                                <span class="badge badge-secondary">{{ $appointment->service->name }}</span>
                            </p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">${{ number_format($appointment->final_amount, 2) }}</span>
                            <div>
                                @if($appointment->payment_status === 'pending' && $appointment->is_upcoming)
                                    <a href="{{ route('client.appointments.payment', $appointment) }}" 
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-credit-card"></i> Pay Now
                                    </a>
                                @endif
                                <a href="{{ route('client.appointments.show', $appointment) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                        <h4>No Appointments Found</h4>
                        <p class="text-muted">You haven't booked any appointments yet.</p>
                        <a href="{{ route('client.appointments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Book Your First Appointment
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $appointments->withQueryString()->links() }}
    </div>
</div>
@endsection