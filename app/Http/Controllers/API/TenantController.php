<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Requests\TenantStoreRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    public function index(): JsonResponse
    {
        $tenants = Tenant::with(['lease', 'property', 'representativeLegalEntities', 'bankDetails'])->get();
        return response()->json($tenants, 200);
    }

    public function store(TenantStoreRequest $request): JsonResponse
    {
        $tenant = Tenant::create($request->only([
            'lease_id',
            'type',
            'property_id',
            'property_type',
            'salutation',
            'company_name',
            'last_name',
            'first_name',
            'email',
            'phone',
            'date_of_birth',
            'place_of_birth',
            'address',
            'additional_address',
            'postal_code',
            'city',
            'country',
            'owner_siret_siren_number',
            'website',
            'notes',
        ]));

        // Create related representative legal entities
        if ($request->has('representative_legal_entities')) {
            foreach ($request->representative_legal_entities as $rep) {
                $tenant->representativeLegalEntities()->create($rep);
            }
        }

        // Create related bank details
        if ($request->has('bank_details')) {
            foreach ($request->bank_details as $bankDetail) {
                $tenant->bankDetails()->create($bankDetail);
            }
        }

        return response()->json($tenant->load(['lease', 'property', 'representativeLegalEntities', 'bankDetails']), 201);
    }

    public function show($id): JsonResponse
    {
        $tenant = Tenant::with(['lease', 'property', 'representativeLegalEntities', 'bankDetails'])->findOrFail($id);
        return response()->json($tenant, 200);
    }

    public function update(TenantStoreRequest $request, $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($request->only([
            'lease_id',
            'type',
            'property_id',
            'property_type',
            'salutation',
            'company_name',
            'last_name',
            'first_name',
            'email',
            'phone',
            'date_of_birth',
            'place_of_birth',
            'address',
            'additional_address',
            'postal_code',
            'city',
            'country',
            'owner_siret_siren_number',
            'website',
            'notes',
        ]));

        // Update or create representative legal entities
        if ($request->has('representative_legal_entities')) {
            $tenant->representativeLegalEntities()->delete();
            foreach ($request->representative_legal_entities as $rep) {
                $tenant->representativeLegalEntities()->create($rep);
            }
        }

        // Update or create bank details
        if ($request->has('bank_details')) {
            $tenant->bankDetails()->delete();
            foreach ($request->bank_details as $bankDetail) {
                $tenant->bankDetails()->create($bankDetail);
            }
        }

        return response()->json($tenant->load(['lease', 'property', 'representativeLegalEntities', 'bankDetails']), 200);
    }

    public function destroy($id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete();
        return response()->json(null, 204);
    }
}
