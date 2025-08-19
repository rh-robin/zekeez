<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactBankDetail;
use App\Models\Contact;
use App\Models\EntityAccess;
use App\Models\ContactEntityRepresentative;
use App\Models\Property;
use App\Models\User;
use App\Trait\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ContactApiController extends Controller
{
    use ResponseTrait;
    public function store(StoreContactRequest $request)
    {
        $user = auth('api')->user();
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Save contact data
            $contact = Contact::create([
                'user_id' => $user->id,
                'entity_ids' => $validated['entity_ids'] ? json_encode($validated['entity_ids']) : null,
                'type' => $validated['type'],
                'category' => $validated['category'],
                'salutation' => $validated['salutation'] ?? null,
                'first_name' => $validated['first_name'] ?? null,
                'last_name' => $validated['last_name'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'legal_status' => $validated['legal_status'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address_line_1' => $validated['address_line_1'] ?? null,
                'address_line_2' => $validated['address_line_2'] ?? null,
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'additional_info' => $validated['additional_info'] ?? null,
            ]);

            // Save bank details
            if ($validated['bank_name'] || $validated['rib_iban'] || $validated['bic_swift']) {
                ContactBankDetail::create([
                    'contact_id' => $contact->id,
                    'name' => $validated['bank_name'] ?? null,
                    'rib_iban' => $validated['rib_iban'] ?? null,
                    'bic_swift' => $validated['bic_swift'] ?? null,
                    'address_line_1' => $validated['bank_address_line_1'] ?? null,
                    'address_line_2' => $validated['bank_address_line_2'] ?? null,
                    'country' => $validated['bank_country'] ?? null,
                    'city' => $validated['bank_city'] ?? null,
                    'postal_code' => $validated['bank_postal_code'] ?? null,
                ]);
            }

            // Save entity representative data
            if ($validated['type'] === 'legal_entity' && $validated['r_email']) {
                ContactEntityRepresentative::create([
                    'contact_id' => $contact->id,
                    'salutation' => $validated['r_salutation'] ?? null,
                    'first_name' => $validated['r_first_name'] ?? null,
                    'last_name' => $validated['r_last_name'] ?? null,
                    'quality' => $validated['r_quality'] ?? null,
                    'email' => $validated['r_email'] ?? null,
                    'phone' => $validated['r_phone'] ?? null,
                    'date_of_birth' => $validated['r_date_of_birth'] ?? null,
                    'place_of_birth' => $validated['r_place_of_birth'] ?? null,
                    'address_line_1' => $validated['r_address_line_1'] ?? null,
                    'address_line_2' => $validated['r_address_line_2'] ?? null,
                    'country' => $validated['r_country'] ?? null,
                    'city' => $validated['r_city'] ?? null,
                    'postal_code' => $validated['r_postal_code'] ?? null,
                    'siren' => $validated['siren'] ?? null,
                    'website_url' => $validated['website_url'] ?? null,
                ]);
            }

            // Save contact-property relationships
            if (!empty($validated['property_ids'])) {
                foreach ($validated['property_ids'] as $property) {
                    // Validate property ID existence based on type
                    if ($property['type'] === 'App\Models\Building') {
                        if (!\App\Models\Building::where('id', $property['id'])->exists()) {
                            throw new \Exception("Building ID {$property['id']} does not exist.");
                        }
                    } elseif ($property['type'] === 'App\Models\Unit') {
                        if (!\App\Models\Unit::where('id', $property['id'])->exists()) {
                            throw new \Exception("Unit ID {$property['id']} does not exist.");
                        }
                    }

                    DB::table('contact_property')->insert([
                        'contact_id' => $contact->id,
                        'property_id' => $property['id'],
                        'property_type' => $property['type'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            $success = [
                'contact_id' => $contact->id,
            ];

            return $this->sendResponse($success, 'Contact saved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    } // end store


    /*======= get all individual contact =========*/
    public function getIndividualContacts()
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Fetch contacts of type 'individual' created by the authenticated user
        $contacts = Contact::where('user_id', $user->id)
            ->where('type', 'individual')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->first_name . ' ' . $contact->last_name,
                ];
            });

        return $this->sendResponse($contacts, 'Individual contacts retrieved successfully');
    }


    /*======= get all legal entity contact =========*/
    public function getLegalEntityContacts()
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Fetch contacts of type 'individual' created by the authenticated user
        $contacts = Contact::where('user_id', $user->id)
            ->where('type', 'legal_entity')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->company_name,
                ];
            });

        return $this->sendResponse($contacts, 'Legal entity contacts retrieved successfully');
    }


    /*======= get all contacts =========*/
    /*public function getAllContacts()
    {
        // Check if the user is authenticated via the 'api' guard
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Get all contacts created by the authenticated user and eager load relationships
        $contacts = $user->contactsCreated()
            ->with([
                'user.bankDetail',   // contact's assigned user and their bank detail
                'representative'     // contact's representative
            ])
            ->get();

        return $this->sendResponse($contacts, 'All contacts retrieved successfully');
    }*/

    public function getAllContacts()
    {
        // Ensure the user is authenticated
        if (!auth('api')->check()) {
            return $this->sendError('Please login first', [], 422);
        }

        $user = auth('api')->user();

        // Get all entity IDs created by this user
        $ownedEntityIds = $user->entities()->pluck('id');

        // Get all contacts created by this user
        $contacts = $user->contactsCreated()
            ->with([
                'bankDetails',
                'representative',
            ])
            ->get();

        return $this->sendResponse($contacts, 'All contacts retrieved successfully');
    }


}
