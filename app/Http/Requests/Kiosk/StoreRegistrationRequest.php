<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-zA-Z\s\-\'.]+$/',
            ],
            'party_size' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],
            'contact' => [
                'nullable',
                'string',
                'regex:/^[0-9]{9}$/',
            ],
            'is_priority' => [
                'required',
                'in:0,1',
            ],
            'priority_type' => [
                'required_if:is_priority,1',
                'nullable',
                'in:normal,senior,pwd,pregnant',
            ],
            'is_edit_mode' => [
                'nullable',
                'in:0,1',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 2 characters',
            'name.max' => 'Name cannot exceed 50 characters',
            'name.regex' => 'Name can only contain letters, spaces, hyphens, apostrophes, and periods',

            'party_size.required' => 'Party size is required',
            'party_size.integer' => 'Party size must be a number',
            'party_size.min' => 'Party size must be at least 1 person',
            'party_size.max' => 'Party size cannot exceed 20 people',

            'contact.regex' => 'Contact number must be exactly 9 digits',

            'is_priority.required' => 'Please answer the priority question',
            'is_priority.in' => 'Invalid priority selection',

            'priority_type.required_if' => 'Please select a priority type',
            'priority_type.in' => 'Invalid priority type selected',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Strip '09' prefix from contact if present
        if ($this->has('contact') && $this->contact) {
            $contact = preg_replace('/\D/', '', $this->contact);

            if (strlen($contact) === 11 && str_starts_with($contact, '09')) {
                $contact = substr($contact, 2);
            }

            $this->merge([
                'contact' => $contact,
            ]);
        }

        // Set default priority_type if not priority
        if ($this->is_priority == '0' && !$this->has('priority_type')) {
            $this->merge([
                'priority_type' => 'normal',
            ]);
        }
    }

    /**
     * Get the validated data with contact number formatted.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        // Add '09' prefix to contact number if provided
        if (!empty($validated['contact'])) {
            $validated['contact_number'] = '09' . $validated['contact'];
        }

        return $validated;
    }
}
