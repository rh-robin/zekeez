<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Contact;
use App\Models\Entity;
use App\Models\EntityAccess;
use App\Models\Unit;
use App\Trait\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EntityApiController extends Controller
{
    use ResponseTrait;

    public function getAllEntitiesFroDropdown()
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();
        $entities = Entity::where('user_id', $user->id)
            ->select('id', 'name')
            ->latest()
            ->get();

        $message = 'Entity retrieved successfully.';
        return $this->sendResponse($entities, $message);

    }



    public function getAllEntities()
    {
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();
        $entities = Entity::where('user_id', $user->id)->latest()->get();

        $formattedEntities = $entities->map(function ($entity) {
            // Get all buildings for the entity with their units
            $buildings = $entity->buildings()->with('units')->get();

            // Get standalone units (those with no building_id) for the entity
            $standaloneUnits = $entity->units()->whereNull('building_id')->get();

            // Combine into properties array
            $properties = collect();

            // Add buildings with their units
            foreach ($buildings as $building) {
                $properties->push([
                    'id' => $building->id,
                    'name' => $building->name ?? "Building {$building->id}",
                    'type' => 'building',
                    'property_type' => 'building',
                    'units' => $building->units->map(function ($unit) {
                        return [
                            'id' => $unit->id,
                            'name' => $unit->name ?? "Unit {$unit->id}",
                        ];
                    })->all(),
                ]);
            }

            // Add standalone units
            foreach ($standaloneUnits as $unit) {
                $properties->push([
                    'id' => $unit->id,
                    'name' => $unit->name ?? "Unit {$unit->id}",
                    'type' => 'unit',
                    'property_type' => 'unit',
                    'units' => [],
                ]);
            }

            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'type' => $entity->type,
                'properties' => $properties->all(),
            ];
        });

        $message = 'Entities retrieved successfully.';
        return $this->sendResponse($formattedEntities, $message);
    }


    /*====== get the entities which has granted access =======*/
    public function getAccessibleEntities()
    {
        // Ensure the user is authenticated via 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Get all entities the user has access to (via entity_accesses pivot table)
        $entities = $user->accessibleEntities()->latest()->get();

        return $this->sendResponse($entities, 'Accessible entities retrieved successfully.');
    }


    public function storeEntity(Request $request)
    {
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:individual,joint_owner,legal_entity',
            'contact_id' => 'nullable|array',
            'contact_id.*' => 'exists:contacts,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();

        try {
            // Create or update entity for this user
            $entity = Entity::updateOrCreate(
                [
                    'name' => $request->name,
                    'user_id' => $user->id,
                ],
                [
                    'type' => $request->type,
                ]
            );

            // Update entity_ids in contacts table if contact_ids are provided
            if ($request->has('contact_id') && !empty($request->contact_id)) {
                foreach ($request->contact_id as $contactId) {
                    $contact = Contact::find($contactId);
                    if ($contact) {
                        $existingEntityIds = $contact->entity_ids ?? [];
                        $newEntityIds = array_unique(array_merge((array) $existingEntityIds, [$entity->id]));
                        $contact->update(['entity_ids' => $newEntityIds]);
                    }
                }
            }

            DB::commit();

            return $this->sendResponse($entity, 'Entity saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }


}
