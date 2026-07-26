<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255'],
            'objective' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'channel' => ['required', 'string', 'max:255'],
            'audience' => ['required', 'string', 'max:255'],
            'subject_line' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'media' => ['nullable', 'file', 'max:10240'],
            'send_date' => ['required', 'date'],
            'send_time' => ['nullable'],
            'end_date' => ['nullable', 'date'],
            'repeat_campaign' => ['nullable', 'string', 'max:255'],
            'send_option' => ['required', 'in:immediate,schedule'],
            'notes' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,scheduled'],
        ];
    }
}