{{-- resources/views/client/appointments/payment.blade.php --}}

@extends('layouts.client')

@section('title', 'Payment - ' . $appointment->appointment_number)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-credit-card mr-2"></i>Complete Payment</h4>
                </div>
                <div class="card-body">
                    <!-- Appointment Summary -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Appointment:</strong> {{ $appointment->appointment_number }}</p>
                                <p class="mb-1"><strong>Date:</strong> {{ $appointment->formatted_date }}</p>
                                <p class="mb-0"><strong>Time:</strong> {{ $appointment->formatted_time }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Coach:</strong> {{ $appointment->coach->name }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</p>
                                <p class="mb-0"><strong>Duration:</strong> {{ $appointment->duration_minutes }} minutes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form id="payment-form">
                        @csrf
                        <div class="form-group">
                            <label>Card Details</label>
                            <div id="card-element" class="form-control" style="height: 40px; padding-top: 10px;"></div>
                            <div id="card-errors" class="text-danger mt-2"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="text-muted">Amount to Pay:</span>
                                <h3 class="mb-0 text-primary">${{ number_format($appointment->final_amount, 2) }}</h3>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg" id="submit-button">
                                <i class="fas fa-lock mr-2"></i>Pay Now
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="text-center text-muted">
                        <p><i class="fas fa-shield-alt mr-2"></i>Your payment is secured with Stripe</p>
                        <img src="{{ asset('images/stripe-badge.png') }}" alt="Stripe" style="height: 30px;">
                    </div>
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('client.appointments.index') }}" class="text-muted">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Appointments
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe('{{ config("services.stripe.key") }}');
    var elements = stripe.elements();
    
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    
    var cardElement = elements.create('card', {style: style});
    cardElement.mount('#card-element');
    
    cardElement.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    var form = document.getElementById('payment-form');
    var submitButton = document.getElementById('submit-button');
    
    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        const {paymentMethod, error} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        });
        
        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay Now';
        } else {
            // Send payment method to server
            fetch('{{ route("client.appointments.payment.process", $appointment) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    payment_method: paymentMethod.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("client.appointments.show", $appointment) }}';
                } else if (data.requires_action) {
                    stripe.confirmCardPayment(data.client_secret).then(function(result) {
                        if (result.error) {
                            document.getElementById('card-errors').textContent = result.error.message;
                            submitButton.disabled = false;
                            submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay Now';
                        } else {
                            window.location.href = '{{ route("client.appointments.show", $appointment) }}';
                        }
                    });
                } else {
                    document.getElementById('card-errors').textContent = data.error || 'Payment failed. Please try again.';
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay Now';
                }
            })
            .catch(error => {
                document.getElementById('card-errors').textContent = 'An error occurred. Please try again.';
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>Pay Now';
            });
        }
    });
</script>
@endpush