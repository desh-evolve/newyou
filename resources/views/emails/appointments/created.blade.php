{{-- resources/views/emails/appointments/created.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .details { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Appointment Booked!</h1>
        </div>
        <div class="content">
            <p>Dear {{ $appointment->client->full_name }},</p>
            <p>Your appointment has been successfully booked. Here are the details:</p>
            
            <div class="details">
                <p><strong>Appointment #:</strong> {{ $appointment->appointment_number }}</p>
                <p><strong>Date:</strong> {{ $appointment->formatted_date }}</p>
                <p><strong>Time:</strong> {{ $appointment->formatted_time }}</p>
                <p><strong>Coach:</strong> {{ $appointment->coach->name }}</p>
                <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</p>
                <p><strong>Amount:</strong> ${{ number_format($appointment->final_amount, 2) }}</p>
            </div>
            
            @if($appointment->payment_status === 'pending')
                <p><strong>Note:</strong> Please complete your payment to confirm the appointment.</p>
                <p style="text-align: center;">
                    <a href="{{ route('client.appointments.payment', $appointment) }}" class="btn">Pay Now</a>
                </p>
            @endif
            
            <p>Thank you for booking with us!</p>
        </div>
        <div class="footer">
            <p>If you have any questions, please contact us at support@example.com</p>
        </div>
    </div>
</body>
</html>