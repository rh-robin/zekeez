<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Tenant
            'lease_id' => 'nullable|exists:leases,id',
            'type' => 'required|in:individual,legal_entity',
            'property_id' => 'required',
            'property_type' => 'required|string',
            'salutation' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'place_of_birth' => 'required|string|max:255',
            'address' => 'nullable|string',
            'additional_address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'owner_siret_siren_number' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            // Tenant Representative Legal Entities
            'representative_legal_entities.*.salutation' => 'nullable|string|max:255',
            'representative_legal_entities.*.name' => 'required|string|max:255',
            'representative_legal_entities.*.first_name' => 'required|string|max:255',
            'representative_legal_entities.*.quality' => 'required|string|max:255',
            'representative_legal_entities.*.date_of_birth' => 'required|date',
            'representative_legal_entities.*.place_of_birth' => 'required|string|max:255',
            'representative_legal_entities.*.address' => 'nullable|string',
            'representative_legal_entities.*.additional_address' => 'nullable|string',
            'representative_legal_entities.*.postal_code' => 'nullable|string|max:255',
            'representative_legal_entities.*.city' => 'required|string|max:255',
            'representative_legal_entities.*.country' => 'required|string|max:255',
            'representative_legal_entities.*.phone' => 'required|string|max:255',

            // Tenant Bank Details
            'bank_details.*.salutation' => 'nullable|string|max:255',
            'bank_details.*.name' => 'required|string|max:255',
            'bank_details.*.first_name' => 'required|string|max:255',
            'bank_details.*.quality' => 'required|string|max:255',
            'bank_details.*.date_of_birth' => 'required|date',
            'bank_details.*.place_of_birth' => 'required|string|max:255',
            'bank_details.*.address' => 'nullable|string',
            'bank_details.*.additional_address' => 'nullable|string',
            'bank_details.*.postal_code' => 'nullable|string|max:255',
            'bank_details.*.city' => 'required|string|max:255',
            'bank_details.*.country' => 'required|string|max:255',
            'bank_details.*.phone' => 'required|string|max:255',
        ];
    }
}