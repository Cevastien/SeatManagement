<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:queue_customers,id',
            ],
            'priority_type' => [
                'required',
                'in:senior,pwd,pregnant',
            ],
            'verification_method' => [
                'nullable',
                'in:id_scan,staff_visual,self_declaration',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer ID is required',
            'customer_id.exists' => 'Customer not found',
            'priority_type.required' => 'Priority type is required',
            'priority_type.in' => 'Invalid priority type',
        ];
    }
}
