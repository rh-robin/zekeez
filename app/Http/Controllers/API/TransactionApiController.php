<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Building;
use App\Models\Tenant;
use App\Models\TenantBankDetail;
use App\Models\Transaction;
use App\Models\Unit;
use App\Services\TransactionCategorizationService;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    use ResponseTrait;

    public function getTransactions($accountId)
    {
        // Debug: Log method entry
        \Log::info("getTransactions called for accountId: {$accountId}");

        $account = Account::where('account_id', $accountId)->firstOrFail();

        // Debug: Log token expiration status
        \Log::info("Token expires at: {$account->token_expires_at}, Current time: " . now());

        if ($account->token_expires_at < now()) {
            \Log::info("Token expired, attempting to regenerate");
            try {
                $newAccessToken = $this->getAccessToken();
                $account->update([
                    'access_token' => $newAccessToken,
                    'token_expires_at' => now()->addDay()
                ]);
                \Log::info("Token regenerated successfully: {$newAccessToken}");
            } catch (\Exception $e) {
                \Log::error("Token refresh failed: " . $e->getMessage());
                return $this->sendError('Token refresh failed. Please re-authenticate.', ['error' => $e->getMessage()], 401);
            }
        }

        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $headers = [
            'Authorization' => 'Bearer ' . $account->access_token,
            'Accept' => 'application/json'
        ];

        try {
            // Debug: Log the API request details
            \Log::info("Making API call to: {$baseUrl}/accounts/{$accountId}/transactions/ with headers: " . json_encode($headers));
            $response = $client->get("{$baseUrl}/accounts/{$accountId}/transactions/", [
                'headers' => $headers,
                'query' => [
                    'date_from' => '2025-07-20',
                    'date_to' => '2025-07-20'
                ]
            ]);

            // Debug: Log the raw response
            $responseBody = $response->getBody()->getContents();
            \Log::info("API Response: " . $responseBody);

            $transactions = json_decode($responseBody, true);
            $categorizedTransactions = [];

            // Access the nested 'transactions' key
            $bookedTransactions = $transactions['transactions']['booked'] ?? [];

            if (empty($bookedTransactions)) {
                \Log::warning("No transactions found in response for accountId: {$accountId}");
                return $this->sendResponse(['transactions' => []], 'No transactions found');
            }

            // Batch categorize all transactions
            $aiService = new TransactionCategorizationService();
            $categorizations = $aiService->categorizeTransaction($bookedTransactions, $account->id);

            // Debug: Log the categorizations received
            \Log::info("Categorizations received: " . json_encode($categorizations));

            foreach ($bookedTransactions as $index => $transaction) {
                // Check if transaction_id already exists in the database
                if (Transaction::where('transaction_id', $transaction['transactionId'])->exists()) {
                    \Log::info("Transaction ID {$transaction['transactionId']} already exists, skipping.");
                    continue;
                }

                $categorization = $categorizations[$index] ?? null;
                $transactionData = [
                    'account_id' => $account->id,
                    'transaction_id' => $transaction['transactionId'],
                    'entry_reference' => $transaction['entryReference'],
                    'booking_date' => $transaction['bookingDate'] ?? null,
                    'value_date' => $transaction['valueDate'],
                    'amount' => $transaction['transactionAmount']['amount'],
                    'currency' => $transaction['transactionAmount']['currency'],
                    'creditor_name' => $transaction['creditorName'] ?? null,
                    'debtor_name' => $transaction['debtorName'] ?? null,
                    'remittance_information' => $transaction['remittanceInformationUnstructured'] ?? null,
                    'bank_transaction_code' => $transaction['bankTransactionCode'] ?? null,
                    'proprietary_bank_transaction_code' => $transaction['proprietaryBankTransactionCode'] ?? null,
                    'internal_transaction_id' => $transaction['internalTransactionId'] ?? null,
                ];

                if ($categorization) {
                    $transactionData = array_merge($transactionData, [
                        'entity_id' => $categorization['entity_id'],
                        'building_id' => $categorization['building_id'],
                        'unit_id' => $categorization['unit_id'],
                        'lease_id' => $categorization['lease_id'],
                        'tenant_id' => $categorization['tenant_id'],
                        'category_id' => $categorization['category_id'],
                    ]);

                    // Set status based on confidence category
                    $confidence = $categorization['confidence'] ?? 'To Categorize';
                    $transactionData['status'] = match ($confidence) {
                        'Confident' => 'validated',
                        'To Validate' => 'to_validate',
                        'To Categorize' => 'to_categorize',
                        default => 'to_categorize',
                    };
                } else {
                    $transactionData['status'] = 'to_categorize';
                }

                $categorizedTransactions[] = Transaction::create($transactionData);
            }

            return $this->sendResponse(['transactions' => $categorizedTransactions], 'Transactions retrieved and categorized');
        } catch (\Exception $e) {
            \Log::error("Exception in getTransactions: " . $e->getMessage());
            return $this->sendError('Failed to retrieve transactions', ['error' => $e->getMessage()], 500);
        }
    }

    protected function getAccessToken()
    {
        $client = new Client();
        try {
            $response = $client->post('https://bankaccountdata.gocardless.com/api/v2/token/new/', [
                'json' => [
                    'secret_id' => "426f2dff-c98e-4940-9cd7-b0338c294de0",
                    'secret_key' => "ae3a880361fd9d1cd963b1aca4defe76101d55ea6522cde387fcdaefab1b133e183fcc9db7f3df827f068f7e40ffff1a7c5cea2b0e9f54401036b85ca3fb49ac"
                ]
            ]);
            $tokenData = json_decode($response->getBody()->getContents(), true);
            return $tokenData['access'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate access token: ' . $e->getMessage());
        }
    }



    public function index()
    {
        $transactions = Transaction::with(['account', 'entity', 'building', 'unit', 'lease', 'tenant', 'category'])->get();
        return response()->json(['success' => true, 'data' => $transactions], 200);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['account', 'entity', 'building', 'unit', 'lease', 'tenant', 'category'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $transaction], 200);
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $validated = $request->validate([
            'entity_id' => 'nullable|exists:entities,id',
            'building_id' => 'nullable|exists:buildings,id',
            'unit_id' => 'nullable|exists:units,id',
            'lease_id' => 'nullable|exists:leases,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'category_id' => 'nullable|exists:transaction_categories,id',
            'status' => 'in:to_categorize,to_validate,validated,archived',
        ]);
        if (isset($validated['status']) && $validated['status'] === 'validated') {
            // Optional: Add additional validation logic if needed
        }
        $transaction->update($validated);
        return response()->json(['success' => true, 'data' => $transaction], 200);
    }

    public function archive($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->update(['status' => 'archived']);
        return response()->json(['success' => true, 'message' => 'Transaction archived'], 200);
    }
}
