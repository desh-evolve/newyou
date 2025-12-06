{{-- resources/views/client/appointments/show.blade.php --}}

@extends('layouts.client')

@section('title', 'Appointment Details')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $appointment->appointment_number }}</h4>
                    <div>
                        {!! $appointment->status_badge !!}
                        {!! $appointment->payment_badge !!}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date & Time</h6>
                            <p class="mb-1">
                                <i class="fas fa-calendar text-primary mr-2"></i>
                                {{ $appointment->formatted_date }}
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-clock text-primary mr-2"></i>
                                {{ $appointment->formatted_time }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Type</h6>
                            <p>{!! $appointment->type_badge !!}</p>
                            
                            @if($appointment->meeting_link && in_array($appointment->appointment_status, ['confirmed', 'in_progress']))
                                <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-video mr-2"></i>Join Meeting
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Coach</h6>
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('images/default-avatar.png') }}" class="rounded-circle mr-3" 
                                     style="width: 50px; height: 50px;">
                                <div>
                                    <strong>{{ $appointment->coach->name }}</strong><br>
                                    <small class="text-muted">{{ $appointment->coach->email }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Package/Service</h6>
                            @if($appointment->package)
                                <span class="badge badge-primary">{{ $appointment->package->name }}</span>
                            @elseif($appointment->service)
                                <span class="badge badge-secondary">{{ $appointment->service->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    @if($appointment->client_notes)
                        <div class="mb-4">
                            <h6 class="text-muted">Your Notes</h6>
                            <p>{{ $appointment->client_notes }}</p>
                        </div>
                    @endif

                    @if($appointment->cancellation_reason)
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-times-circle mr-2"></i>Cancellation Reason</h6>
                            <p class="mb-0">{{ $appointment->cancellation_reason }}</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('client.appointments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Appointments
                    </a>
                    
                    @if($appointment->payment_status === 'pending' && $appointment->is_upcoming)
                        <a href="{{ route('client.appointments.payment', $appointment) }}" class="btn btn-success float-right">
                            <i class="fas fa-credit-card mr-2"></i>Pay Now
                        </a>
                    @endif
                </div>
            </div>

            <!-- Session Notes (visible to client) -->
            @if($appointment->notes->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sticky-note mr-2"></i>Session Notes</h5>
                    </div>
                    <div class="card-body">
                        @foreach($appointment->notes as $note)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>{{ $note->title ?: 'Note' }}</strong>
                                        <small class="text-muted">{{ $note->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <p class="mb-0">{{ $note->note_content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Payment Summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">${{ number_format($appointment->amount, 2) }}</td>
                        </tr>
                        @if($appointment->discount_amount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="text-right text-success">-${{ number_format($appointment->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <th>Total:</th>
                            <th class="text-right">${{ number_format($appointment->final_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td class="text-right">{!! $appointment->payment_badge !!}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Need Help Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">If you need to reschedule or have any questions about your appointment, please contact us.</p>
                    <a href="mailto:support@example.com" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-envelope mr-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection