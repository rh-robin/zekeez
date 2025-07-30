<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeaseTenantStoreRequest;
use App\Models\Lease;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LeaseTenantController extends Controller
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

        $tenants = Tenant::with(['lease', 'property', 'representativeLegalEntities', 'bankDetails'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Retrieved all leases and tenants',
            'data' => [
                'leases' => $leases,
                'tenants' => $tenants
            ]
        ], 200);
    }

    public function store(LeaseTenantStoreRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $tenant = null;
            $lease = null;

            // Create Lease (required)
            if ($request->has('lease')) {
                $leaseData = $request->input('lease');
                $lease = Lease::create($leaseData);
                $this->createOrUpdateRelatedLeaseRecords($lease, $leaseData);
            }

            // Create Tenant if provided (optional)
            if ($request->has('tenant')) {
                $tenantData = $request->input('tenant');
                $tenantData['lease_id'] = $lease->id; // Link tenant to lease
                $tenant = Tenant::create($tenantData);

                // Create related representative legal entities
                if (isset($tenantData['representative_legal_entities'])) {
                    foreach ($tenantData['representative_legal_entities'] as $rep) {
                        $tenant->representativeLegalEntities()->create($rep);
                    }
                }

                // Create related bank details
                if (isset($tenantData['bank_details'])) {
                    foreach ($tenantData['bank_details'] as $bankDetail) {
                        $tenant->bankDetails()->create($bankDetail);
                    }
                }

                // Update lease with tenant_id
                $lease->tenant_id = $tenant->id;
                $lease->save();
            }

            // Load relationships for response
            if ($lease) {
                $lease->load([
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
                ]);
            }
            if ($tenant) {
                $tenant->load(['lease', 'property', 'representativeLegalEntities', 'bankDetails']);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lease and/or tenant created successfully',
                'data' => [
                    'lease' => $lease,
                    'tenant' => $tenant
                ]
            ], 201);
        });
    }

    public function show($id, $type = 'lease'): JsonResponse
    {
        $data = null;

        if ($type === 'lease') {
            $data = Lease::with([
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
            ])->find($id);
        } elseif ($type === 'tenant') {
            $data = Tenant::with(['lease', 'property', 'representativeLegalEntities', 'bankDetails'])->find($id);
        }

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => ucfirst($type) . ' not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($type) . ' retrieved successfully',
            'data' => [
                $type => $data
            ]
        ], 200);
    }

    public function update(LeaseTenantStoreRequest $request, $id, $type = 'lease'): JsonResponse
    {
        return DB::transaction(function () use ($request, $id, $type) {
            $lease = null;
            $tenant = null;

            if ($type === 'lease') {
                $lease = Lease::findOrFail($id);
                if ($request->has('lease')) {
                    $leaseData = $request->input('lease');
                    $lease->update($leaseData);
                    $this->createOrUpdateRelatedLeaseRecords($lease, $leaseData);
                }

                if ($request->has('tenant')) {
                    $tenantData = $request->input('tenant');
                    $tenantData['lease_id'] = $lease->id;
                    $tenant = Tenant::updateOrCreate(
                        ['lease_id' => $lease->id],
                        $tenantData
                    );
                    $lease->tenant_id = $tenant->id;
                    $lease->save();

                    // Update tenant-related records
                    if (isset($tenantData['representative_legal_entities'])) {
                        $tenant->representativeLegalEntities()->delete();
                        foreach ($tenantData['representative_legal_entities'] as $rep) {
                            $tenant->representativeLegalEntities()->create($rep);
                        }
                    }
                    if (isset($tenantData['bank_details'])) {
                        $tenant->bankDetails()->delete();
                        foreach ($tenantData['bank_details'] as $bankDetail) {
                            $tenant->bankDetails()->create($bankDetail);
                        }
                    }
                }
            } elseif ($type === 'tenant') {
                $tenant = Tenant::findOrFail($id);
                if ($request->has('tenant')) {
                    $tenantData = $request->input('tenant');
                    $tenant->update($tenantData);

                    // Update tenant-related records
                    if (isset($tenantData['representative_legal_entities'])) {
                        $tenant->representativeLegalEntities()->delete();
                        foreach ($tenantData['representative_legal_entities'] as $rep) {
                            $tenant->representativeLegalEntities()->create($rep);
                        }
                    }
                    if (isset($tenantData['bank_details'])) {
                        $tenant->bankDetails()->delete();
                        foreach ($tenantData['bank_details'] as $bankDetail) {
                            $tenant->bankDetails()->create($bankDetail);
                        }
                    }
                }

                if ($request->has('lease') && $tenant->lease_id) {
                    $leaseData = $request->input('lease');
                    $lease = Lease::findOrFail($tenant->lease_id);
                    $lease->update($leaseData);
                    $this->createOrUpdateRelatedLeaseRecords($lease, $leaseData);
                }
            }

            // Load relationships for response
            if ($lease) {
                $lease->load([
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
                ]);
            }
            if ($tenant) {
                $tenant->load(['lease', 'property', 'representativeLegalEntities', 'bankDetails']);
            }

            return response()->json([
                'status' => 'success',
                'message' => ucfirst($type) . ' updated successfully',
                'data' => [
                    'lease' => $lease,
                    'tenant' => $tenant
                ]
            ], 200);
        });
    }

    public function destroy($id, $type = 'lease'): JsonResponse
    {
        $data = null;

        if ($type === 'lease') {
            $data = Lease::find($id);
            if ($data && $data->tenant) {
                $data->tenant()->delete(); // Delete associated tenant
            }
        } elseif ($type === 'tenant') {
            $data = Tenant::find($id);
        }

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => ucfirst($type) . ' not found',
                'data' => null
            ], 404);
        }

        $data->delete();

        return response()->json([
            'status' => 'success',
            'message' => ucfirst($type) . ' deleted successfully',
            'data' => null
        ], 204);
    }

    private function createOrUpdateRelatedLeaseRecords($lease, $leaseData)
    {
        if (isset($leaseData['lease_term_effective_dates'])) {
            $lease->leaseTermEffectiveDates()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_term_effective_dates']
            );
        }
        if (isset($leaseData['lease_financial_rent_conditions'])) {
            $lease->leaseFinancialRentConditions()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_financial_rent_conditions']
            );
        }
        if (isset($leaseData['lease_financial_service_charges_conditions'])) {
            $lease->leaseFinancialServiceChargesConditions()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_financial_service_charges_conditions']
            );
        }
        if (isset($leaseData['lease_rent_revision_conditions'])) {
            $lease->leaseRentRevisionConditions()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_rent_revision_conditions']
            );
        }
        if (isset($leaseData['lease_specific_clauses'])) {
            $lease->leaseSpecificClauses()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_specific_clauses']
            );
        }
        if (isset($leaseData['lease_documents'])) {
            $lease->leaseDocuments()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_documents']
            );
        }
        if (isset($leaseData['lease_zekeez_automations'])) {
            $lease->leaseZekeezAutomations()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_zekeez_automations']
            );
        }
        if (isset($leaseData['lease_end_details'])) {
            $lease->leaseEndDetails()->updateOrCreate(
                ['lease_id' => $lease->id],
                $leaseData['lease_end_details']
            );
        }
    }
}