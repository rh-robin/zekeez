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
