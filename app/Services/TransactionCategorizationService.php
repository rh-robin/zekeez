<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Cloud\AIPlatform\V1\PredictRequest;
use Google\Cloud\AIPlatform\V1\Value;
use Illuminate\Support\Facades\Http;

class TransactionCategorizationService
{
    public function categorizeTransaction(array $transaction, $accountId)
    {
        $prompt = $this->buildPrompt($transaction, $accountId);
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            throw new \Exception('Gemini API key is not configured. Please set GEMINI_API_KEY in .env');
        }

        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
        // Note: Adjust model (e.g., gemini-1.5-pro) based on your API access

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
                return ['success' => false];
            }

            $result = $response->json();
            $prediction = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';

            // Corrected regular expression to remove ```json
            $cleanedPrediction = preg_replace('/^```json\s*|\s*```$/m', '', $prediction);
            $categorization = json_decode($cleanedPrediction, true);

            // Debug: Log the raw and cleaned prediction
            //\Log::info('Raw Prediction: ' . $prediction);
            //\Log::info('Cleaned Prediction: ' . $cleanedPrediction);
            \Log::info('Categorization: ' . json_encode($categorization));

            return [
                'success' => $categorization['confidence'] > 0.7,
                'entity_id' => $categorization['entity_id'] ?? null,
                'building_id' => $categorization['building_id'] ?? null,
                'unit_id' => $categorization['unit_id'] ?? null,
                'lease_id' => $categorization['lease_id'] ?? null,
                'tenant_id' => $categorization['tenant_id'] ?? null,
                'category_id' => $categorization['category_id'] ?? null,
            ];
        } catch (\Exception $e) {
            \Log::error('Transaction Categorization Error: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    protected function buildPrompt(array $transaction, $accountId)
    {
        // Fetch all transaction categories
        $categories = TransactionCategory::with('children')->get()->map(function ($category) {
            $path = $category->parent ? "{$category->parent->name} > {$category->name}" : $category->name;
            return "ID: {$category->id}, Name: {$path}, Description: {$category->description}";
        })->join("\n");

        $historicalTransactions = Transaction::where('account_id', $accountId)
            ->where('status', 'validated')
            ->get()
            ->map(function ($t) {
                $categoryPath = $t->category->parent ? "{$t->category->parent->name} > {$t->category->name}" : $t->category->name;
                $tenantName = isset($t->tenant->first_name) ? $t->tenant->first_name : 'N/A';
                return "Date: {$t->booking_date}, Amount: {$t->amount}, Category: {$categoryPath}, Tenant: {$tenantName}";
            })->join("\n");

        $transactionDate = $transaction['bookingDate'] ?? $transaction['valueDate'];
        $amount = $transaction['transactionAmount']['amount'];
        $currency = $transaction['transactionAmount']['currency'];
        $creditor = $transaction['creditorName'] ?? 'N/A';
        $debtor = $transaction['debtorName'] ?? 'N/A';
        $remittance = $transaction['remittanceInformationUnstructured'] ?? 'N/A';

        return "Categorize the following transaction for a property management system where tenants pay rent and bills:\n"
            . "Transaction: Date={$transactionDate}, Amount={$amount}, Currency={$currency}, Creditor={$creditor}, Debtor={$debtor}, Remittance={$remittance}\n"
            . "Available Categories:\n{$categories}\n"
            . "Historical Transactions:\n{$historicalTransactions}\n"
            . "Match against tenant bank details (IBAN, name) and lease properties. Return a JSON object with: {confidence: float, entity_id: int|null, building_id: int|null, unit_id: int|null, lease_id: int|null, tenant_id: int|null, category_id: int|null} using the provided category IDs. Return only the JSON object, without any additional text or formatting.";
    }
}
