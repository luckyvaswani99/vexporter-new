<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->vendor()->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            // Step 1 — company
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2'],

            // Step 2 — statutory
            'gst_number' => ['required', 'string', 'max:20'],
            'pan' => ['required', 'string', 'max:12'],
            'iec_code' => ['required', 'string', 'max:20'],
            'cin' => ['nullable', 'string', 'max:32'],

            // Step 3 — what they sell
            'verticals' => ['required', 'array', 'min:1'],
            'verticals.*' => ['integer', Rule::exists('verticals', 'id')],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', Rule::exists('categories', 'id')],

            // Step 4 — certifications & documents
            'documents' => ['nullable', 'array'],
            'documents.*.label' => ['required_with:documents.*.file', 'string', 'max:120'],
            'documents.*.number' => ['nullable', 'string', 'max:120'],
            'documents.*.expires_at' => ['nullable', 'date', 'after:today'],
            'documents.*.file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            // Step 5 — payout
            'account_holder' => ['required', 'string', 'max:255'],
            'account_no' => ['required', 'string', 'max:40'],
            'ifsc' => ['required_if:country_code,IN', 'nullable', 'string', 'max:20'],
            'swift' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'payout_currency' => ['required', 'string', 'size:3'],

            'declaration' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'verticals.required' => 'Select at least one vertical you supply.',
            'declaration.accepted' => 'You must confirm the declaration before submitting.',
            'ifsc.required_if' => 'IFSC code is required for Indian bank accounts.',
        ];
    }
}
