<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Building;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Unit;
use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\Cloud\AIPlatform\V1\Value;
use Illuminate\Support\Facades\Http;

class TransactionCategorizationService
{
    public function categorizeTransaction(array $transactions, $accountId)
    {
        $prompt = $this->buildPrompt($transactions, $accountId);

        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in .env');
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        try {
            $response = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($response->failed()) {
                \Log::error('Gemini API Error: ' . $response->body());
                return array_fill(0, count($transactions), ['success' => false]);
            }

            $result = $response->json();
            $prediction = $result['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
            $cleanedPrediction = preg_replace('/^```json\s*|\s*```$/m', '', $prediction);
            $categorizations = json_decode($cleanedPrediction, true);

            \Log::info('Raw Prediction: ' . $prediction);
            \Log::info('Cleaned Prediction: ' . $cleanedPrediction);
            \Log::info('Categorizations: ' . json_encode($categorizations));

            return $categorizations;
        } catch (\Exception $e) {
            \Log::error('Transaction Categorization Error: ' . $e->getMessage());
            return array_fill(0, count($transactions), ['success' => false]);
        }
    }

    protected function buildPrompt(array $transactions, $accountId)
    {
        $account = Account::with(['entities.buildings', 'entities.units', 'entities.tenants.bankDetails', 'entities.leases.leaseFinancialRentConditions', 'entities.leases.tenants'])->findOrFail($accountId);
        $portfolio = $this->buildPortfolio($account);
        $categories = TransactionCategory::with('children')->get()->map(function ($category) {
            $path = $category->parent ? "{$category->parent->name} > {$category->name}" : $category->name;
            return "ID: {$category->id}, Name: {$path}, Description: {$category->description}";
        })->join("\n");
        $historicalTransactions = Transaction::where('account_id', $account->id)
            ->where('status', 'validated')
            ->get()
            ->map(function ($t) {
                $categoryPath = $t->category->parent ? "{$t->category->parent->name} > {$t->category->name}" : $t->category->name;
                $tenantName = $t->tenant?->first_name ?? 'N/A';
                return "Date: {$t->booking_date}, Amount: {$t->amount}, Debtor: {$t->debtor_name}, Category: {$categoryPath}, Mapped to Lease: {$t->lease_id}";
            })->join("\n");

        $transactionData = array_map(function ($transaction) {
            return [
                'date' => $transaction['bookingDate'] ?? $transaction['valueDate'],
                'amount' => $transaction['transactionAmount']['amount'],
                'debtor_name' => $transaction['debtorName'] ?? 'N/A',
                'remittance_info' => $transaction['remittanceInformationUnstructured'] ?? 'N/A',
            ];
        }, $transactions);

        return "You are an expert real estate transaction categorization AI. Your goal is to analyze bank transactions, categorize them into the provided categories and, whenever possible, precisely map it to the lessor's portfolio\n"
            . "--- LESSOR PORTFOLIO (DATA MODEL) ---\n"
            . "This JSON object describes all properties, tenants, and the leases that connect them. Use it as your single source of truth.\n"
            . "```json\n" . json_encode($portfolio, JSON_PRETTY_PRINT) . "\n```\n"
            . "--- AVAILABLE CATEGORIES ---\n"
            . $categories . "\n"
            . "--- RECENT HISTORICAL EXAMPLES ---\n"
            . $historicalTransactions . "\n"
            . "--- TRANSACTIONS TO CATEGORIZE ---\n"
            . json_encode($transactionData) . "\n"
            . "--- YOUR TASK ---\n"
            . "Analyze each transaction's Debtor Name, Amount, and Remittance Info.\n"
            . "Cross-reference this with the ‘entities’, 'tenants', 'leases', and 'properties' in the JSON data model.\n"
            . "Not all transactions will be mapped to each dimension. For most, you’ll need past validated transactions to learn from them. For instance, a general expense like accounting fees will only be mapped to a category ID and an entity. A rent will be mapped to an entity, a lease (hence a property and a tenant). An electricity debit will be mapped to an entity and a property (either a building alone or a building and a unit).\n"
            . "To categorize a rent, look at the lease details (expected rent amount, name of the tenant, any description from the transaction that matches the lease type or rented property type, etc.) and try to map it to credits. Note that not all rent amounts received will exactly match the expected rent (some tenants might pay their rent in two transactions).\n"
            . "If it's a general expense (e.g., accounting fees), map only the category_id.\n"
            . "Return a JSON array of objects, one per transaction, with your conclusion. Structure each as: {\"confidence\":\"Confident\"|\"To Validate\"|\"To Categorize\",\"entity_id\":int|null,\"building_id\":int|null,\"unit_id\":int|null,\"lease_id\":int|null,\"tenant_id\":int|null,\"category_id\":int|null}. Return ONLY the JSON array and nothing else.";
    }

    protected function buildPortfolio(Account $account)
    {
        $entities = $account->entities;
        $portfolio = [
            'entities' => $entities->map(function ($entity) {
                return [
                    'id' => $entity->id,
                    'name' => $entity->name,
                ];
            })->values(),
            'properties' => [],
            'tenants' => [],
            'leases' => [],
        ];

        // Aggregate properties (buildings and units)
        $buildings = $entities->flatMap->buildings;
        $units = $entities->flatMap->units;
        $portfolio['properties'] = $buildings->map(function ($building) {
            return [
                'property_id' => $building->id,
                'address' => implode(', ', array_filter([
                    $building->address['street'] ?? $building->address,
                    $building->post_code,
                    $building->city,
                    $building->country,
                ])),
                'property_type' => 'Building',
            ];
        })->merge($units->map(function ($unit) {
            return [
                'property_id' => $unit->id,
                'address' => implode(', ', array_filter([
                    $unit->address,
                    $unit->post_code,
                    $unit->city,
                    $unit->country,
                ])),
                'property_type' => 'Unit',
                'building_id' => $unit->building_id,
            ];
        }))->values();

        // Aggregate tenants
        $tenants = $entities->flatMap->tenants;
        $portfolio['tenants'] = $tenants->map(function ($tenant) {
            $bankDetail = $tenant->bankDetails->first();
            return [
                'tenant_id' => $tenant->id,
                'name' => trim("{$tenant->first_name} {$tenant->last_name}"),
                'iban' => $bankDetail?->rib_iban,
            ];
        })->unique('tenant_id')->values();

        // Aggregate leases
        $leases = $entities->flatMap->leases;
        $portfolio['leases'] = $leases->map(function ($lease) {
            $rentCondition = $lease->leaseFinancialRentConditions->first();
            $tenant = $lease->tenants->first();
            $building = $lease->buildings->first();
            $unit = $lease->units->first();
            $property = $building ?: $unit; // Use first available property
            $propertyAddress = $property instanceof Building
                ? implode(', ', array_filter([$property->address['street'] ?? $property->address, $property->post_code, $property->city, $property->country]))
                : ($property instanceof Unit
                    ? implode(', ', array_filter([$property->address, $property->post_code, $property->city, $property->country]))
                    : null);
            return [
                'lease_id' => $lease->id,
                'property_id' => $property->id ?? null,
                'tenant_id' => $tenant->id ?? null,
                'rent_amount' => $rentCondition?->rent_amount,
                'property_address' => $propertyAddress,
                'tenant_name' => $tenant ? trim("{$tenant->first_name} {$tenant->last_name}") : null,
            ];
        })->values();

        return $portfolio;
    }
}
