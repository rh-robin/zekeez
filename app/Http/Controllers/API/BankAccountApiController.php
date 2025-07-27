<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class BankAccountApiController extends Controller
{
    use ResponseTrait;

    public function linkAccount(Request $request)
    {
        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $accessToken = $this->getAccessToken();

        // Step 1: Create a requisition (using sandbox)
        try {
            $response = $client->post("{$baseUrl}/requisitions/", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'institution_id' => $this->getInstitutionId(),
                    'redirect' => env('APP_URL') . '/account-linked'
                ]
            ]);

            $requisition = json_decode($response->getBody()->getContents(), true);
            $requisitionId = $requisition['id'];
        } catch (\Exception $e) {
            return $this->sendError('Failed to create requisition', ['error' => $e->getMessage()], 500);
        }

        // Step 2: Fetch account details (simulate auth in sandbox)
        try {
            $accountResponse = $client->get("{$baseUrl}/requisitions/{$requisitionId}/", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);

            $accountData = json_decode($accountResponse->getBody()->getContents(), true);
            $accountId = $accountData['accounts'][0]['account_id'] ?? null;
        } catch (\Exception $e) {
            return $this->sendError('Failed to fetch account details', ['error' => $e->getMessage()], 500);
        }

        if (!$accountId) {
            return $this->sendError('No account linked', [], 400);
        }

        // Step 3: Store the account
        try {
            Account::create([
                'account_id' => $accountId,
                'entity_id' => $request->input('entity_id'),
                'access_token' => $accessToken,
                'token_expires_at' => now()->addDay()
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Failed to store account', ['error' => $e->getMessage()], 500);
        }

        return $this->sendResponse(['account_id' => $accountId], 'Account linked');
    }

    public function getTransactions($accountId)
    {
        $account = Account::where('account_id', $accountId)->firstOrFail();

        if ($account->token_expires_at < now()) {
            return $this->sendError('Token expired', [], 401);
        }

        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $headers = [
            'Authorization' => 'Bearer ' . $account->access_token,
            'Accept' => 'application/json'
        ];

        try {
            $response = $client->get("{$baseUrl}/accounts/{$accountId}/transactions/", [
                'headers' => $headers,
                'query' => [
                    'date_from' => now()->subMonths(2)->toDateString(),
                    'date_to' => now()->toDateString()
                ]
            ]);

            $transactions = json_decode($response->getBody()->getContents(), true);
            return $this->sendResponse($transactions, 'Transactions retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve transactions', ['error' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    protected function getAccessToken()
    {
        // Use the access token created in the sandbox dashboard
        return env('GOCARDLESS_ACCESS_TOKEN', 'your_sandbox_access_token');
    }

    protected function getInstitutionId()
    {
        // Use sandbox for now
        return 'SANDBOXFINANCE_SFIN0000';
    }
}
