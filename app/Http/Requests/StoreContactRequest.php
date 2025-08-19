<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize()
    {
        return auth('api')->check();
    }

    public function rules()
    {
        return [
            'type' => 'required|in:individual,legal_entity',
            'category' => 'required|string',
            'salutation' => 'required_if:type,individual|nullable|string',
            'first_name' => 'required_if:type,individual|nullable|string',
            'last_name' => 'required_if:type,individual|nullable|string',
            'email' => 'required|email',
            'phone' => 'required|string|regex:/^\+?[0-9]\d{1,14}$/',
            'date_of_birth' => 'nullable|date|date_format:Y-m-d',
            'place_of_birth' => 'nullable|string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'additional_info' => 'nullable|string',
            'entity_ids' => 'nullable|array',
            'entity_ids.*' => 'exists:entities,id',
            'property_ids' => 'nullable|array',
            'property_ids.*.id' => 'required|integer', // Validate each property ID
            'property_ids.*.type' => 'required|in:App\Models\Building,App\Models\Unit', // Validate property type
            'legal_status' => 'required_if:type,legal_entity|nullable|string',
            'company_name' => 'required_if:type,legal_entity|nullable|string',
            'bank_name' => 'nullable|string',
            'rib_iban' => 'nullable|string',
            'bic_swift' => 'nullable|string',
            'bank_address_line_1' => 'nullable|string',
            'bank_address_line_2' => 'nullable|string',
            'bank_country' => 'nullable|string',
            'bank_city' => 'nullable|string',
            'bank_postal_code' => 'nullable|string',
            'r_salutation' => 'nullable|string',
            'r_first_name' => 'nullable|string',
            'r_last_name' => 'nullable|string',
            'quality' => 'nullable|string',
            'r_email' => 'required_if:type,legal_entity|nullable|email',
            'r_phone' => 'nullable|string|regex:/^\+?[0-9]\d{1,14}$/',
            'r_date_of_birth' => 'nullable|date|date_format:Y-m-d',
            'r_place_of_birth' => 'nullable|string',
            'r_address_line_1' => 'nullable|string',
            'r_address_line_2' => 'nullable|string',
            'r_country' => 'nullable|string',
            'r_city' => 'nullable|string',
            'r_postal_code' => 'nullable|string',
            'siren' => 'nullable|string',
            'website_url' => 'nullable|string|url',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'The contact type is required.',
            'type.in' => 'The contact type must be either individual or legal_entity.',
            'category.required' => 'The category is required.',
            'salutation.required_if' => 'Salutation is required for individual contacts.',
            'first_name.required_if' => 'First name is required for individual contacts.',
            'last_name.required_if' => 'Last name is required for individual contacts.',
            'email.required' => 'The email address is required.',
            'email.email' => 'The email must be a valid email address.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number format is invalid.',
            'date_of_birth.date_format' => 'The date of birth must be in Y-m-d format.',
            'entity_ids.array' => 'Entity IDs must be an array.',
            'entity_ids.*.exists' => 'One or more entity IDs are invalid.',
            'property_ids.array' => 'Property IDs must be an array.',
            'property_ids.*.id.required' => 'Each property must have an ID.',
            'property_ids.*.type.required' => 'Each property must have a type.',
            'property_ids.*.type.in' => 'Property type must be either App\Models\Building or App\Models\Unit.',
            'legal_status.required_if' => 'Legal status is required for legal entities.',
            'company_name.required_if' => 'Company name is required for legal entities.',
            'r_email.required_if' => 'Representative email is required for legal entities.',
            'r_phone.regex' => 'The representative phone number format is invalid.',
            'r_date_of_birth.date_format' => 'The representative date of birth must be in Y-m-d format.',
            'website_url.url' => 'The website URL must be a valid URL.',
        ];
    }
}
