<?php

namespace App\Http\Controllers\API\Property;

use App\Models\Building;
use App\Models\Unit;
use Exception;
use App\Models\Property;
use App\Trait\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;

class PropertyController extends Controller
{
    use ResponseTrait;
    public function createProperty(StorePropertyRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $building = null;
            $unit = null;

            // Handle existing building or create new one
            if (isset($data['building']) && !empty(array_filter($data['building']))) {
                $buildingData = $data['building'];
                $building = Building::create($buildingData);

                // Create building facilities if provided
                if (isset($buildingData['building_facilities']) && !empty($buildingData['building_facilities'])) {
                    $building->buildingFacilities()->create($buildingData['building_facilities']);
                }
            } elseif (isset($data['unit']['building_id'])) {
                $building = Building::find($data['unit']['building_id']);
                if (!$building) {
                    return $this->sendError('Building not found', [], 404);
                }
            }

            // Handle existing unit or create new one
            if (isset($data['unit']['unit_id'])) {
                $unit = Unit::find($data['unit']['unit_id']);
                if (!$unit) {
                    return $this->sendError('Unit not found', [], 404);
                }
                if ($building) {
                    $unit->building_id = $building->id;
                    $unit->save();
                }
            } elseif (isset($data['unit']) && !empty(array_filter($data['unit']))) {
                $unitData = $data['unit'];
                if ($building) {
                    $unitData['building_id'] = $building->id;
                }
                /*elseif (!isset($unitData['building_id']) || !$unitData['building_id']) {
                    return $this->sendError('Unit must be associated with a building', [], 400);
                }*/

                $unit = Unit::create($unitData);

                // Create related records for unit if data is provided
                if (isset($unitData['unit_destination_premises']) && !empty($unitData['unit_destination_premises'])) {
                    $unit->unitDestinationPremises()->create($unitData['unit_destination_premises']);
                }
                if (isset($unitData['unit_other_facilities']) && !empty($unitData['unit_other_facilities'])) {
                    $unit->unitOtherFacilities()->create($unitData['unit_other_facilities']);
                }
                if (isset($unitData['unit_bathroom_facilities']) && !empty($unitData['unit_bathroom_facilities'])) {
                    $unit->unitBathroomFacilities()->create($unitData['unit_bathroom_facilities']);
                }
                if (isset($unitData['unit_kitchen_facilities']) && !empty($unitData['unit_kitchen_facilities'])) {
                    $unit->unitKitchenFacilities()->create($unitData['unit_kitchen_facilities']);
                }
                if (isset($unitData['unit_comfort_elements']) && !empty($unitData['unit_comfort_elements'])) {
                    $unit->unitComfortElements()->create($unitData['unit_comfort_elements']);
                }
                if (isset($unitData['unit_details']) && !empty($unitData['unit_details'])) {
                    $unit->unitDetails()->create($unitData['unit_details']);
                }
            }

            DB::commit();

            // Load relationships for response
            $responseData = [];
            if ($building) {
                $building->load(['buildingFacilities']);
                $responseData['building'] = $building;
            }
            if ($unit) {
                $unit->load([
                    'unitDestinationPremises',
                    'unitOtherFacilities',
                    'unitBathroomFacilities',
                    'unitKitchenFacilities',
                    'unitComfortElements',
                    'unitDetails'
                ]);
                $responseData['unit'] = $unit;
            }

            $message = 'Property created successfully.';
            return $this->sendResponse($responseData, $message);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return $this->sendError('Failed to create property due to database error', ['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create property', ['error' => $e->getMessage()], 500);
        }
    }


    public function getAllBuildings($entityId)
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Verify the entity belongs to the authenticated user
        $entity = $user->entities()->where('id', $entityId)->first();

        if (!$entity) {
            return $this->sendError('Entity not found or not accessible by this user.', [], 404);
        }

        // Fetch buildings for the specific entity
        $buildings = $entity->buildings()
            ->select('id', 'name as building_name', 'entity_id')
            ->get();

        if ($buildings->isEmpty()) {
            return $this->sendResponse([], 'No buildings found for this entity.', null, 200);
        }

        return $this->sendResponse($buildings, 'Buildings retrieved successfully.', null, 200);
    }


    public function getUnitsWithoutBuilding($entityId)
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Verify the entity belongs to the authenticated user
        $entity = $user->entities()->where('id', $entityId)->first();

        if (!$entity) {
            return $this->sendError('Entity not found or not accessible by this user.', [], 404);
        }

        // Fetch units with no building_id for the specific entity
        $units = $entity->units()
            ->select('id as unit_id', 'name', 'entity_id')
            ->whereNull('building_id')
            ->get();

        if ($units->isEmpty()) {
            return $this->sendResponse([], 'No units without building found for this entity.', null, 200);
        }

        return $this->sendResponse($units, 'Units without building retrieved successfully.', null, 200);
    }



    public function getPropertiesByEntity($entityId)
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Verify the entity belongs to the authenticated user
        $entity = $user->entities()->where('id', $entityId)->first();

        if (!$entity) {
            return $this->sendError('Entity not found or not accessible by this user.', [], 404);
        }

        // Fetch buildings for the specific entity
        $buildings = $entity->buildings()
            ->select('id as building_id', 'name as building_name', 'entity_id')
            ->get()
            ->map(function ($building) {
                return [
                    'property_name' => $building->building_name,
                    'building_id' => $building->building_id,
                    'unit_id' => null,
                    'property_type' => 'building', // Added property_type for buildings
                ];
            });

        // Fetch units with their building names if building_id exists
        $units = Unit::where('units.entity_id', $entityId) // Specify units.entity_id to resolve ambiguity
        ->leftJoin('buildings', 'units.building_id', '=', 'buildings.id')
            ->select(
                'units.id as unit_id',
                'units.name as unit_name',
                'units.entity_id',
                'units.building_id',
                'buildings.name as building_name'
            )
            ->get()
            ->map(function ($unit) {
                $propertyName = $unit->building_id && $unit->building_name
                    ? "{$unit->building_name} - {$unit->unit_name}"
                    : $unit->unit_name;
                return [
                    'property_name' => $propertyName,
                    'building_id' => $unit->building_id,
                    'unit_id' => $unit->unit_id,
                    'property_type' => 'unit', // Added property_type for units
                ];
            });

        // Merge buildings and units
        $properties = $buildings->merge($units);

        if ($properties->isEmpty()) {
            return $this->sendResponse([], 'No properties found for this entity.', null, 200);
        }

        return $this->sendResponse($properties, 'Properties retrieved successfully.', null, 200);
    }





}
