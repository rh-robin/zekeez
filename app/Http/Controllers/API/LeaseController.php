<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests\LeaseStoreRequest;
use App\Models\Lease;
use Illuminate\Http\JsonResponse;

class LeaseController extends Controller
{
    public function index(): JsonResponse
    {
        $leases = Lease::with([
            'tenant',
            'property',
            'leaseTermEffectiveDates',
            'leaseFinancialRentConditions',
            'leaseFinancialServiceChargesConditions',
            'leaseRentRevisionConditions',
            'leaseSpecificClauses',
            'leaseDocuments',
            'leaseZekeezAutomations',
            'leaseEndDetails'
        ])->get();
        return response()->json($leases, 200);
    }

    public function store(LeaseStoreRequest $request): JsonResponse
    {
        $lease = Lease::create($request->only([
            'tenant_id',
            'property_id',
            'property_type',
            'guarantor',
        ]));

        // Create related records
        $lease->leaseTermEffectiveDates()->create($request->only([
            'lease_type',
            'furnished_lease_term_type',
            'furnished_lease_duration',
            'unfurnished_lease_term_type',
            'unfurnished_lease_duration',
            'commercial_lease_term_type',
            'commercial_lease_duration',
            'professional_lease_term_type',
            'professional_lease_duration',
            'other_lease_term_type',
            'lease_signing_date',
            'lease_effective_date',
            'lease_renewal_conditions_type',
            'other_lease_renewal_conditions',
        ]));

        $lease->leaseFinancialRentConditions()->create($request->only([
            'rent_amount',
            'rent_payment_due_date',
            'rent_payment_frequency',
            'preferred_rent_payment_method',
            'other_accepted_payment_methods',
        ]));

        $lease->leaseFinancialServiceChargesConditions()->create($request->only([
            'type_of_service_charges',
            'monthly_flat_rate_amount',
            'fixed_charges_included',
            'monthly_provision_actual_charges',
            'types_of_actual_charges',
            'procedures_regularization_actual_charges',
            'distribution_charges_co_tenants',
            'property_tax_allocation',
            'property_tax_allocation_other',
            'co_ownership_charges_allocation',
            'co_ownership_charges_allocation_other',
            'insurance_allocation',
            'insurance_allocation_other',
            'maintenance_repairs_allocation',
            'maintenance_repairs_allocation_other',
            'taxes_fees_allocation',
            'taxes_fees_allocation_other',
            'amount_of_security_deposit',
        ]));

        $lease->leaseRentRevisionConditions()->create($request->only([
            'frequency_of_rent_revision',
            'date_of_last_rent_revision',
            'reference_index',
            'other_index_to_specify',
            'index_reference_quarter',
            'index_reference_year',
            'reference_index_value',
            'revision_formula',
        ]));

        $lease->leaseSpecificClauses()->create($request->only([
            'joint_several_liability_clause',
            'termination_clause',
            'termination_clause_grounds',
            'destination_clause_type_of_use',
            'key_money_right_to_lease',
            'key_money_amount',
            'key_money_legal_qualification',
            'right_to_lease_existence',
            'right_to_lease_conditions_assignment',
            'right_to_lease_value',
        ]));

        $lease->leaseDocuments()->create($request->only([
            'inventory_of_premises_annex',
            'technical_diagnostics_ddt_annex',
            'inventory_of_furnishings_annex',
            'co_ownership_regulations_annex',
            'landlord_bank_details_annex',
            'student_mobility_lease_justification_annex',
            'other_documents',
        ]));

        $lease->leaseZekeezAutomations()->create($request->only([
            'generate_rents_from',
            'has_tenant_balance',
            'tenant_balance',
            'automatic_rent_revision',
            'automatic_rent_receipt_sending',
            'automatic_rent_call_sending',
            'rent_call_sending_date',
            'automatic_first_reminder_unpaid_rent',
            'first_unpaid_rent_reminder_sending_date',
            'automatic_second_reminder_unpaid_rent',
            'second_unpaid_rent_reminder_sending_date',
            'automatic_third_reminder_unpaid_rent',
            'third_unpaid_rent_reminder_sending_date',
        ]));

        $lease->leaseEndDetails()->create($request->only([
            'departure_date_of_the_tenant',
            'deposit_to_be_returned',
            'date_of_return_of_the_security_deposit',
        ]));

        return response()->json($lease->load([
            'tenant',
            'property',
            'leaseTermEffectiveDates',
            'leaseFinancialRentConditions',
            'leaseFinancialServiceChargesConditions',
            'leaseRentRevisionConditions',
            'leaseSpecificClauses',
            'leaseDocuments',
            'leaseZekeezAutomations',
            'leaseEndDetails'
        ]), 201);
    }

    public function show($id): JsonResponse
    {
        $lease = Lease::with([
            'tenant',
            'property',
            'leaseTermEffectiveDates',
            'leaseFinancialRentConditions',
            'leaseFinancialServiceChargesConditions',
            'leaseRentRevisionConditions',
            'leaseSpecificClauses',
            'leaseDocuments',
            'leaseZekeezAutomations',
            'leaseEndDetails'
        ])->findOrFail($id);
        return response()->json($lease, 200);
    }

    public function update(LeaseStoreRequest $request, $id): JsonResponse
    {
        $lease = Lease::findOrFail($id);
        $lease->update($request->only([
            'tenant_id',
            'property_id',
            'property_type',
            'guarantor',
        ]));

        // Update related records
        $lease->leaseTermEffectiveDates()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'lease_type',
                'furnished_lease_term_type',
                'furnished_lease_duration',
                'unfurnished_lease_term_type',
                'unfurnished_lease_duration',
                'commercial_lease_term_type',
                'commercial_lease_duration',
                'professional_lease_term_type',
                'professional_lease_duration',
                'other_lease_term_type',
                'lease_signing_date',
                'lease_effective_date',
                'lease_renewal_conditions_type',
                'other_lease_renewal_conditions',
            ])
        );

        $lease->leaseFinancialRentConditions()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'rent_amount',
                'rent_payment_due_date',
                'rent_payment_frequency',
                'preferred_rent_payment_method',
                'other_accepted_payment_methods',
            ])
        );

        $lease->leaseFinancialServiceChargesConditions()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'type_of_service_charges',
                'monthly_flat_rate_amount',
                'fixed_charges_included',
                'monthly_provision_actual_charges',
                'types_of_actual_charges',
                'procedures_regularization_actual_charges',
                'distribution_charges_co_tenants',
                'property_tax_allocation',
                'property_tax_allocation_other',
                'co_ownership_charges_allocation',
                'co_ownership_charges_allocation_other',
                'insurance_allocation',
                'insurance_allocation_other',
                'maintenance_repairs_allocation',
                'maintenance_repairs_allocation_other',
                'taxes_fees_allocation',
                'taxes_fees_allocation_other',
                'amount_of_security_deposit',
            ])
        );

        $lease->leaseRentRevisionConditions()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'frequency_of_rent_revision',
                'date_of_last_rent_revision',
                'reference_index',
                'other_index_to_specify',
                'index_reference_quarter',
                'index_reference_year',
                'reference_index_value',
                'revision_formula',
            ])
        );

        $lease->leaseSpecificClauses()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'joint_several_liability_clause',
                'termination_clause',
                'termination_clause_grounds',
                'destination_clause_type_of_use',
                'key_money_right_to_lease',
                'key_money_amount',
                'key_money_legal_qualification',
                'right_to_lease_existence',
                'right_to_lease_conditions_assignment',
                'right_to_lease_value',
            ])
        );

        $lease->leaseDocuments()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'inventory_of_premises_annex',
                'technical_diagnostics_ddt_annex',
                'inventory_of_furnishings_annex',
                'co_ownership_regulations_annex',
                'landlord_bank_details_annex',
                'student_mobility_lease_justification_annex',
                'other_documents',
            ])
        );

        $lease->leaseZekeezAutomations()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'generate_rents_from',
                'has_tenant_balance',
                'tenant_balance',
                'automatic_rent_revision',
                'automatic_rent_receipt_sending',
                'automatic_rent_call_sending',
                'rent_call_sending_date',
                'automatic_first_reminder_unpaid_rent',
                'first_unpaid_rent_reminder_sending_date',
                'automatic_second_reminder_unpaid_rent',
                'second_unpaid_rent_reminder_sending_date',
                'automatic_third_reminder_unpaid_rent',
                'third_unpaid_rent_reminder_sending_date',
            ])
        );

        $lease->leaseEndDetails()->updateOrCreate(
            ['lease_id' => $lease->id],
            $request->only([
                'departure_date_of_the_tenant',
                'deposit_to_be_returned',
                'date_of_return_of_the_security_deposit',
            ])
        );

        return response()->json($lease->load([
            'tenant',
            'property',
            'leaseTermEffectiveDates',
            'leaseFinancialRentConditions',
            'leaseFinancialServiceChargesConditions',
            'leaseRentRevisionConditions',
            'leaseSpecificClauses',
            'leaseDocuments',
            'leaseZekeezAutomations',
            'leaseEndDetails'
        ]), 200);
    }

    public function destroy($id): JsonResponse
    {
        $lease = Lease::findOrFail($id);
        $lease->delete();
        return response()->json(null, 204);
    }
}
