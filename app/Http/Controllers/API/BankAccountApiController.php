<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Building;
use App\Models\Requisition;
use App\Models\Tenant;
use App\Models\TenantBankDetail;
use App\Models\Transaction;
use App\Models\Unit;
use App\Services\TransactionCategorizationService;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BankAccountApiController extends Controller
{
    use ResponseTrait;

    public function createRequisition(Request $request)
    {
        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $accessToken = $this->getAccessToken();

        // Validate multiple entity_ids
        $request->validate([
            'entity_ids' => 'required|array',
            'entity_ids.*' => 'exists:entities,id'
        ]);

        try {
            $agreementId = $this->createEUA();
            $response = $client->post("{$baseUrl}/requisitions/", [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken, 'Accept' => 'application/json', 'Content-Type' => 'application/json'],
                'json' => [
                    'institution_id' => $this->getInstitutionId(),
                    'redirect' => env('APP_URL') . '/account-linked',
                    'reference' => 'req_' . uniqid(),
                    'agreement' => $agreementId,
                    'user_language' => 'EN'
                ]
            ]);
            $requisition = json_decode($response->getBody()->getContents(), true);
            $requisitionId = $requisition['id'];
            $reference = $requisition['reference'];

            Requisition::create([
                'requisition_id' => $requisitionId,
                'reference' => $reference,
                'entity_ids' => json_encode($request->input('entity_ids'))
            ]);

            return $this->sendResponse(['link' => $requisition['link'], 'requisition_id' => $requisitionId], 'Requisition created');
        } catch (\Exception $e) {
            return $this->sendError('Failed to create requisition', ['error' => $e->getMessage()], 500);
        }
    }

    public function connectAccount(Request $request)
    {
        $user = Auth::user();
        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $accessToken = $this->getAccessToken();
        $reference = $request->reference;

        try {
            $requisition = Requisition::where('reference', $reference)->firstOrFail();
            $requisitionId = $requisition->requisition_id;

            $accountResponse = $client->get("{$baseUrl}/requisitions/{$requisitionId}/", [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken, 'Accept' => 'application/json']
            ]);
            $accountData = json_decode($accountResponse->getBody()->getContents(), true);
            $accountIds = $accountData['accounts'] ?? [];

            if (empty($accountIds)) {
                return $this->sendError('No accounts linked', ['error' => 'Accounts not found in requisition'], 400);
            }

            foreach ($accountIds as $accountId) {
                $account = Account::create([
                    'user_id' => $user->id,
                    'account_id' => $accountId,
                    'access_token' => $accessToken,
                    'token_expires_at' => now()->addDay()
                ]);

                // Attach multiple entities to the account
                $entityIds = json_decode($requisition->entity_ids, true);
                $account->entities()->attach($entityIds);
            }

            return $this->sendResponse(['account_ids' => $accountIds], 'Accounts linked');
        } catch (\Exception $e) {
            return $this->sendError('Failed to link accounts', ['error' => $e->getMessage()], 500);
        }
    }

    public function getTransactions($accountId)
    {
        // Debug: Log method entry
        //\Log::info("getTransactions called for accountId: {$accountId}");

        $account = Account::where('account_id', $accountId)->firstOrFail();

        // Debug: Log token expiration status
        //\Log::info("Token expires at: {$account->token_expires_at}, Current time: " . now());

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
            //\Log::info("Making API call to: {$baseUrl}/accounts/{$accountId}/transactions/ with headers: " . json_encode($headers));
            $response = $client->get("{$baseUrl}/accounts/{$accountId}/transactions/", [
                'headers' => $headers,
                'query' => [
                    'date_from' => '2025-07-20',
                    'date_to' => '2025-07-20'
                ]
            ]);

            // Debug: Log the raw response
            $responseBody = $response->getBody()->getContents();
            //\Log::info("API Response: " . $responseBody);

            $transactions = json_decode($responseBody, true);
            //return $this->sendResponse($transactions, 'Transactions retrieved');
            $categorizedTransactions = [];

            // Debug: Log the structure of the transactions response
            //\Log::info("Transactions response structure: " . json_encode($transactions));

            // Access the nested 'transactions' key
            $bookedTransactions = $transactions['transactions']['booked'] ?? [];
            //$pendingTransactions = $transactions['transactions']['pending'] ?? [];

            if (empty($bookedTransactions) && empty($pendingTransactions)) {
                \Log::warning("No transactions found in response for accountId: {$accountId}");
                return $this->sendResponse(['transactions' => []], 'No transactions found');
            }

            //foreach (array_merge($bookedTransactions, $pendingTransactions) as $transaction) {
            foreach ($bookedTransactions as $transaction) {
                // Check if transaction_id already exists in the database
                if (Transaction::where('transaction_id', $transaction['transactionId'])->exists()) {
                    \Log::info("Transaction ID {$transaction['transactionId']} already exists, skipping.");
                    continue; // Skip to the next iteration
                }
                if(($transaction['creditorName'] !== "Jennifer Houston") || ($transaction['debtorName'] !== "Jennifer Houston")) {
                    continue;
                }
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
                    'status' => 'to_categorize',
                ];

                // Categorize with AI
                $aiService = new TransactionCategorizationService();
                $categorization = $aiService->categorizeTransaction($transaction, $account->id);

                if ($categorization['success']) {
                    $transactionData = array_merge($transactionData, [
                        'entity_id' => $categorization['entity_id'] ?? $account->entity_id,
                        'building_id' => $categorization['building_id'],
                        'unit_id' => $categorization['unit_id'],
                        'lease_id' => $categorization['lease_id'],
                        'tenant_id' => $categorization['tenant_id'],
                        'category_id' => $categorization['category_id'],
                        'status' => 'to_validate',
                    ]);

                    // Match tenant and property if not set by AI
                    if (!$categorization['tenant_id']) {
                        $tenant = $this->matchTenant($transaction);

                        if ($tenant) {
                            \Log::info("Matched debtorName account name: {$tenant}");
                            $transactionData['tenant_id'] = $tenant->id;
                            $lease = $tenant->leases()->first();
                            \Log::info("Lease: {$lease}");
                            if ($lease) {
                                $transactionData['lease_id'] = $lease->id;
                                $property = $lease->properties()->first();
                                \Log::info("Lease: {$property}");
                                if ($property instanceof Building) {
                                    $transactionData['building_id'] = $property->id;
                                } elseif ($property instanceof Unit) {
                                    $transactionData['unit_id'] = $property->id;
                                    $transactionData['building_id'] = $property->building_id;
                                }
                                $transactionData['entity_id'] = $lease->entity_id ?? $account->entity_id;
                            }
                        }
                    }
                }
                //$categorizedTransactions[] = $transactionData;
                $categorizedTransactions[] = Transaction::create($transactionData);
            }

            return $this->sendResponse(['transactions' => $categorizedTransactions], 'Transactions retrieved and categorized');
        } catch (\Exception $e) {
            \Log::error("Exception in getTransactions: " . $e->getMessage());
            return $this->sendError('Failed to retrieve transactions', ['error' => $e->getMessage()], 500);
        }
    }

    protected function matchTenant(array $transaction)
    {
        $iban = $transaction['creditorAccount']['iban'] ?? $transaction['debtorAccount']['iban'] ?? null;
        $name = $transaction['creditorName'] ?? $transaction['debtorName'] ?? null;
        \Log::info("Matching debtorName account name: {$name}");

        if ($iban) {
            return TenantBankDetail::where('rib_iban', $iban)->first()?->tenant;
        } elseif ($name) {
            $tenant =  Tenant::where('first_name', 'Jennifer')->first();
            //$tenant = Tenant::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$name}%"])->first();
            //\Log::info("Matched debtorName account name: {$tenant}");
            return $tenant;
        }
        return null;
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

    protected function getInstitutionId()
    {
        return 'SANDBOXFINANCE_SFIN0000';
    }

    protected function createEUA()
    {
        $client = new Client();
        $accessToken = $this->getAccessToken();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';

        try {
            $response = $client->post("{$baseUrl}/agreements/enduser/", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'institution_id' => $this->getInstitutionId(),
                    'max_historical_days' => 90,
                    'access_valid_for_days' => 90,
                    'access_scope' => ['balances', 'details', 'transactions']
                ]
            ]);

            $euaData = json_decode($response->getBody()->getContents(), true);
            return $euaData['id'];
        } catch (\Exception $e) {
            throw new \Exception('Failed to create EUA: ' . $e->getMessage());
        }
    }
}
