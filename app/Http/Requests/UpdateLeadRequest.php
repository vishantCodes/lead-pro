<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('edit_leads');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'state' => 'sometimes|required|string|max:100',
            'source_type' => 'sometimes|required|in:global,online,offline',
            'status' => 'sometimes|required|in:new,contacted,qualified,converted,lost',
            'campaign_id' => 'sometimes|nullable|exists:campaigns,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ];
    }
}
