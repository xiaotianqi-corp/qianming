<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdentityRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return auth()->check(); 
    }

    public function rules(): array
    {
        return [
            'country_id'      => ['required', 'exists:countries,id'],
            'type'            => ['required', 'in:natural,legal_representative,company_member'],
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'document_type'   => ['required', 'string'],
            'document_number' => ['required', 'string', 'max:20'],
            'birth_date'      => ['nullable', 'date', 'before:today'],
            'email'           => ['required', 'email', 'max:255'],
            'phone'           => ['required', 'string', 'max:20'],
            'documents'       => ['nullable', 'array'],
            'documents.*'     => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'documents.*.mimes' => 'Only JPG, PNG or PDF files are allowed.',
            'type.in' => 'The selected holder type is not valid.',
        ];
    }
}