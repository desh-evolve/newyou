<?php
// app/Http/Requests/StoreAppointmentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'time_slot_id' => 'required|exists:time_slots,id',
            'client_user_id' => 'required|exists:users,id',
            'package_id' => 'nullable|exists:packages,id',
            'service_id' => 'nullable|exists:services,id',
            'appointment_type' => 'required|in:in_person,video_call,phone_call',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_location' => 'nullable|string|max:500',
            'client_notes' => 'nullable|string|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'time_slot_id.required' => 'Please select a time slot.',
            'time_slot_id.exists' => 'The selected time slot is invalid.',
            'client_user_id.required' => 'Please select a client.',
            'client_user_id.exists' => 'The selected client is invalid.',
            'appointment_type.required' => 'Please select an appointment type.',
            'appointment_type.in' => 'Invalid appointment type selected.',
        ];
    }
}