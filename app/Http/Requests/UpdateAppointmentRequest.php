<?php
// app/Http/Requests/UpdateAppointmentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_type' => 'sometimes|required|in:in_person,video_call,phone_call',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_location' => 'nullable|string|max:500',
            'admin_notes' => 'nullable|string|max:1000',
            'appointment_status' => 'sometimes|required|in:pending,confirmed,in_progress,completed,cancelled,no_show,rescheduled',
        ];
    }
}