<?php

namespace App\Http\Requests;

use App\Rules\UniquePropertyIdType;
use Illuminate\Foundation\Http\FormRequest;

class LeaseTenantStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Create a new lease
            'lease' => 'sometimes|array',
            'lease.entity_id' => 'required_with:lease|exists:entities,id',
            'lease.contact_id' => 'nullable|exists:contacts,id',

            // Related lease tables
            'lease.lease_term_effective_dates' => 'sometimes|array',
            'lease.lease_term_effective_dates.lease_type' => 'nullable|string|max:255',
            'lease.lease_term_effective_dates.furnished_lease_term_type' => 'nullable|string|max:255',
            'lease.lease_term_effective_dates.mobility_lease_term' => 'nullable|integer|min:1',
            'lease.lease_term_effective_dates.unfurnished_lease_term' => 'nullable|integer|min:1',
            'lease.lease_term_effective_dates.commercial_lease_term' => 'nullable|integer|min:1',
            'lease.lease_term_effective_dates.professional_lease_term' => 'nullable|string|max:255',
            'lease.lease_term_effective_dates.parking_or_other_lease_term' => 'nullable|integer|min:1',
            'lease.lease_term_effective_dates.lease_signing_date' => 'nullable|date',
            'lease.lease_term_effective_dates.lease_effective_date' => 'nullable|date|after_or_equal:lease.lease_term_effective_dates.lease_signing_date',
            'lease.lease_term_effective_dates.lease_renewal_conditions' => 'nullable|string',
            'lease.lease_term_effective_dates.other_lease_renewal_conditions' => 'nullable|string',

            'lease.lease_financial_rent_conditions' => 'sometimes|array',
            'lease.lease_financial_rent_conditions.rent_amount' => 'nullable|numeric|min:0',
            'lease.lease_financial_rent_conditions.rent_payment_due_date' => 'nullable|date',
            'lease.lease_financial_rent_conditions.rent_payment_frequency' => 'nullable|in:monthly,quarterly,annually',
            'lease.lease_financial_rent_conditions.preferred_rent_payment_method' => 'nullable|in:bank_transfer,check,cash,direct_debit',
            'lease.lease_financial_rent_conditions.other_accepted_payment_method' => 'nullable|string',

            'lease.lease_financial_service_charges_conditions' => 'sometimes|array',
            'lease.lease_financial_service_charges_conditions.type_of_service_charges' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.monthly_flat_rate_amount' => 'nullable|numeric|min:0',
            'lease.lease_financial_service_charges_conditions.fixed_charges_included' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.monthly_provision_actual_charges' => 'nullable|numeric|min:0',
            'lease.lease_financial_service_charges_conditions.types_of_actual_charges' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.procedures_regularization_actual_charges' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.distribution_charges_co_tenants' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.property_tax_allocation' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.property_tax_allocation_other' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.co_ownership_charges_allocation' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.co_ownership_charges_allocation_other' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.insurance_allocation' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.insurance_allocation_other' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.maintenance_repairs_allocation' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.maintenance_repairs_allocation_other' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.taxes_fees_allocation' => 'nullable|string|max:255',
            'lease.lease_financial_service_charges_conditions.taxes_fees_allocation_other' => 'nullable|string',
            'lease.lease_financial_service_charges_conditions.amount_of_security_deposit' => 'nullable|string',

            'lease.lease_rent_revision_conditions' => 'sometimes|array',
            'lease.lease_rent_revision_conditions.frequency_of_rent_revision' => 'nullable|string|max:255',
            'lease.lease_rent_revision_conditions.date_of_last_rent_revision' => 'nullable|date',
            'lease.lease_rent_revision_conditions.reference_index' => 'nullable|string|max:255',
            'lease.lease_rent_revision_conditions.other_index_to_specify' => 'nullable|string',
            'lease.lease_rent_revision_conditions.index_reference_quarter' => 'nullable|string|max:255',
            'lease.lease_rent_revision_conditions.index_reference_year' => 'nullable|string|max:255',
            'lease.lease_rent_revision_conditions.reference_index_value' => 'nullable|string',
            'lease.lease_rent_revision_conditions.revision_formula' => 'nullable|string',

            'lease.lease_specific_clauses' => 'sometimes|array',
            'lease.lease_specific_clauses.joint_several_liability_clause' => 'nullable|boolean',
            'lease.lease_specific_clauses.termination_clause' => 'nullable|boolean',
            'lease.lease_specific_clauses.termination_clause_grounds' => 'nullable|string',
            'lease.lease_specific_clauses.destination_clause_type_of_use' => 'nullable|string|max:255',
            'lease.lease_specific_clauses.key_money_right_to_lease' => 'nullable|boolean',
            'lease.lease_specific_clauses.key_money_amount' => 'nullable|numeric|min:0',
            'lease.lease_specific_clauses.key_money_legal_qualification' => 'nullable|string|max:255',
            'lease.lease_specific_clauses.right_to_lease_existence' => 'nullable|string',
            'lease.lease_specific_clauses.right_to_lease_conditions_assignment' => 'nullable|string',
            'lease.lease_specific_clauses.right_to_lease_value' => 'nullable|numeric|min:0',

            'lease.lease_documents' => 'sometimes|array',
            'lease.lease_documents.inventory_of_premises_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.technical_diagnostics_ddt_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.inventory_of_furnishings_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.co_ownership_regulations_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.landlord_bank_details_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.student_mobility_lease_justification_annex' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'lease.lease_documents.other_documents' => 'nullable|array',
            'lease.lease_documents.other_documents.*' => 'file|mimes:pdf,doc,docx|max:10240',

            'lease.lease_zekeez_automations' => 'sometimes|array',
            'lease.lease_zekeez_automations.generate_rents_from' => 'nullable|date',
            'lease.lease_zekeez_automations.has_tenant_balance' => 'nullable|boolean',
            'lease.lease_zekeez_automations.tenant_balance' => 'nullable|numeric|min:0',
            'lease.lease_zekeez_automations.automatic_rent_revision' => 'nullable|boolean',
            'lease.lease_zekeez_automations.automatic_rent_receipt_sending' => 'nullable|boolean',
            'lease.lease_zekeez_automations.automatic_rent_call_sending' => 'nullable|boolean',
            'lease.lease_zekeez_automations.rent_call_sending_date' => 'nullable|date',
            'lease.lease_zekeez_automations.automatic_first_reminder_unpaid_rent' => 'nullable|boolean',
            'lease.lease_zekeez_automations.first_unpaid_rent_reminder_sending_date' => 'nullable|date|after_or_equal:lease.lease_zekeez_automations.rent_call_sending_date',
            'lease.lease_zekeez_automations.automatic_second_reminder_unpaid_rent' => 'nullable|boolean',
            'lease.lease_zekeez_automations.second_unpaid_rent_reminder_sending_date' => 'nullable|date|after_or_equal:lease.lease_zekeez_automations.first_unpaid_rent_reminder_sending_date',
            'lease.lease_zekeez_automations.automatic_third_reminder_unpaid_rent' => 'nullable|boolean',
            'lease.lease_zekeez_automations.third_unpaid_rent_reminder_sending_date' => 'nullable|date|after_or_equal:lease.lease_zekeez_automations.second_unpaid_rent_reminder_sending_date',

            'lease.lease_end_details' => 'sometimes|array',
            'lease.lease_end_details.departure_date_of_the_tenant' => 'nullable|date',
            'lease.lease_end_details.deposit_to_be_returned' => 'nullable|numeric|min:0',
            'lease.lease_end_details.date_of_return_of_the_security_deposit' => 'nullable|date|after_or_equal:lease.lease_end_details.departure_date_of_the_tenant',

            // Properties (buildings or units) to attach to the lease
            'properties' => 'sometimes|array',
            'properties.*.id' => [
                'required_with:properties',
                'exists_polymorphic',
                new UniquePropertyIdType($this->input('properties', [])),
            ],
            'properties.*.type' => 'required_with:properties|in:App\Models\Building,App\Models\Unit',

            // Create new tenant(s)
            'tenants' => 'sometimes|array',
            'tenants.*.entity_id' => 'required_with:tenants|exists:entities,id',
            'tenants.*.type' => 'required_with:tenants|in:individual,legal_entity',
            'tenants.*.category' => 'nullable|string|max:255',
            'tenants.*.salutation' => 'nullable|string|max:255',
            'tenants.*.company_name' => 'nullable|string|max:255',
            'tenants.*.legal_status' => 'nullable|string|max:255',
            'tenants.*.last_name' => 'required_with:tenants|string|max:255',
            'tenants.*.first_name' => 'required_with:tenants|string|max:255',
            'tenants.*.email' => 'required_with:tenants|email|max:255',
            'tenants.*.phone' => 'required_with:tenants|string|max:255',
            'tenants.*.date_of_birth' => 'required_with:tenants|date',
            'tenants.*.place_of_birth' => 'required_with:tenants|string|max:255',
            'tenants.*.address' => 'nullable|string',
            'tenants.*.additional_address' => 'nullable|string',
            'tenants.*.postal_code' => 'nullable|string|max:255',
            'tenants.*.city' => 'required_with:tenants|string|max:255',
            'tenants.*.country' => 'required_with:tenants|string|max:255',
            'tenants.*.notes' => 'nullable|string',

            'tenants.*.representative_legal_entities' => 'sometimes|array',
            'tenants.*.representative_legal_entities.*.salutation' => 'nullable|string|max:255',
            'tenants.*.representative_legal_entities.*.first_name' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.last_name' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.quality' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.date_of_birth' => 'required_with:tenants.*.representative_legal_entities|date',
            'tenants.*.representative_legal_entities.*.place_of_birth' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.address' => 'nullable|string',
            'tenants.*.representative_legal_entities.*.additional_address' => 'nullable|string',
            'tenants.*.representative_legal_entities.*.postal_code' => 'nullable|string|max:255',
            'tenants.*.representative_legal_entities.*.city' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.country' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.email' => 'nullable|email|max:255',
            'tenants.*.representative_legal_entities.*.phone' => 'required_with:tenants.*.representative_legal_entities|string|max:255',
            'tenants.*.representative_legal_entities.*.siret_siren_number' => 'nullable|string|max:255',
            'tenants.*.representative_legal_entities.*.website' => 'nullable|string|max:255',

            'tenants.*.bank_details' => 'sometimes|array',
            'tenants.*.bank_details.*.bank_name' => 'required_with:tenants.*.bank_details|string|max:255',
            'tenants.*.bank_details.*.rib_iban' => 'required_with:tenants.*.bank_details|string|max:255',
            'tenants.*.bank_details.*.bic_swift' => 'required_with:tenants.*.bank_details|string|max:255',
            'tenants.*.bank_details.*.address' => 'nullable|string',
            'tenants.*.bank_details.*.additional_address' => 'nullable|string',
            'tenants.*.bank_details.*.postal_code' => 'nullable|string|max:255',
            'tenants.*.bank_details.*.city' => 'required_with:tenants.*.bank_details|string|max:255',
            'tenants.*.bank_details.*.country' => 'required_with:tenants.*.bank_details|string|max:255',

            // Select existing tenant(s)
            'existing_tenants' => 'sometimes|array',
            'existing_tenants.*' => 'exists:tenants,id|distinct',

            // Select existing lease(s)
            'existing_leases' => 'sometimes|array',
            'existing_leases.*' => 'exists:leases,id|distinct',
        ];
    }
}
