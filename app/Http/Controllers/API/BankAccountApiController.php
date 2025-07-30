<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Requisition;
use App\Trait\ResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankAccountApiController extends Controller
{
    use ResponseTrait;

    public function createRequisition(Request $request)
    {
        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $accessToken = $this->getAccessToken();

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

            // Store the mapping
            Requisition::create([
                'requisition_id' => $requisitionId,
                'reference' => $reference,
                'entity_id' => $request->input('entity_id')
            ]);

            return $this->sendResponse(['link' => $requisition['link'], 'requisition_id' => $requisitionId], 'Requisition created');
        } catch (\Exception $e) {
            return $this->sendError('Failed to create requisition', ['error' => $e->getMessage()], 500);
        }
    }

    public function linkAccount(Request $request)
    {
        $client = new Client();
        $baseUrl = 'https://bankaccountdata.gocardless.com/api/v2';
        $accessToken = $this->getAccessToken();
        $reference = $request->reference;

        try {
            // Find the requisition_id using the reference
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
                Account::create([
                    'account_id' => $accountId,
                    'entity_id' => $requisition->entity_id,
                    'access_token' => $accessToken,
                    'token_expires_at' => now()->addDay()
                ]);
            }

            return $this->sendResponse(['account_ids' => $accountIds], 'Accounts linked');
        } catch (\Exception $e) {
            return $this->sendError('Failed to link accounts', ['error' => $e->getMessage()], 500);
        }
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

    /*protected function getAccessToken()
    {
        // Use the access token created in the sandbox dashboard
        return env('GOCARDLESS_ACCESS_TOKEN');
    }*/


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
        // Use sandbox for now
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
