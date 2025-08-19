<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaseTenantStoreRequest;
use App\Models\Lease;
use App\Models\Tenant;
use App\Trait\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaseTenantController extends Controller
{
    use ResponseTrait;

    public function store(LeaseTenantStoreRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $leases = collect();
                $tenants = collect();

                // Handle existing leases
                if ($request->has('existing_leases')) {
                    $leases = Lease::whereIn('id', $request->input('existing_leases'))->get();
                }

                // Create new lease
                if ($request->has('lease')) {
                    $leaseData = $request->input('lease');
                    $lease = Lease::create([
                        'entity_id' => $leaseData['entity_id'],
                        'contact_id' => $leaseData['contact_id'] ?? null,
                    ]);

                    // Create related lease records
                    $this->createOrUpdateRelatedLeaseRecords($lease, $leaseData, $request);

                    // Attach properties
                    if ($request->has('properties')) {
                        foreach ($request->input('properties', []) as $property) {
                            if ($property['type'] === 'App\Models\Building') {
                                $lease->buildings()->attach($property['id'], ['property_type' => $property['type']]);
                            } elseif ($property['type'] === 'App\Models\Unit') {
                                $lease->units()->attach($property['id'], ['property_type' => $property['type']]);
                            }
                        }
                    }

                    $leases->push($lease);
                }

                // Handle existing tenants
                if ($request->has('existing_tenants')) {
                    $tenants = Tenant::whereIn('id', $request->input('existing_tenants'))->get();
                }

                // Create new tenants
                if ($request->has('tenants')) {
                    foreach ($request->input('tenants', []) as $tenantData) {
                        $tenant = Tenant::create(array_filter([
                            'entity_id' => $tenantData['entity_id'],
                            'type' => $tenantData['type'],
                            'category' => $tenantData['category'] ?? null,
                            'salutation' => $tenantData['salutation'] ?? null,
                            'company_name' => $tenantData['company_name'] ?? null,
                            'legal_status' => $tenantData['legal_status'] ?? null,
                            'last_name' => $tenantData['last_name'],
                            'first_name' => $tenantData['first_name'],
                            'email' => $tenantData['email'],
                            'phone' => $tenantData['phone'],
                            'date_of_birth' => $tenantData['date_of_birth'],
                            'place_of_birth' => $tenantData['place_of_birth'],
                            'address' => $tenantData['address'] ?? null,
                            'additional_address' => $tenantData['additional_address'] ?? null,
                            'postal_code' => $tenantData['postal_code'] ?? null,
                            'city' => $tenantData['city'],
                            'country' => $tenantData['country'],
                            'notes' => $tenantData['notes'] ?? null,
                        ]));

                        // Create related representative legal entities
                        if (isset($tenantData['representative_legal_entities'])) {
                            foreach ($tenantData['representative_legal_entities'] as $rep) {
                                $tenant->representativeLegalEntities()->create(array_filter([
                                    'salutation' => $rep['salutation'] ?? null,
                                    'first_name' => $rep['first_name'],
                                    'last_name' => $rep['last_name'],
                                    'quality' => $rep['quality'],
                                    'date_of_birth' => $rep['date_of_birth'],
                                    'place_of_birth' => $rep['place_of_birth'],
                                    'address' => $rep['address'] ?? null,
                                    'additional_address' => $rep['additional_address'] ?? null,
                                    'postal_code' => $rep['postal_code'] ?? null,
                                    'city' => $rep['city'],
                                    'country' => $rep['country'],
                                    'email' => $rep['email'] ?? null,
                                    'phone' => $rep['phone'],
                                    'siret_siren_number' => $rep['siret_siren_number'] ?? null,
                                    'website' => $rep['website'] ?? null,
                                ]));
                            }
                        }

                        // Create related bank details
                        if (isset($tenantData['bank_details'])) {
                            foreach ($tenantData['bank_details'] as $bankDetail) {
                                $tenant->bankDetails()->create(array_filter([
                                    'bank_name' => $bankDetail['bank_name'],
                                    'rib_iban' => $bankDetail['rib_iban'],
                                    'bic_swift' => $bankDetail['bic_swift'],
                                    'address' => $bankDetail['address'] ?? null,
                                    'additional_address' => $bankDetail['additional_address'] ?? null,
                                    'postal_code' => $bankDetail['postal_code'] ?? null,
                                    'city' => $bankDetail['city'],
                                    'country' => $bankDetail['country'],
                                ]));
                            }
                        }

                        $tenants->push($tenant);
                    }
                }

                // Attach tenants to leases
                foreach ($leases as $lease) {
                    foreach ($tenants as $tenant) {
                        $lease->tenants()->syncWithoutDetaching([$tenant->id]);
                    }
                }

                // Load relationships for response
                $leases->each->load([
                    'tenants',
                    'buildings',
                    'units',
                    'leaseTermEffectiveDates',
                    'leaseFinancialRentConditions',
                    'leaseFinancialServiceChargesConditions',
                    'leaseRentRevisionConditions',
                    'leaseSpecificClauses',
                    'leaseDocuments',
                    'leaseZekeezAutomations',
                    'leaseEndDetails',
                ]);

                $tenants->each->load([
                    'leases',
                    'representativeLegalEntities',
                    'bankDetails',
                ]);

                return $this->sendResponse(
                    [
                        'leases' => $leases,
                        'tenants' => $tenants,
                    ],
                    'Lease(s) and/or tenant(s) processed successfully',
                    null,
                    201
                );
            });
        } catch (\Exception $e) {
            return $this->sendError(
                'Failed to process lease/tenant',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    protected function createOrUpdateRelatedLeaseRecords(Lease $lease, array $leaseData, LeaseTenantStoreRequest $request): void
    {
        if (isset($leaseData['lease_term_effective_dates'])) {
            $lease->leaseTermEffectiveDates()->create($leaseData['lease_term_effective_dates']);
        }
        if (isset($leaseData['lease_financial_rent_conditions'])) {
            $lease->leaseFinancialRentConditions()->create($leaseData['lease_financial_rent_conditions']);
        }
        if (isset($leaseData['lease_financial_service_charges_conditions'])) {
            $lease->leaseFinancialServiceChargesConditions()->create($leaseData['lease_financial_service_charges_conditions']);
        }
        if (isset($leaseData['lease_rent_revision_conditions'])) {
            $lease->leaseRentRevisionConditions()->create($leaseData['lease_rent_revision_conditions']);
        }
        if (isset($leaseData['lease_specific_clauses'])) {
            $lease->leaseSpecificClauses()->create($leaseData['lease_specific_clauses']);
        }
        if (isset($leaseData['lease_documents'])) {
            $documentPaths = [];

            // Handle single file uploads
            $singleFileFields = [
                'inventory_of_premises_annex',
                'technical_diagnostics_ddt_annex',
                'inventory_of_furnishings_annex',
                'co_ownership_regulations_annex',
                'landlord_bank_details_annex',
                'student_mobility_lease_justification_annex',
            ];

            foreach ($singleFileFields as $field) {
                if ($request->hasFile("lease.lease_documents.{$field}")) {
                    $file = $request->file("lease.lease_documents.{$field}");
                    $fileName = time() . '_' . Str::slug($field) . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('uploads/lease_documents', $fileName, 'public');
                    if (!$filePath) {
                        throw new \Exception("Failed to store file for {$field}");
                    }
                    $documentPaths[$field] = $filePath;
                } else {
                    $documentPaths[$field] = null;
                }
            }

            // Handle multiple file uploads for other_documents
            if ($request->hasFile('lease.lease_documents.other_documents')) {
                $otherDocumentPaths = [];
                foreach ($request->file('lease.lease_documents.other_documents') as $index => $file) {
                    $fileName = time() . '_other_document_' . $index . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('uploads/lease_documents', $fileName, 'public');
                    if (!$filePath) {
                        throw new \Exception("Failed to store file for other_documents[{$index}]");
                    }
                    $otherDocumentPaths[] = $filePath;
                }
                $documentPaths['other_documents'] = json_encode($otherDocumentPaths);
            } else {
                $documentPaths['other_documents'] = null;
            }

            $lease->leaseDocuments()->create($documentPaths);
        }
        if (isset($leaseData['lease_zekeez_automations'])) {
            $lease->leaseZekeezAutomations()->create($leaseData['lease_zekeez_automations']);
        }
        if (isset($leaseData['lease_end_details'])) {
            $lease->leaseEndDetails()->create($leaseData['lease_end_details']);
        }
    }
}
