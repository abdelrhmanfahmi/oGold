<?php 

namespace App\Services;

use App\Models\MatchData;

class MatchService {

    public function getAccessToken()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/proxy/auth/oauth/token';
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type: application/x-www-form-urlencoded' , 'Authorization' => 'Basic bGl2ZU10cjFDbGllbnQ6TU9USUI2ckRxbjNDenlNdDV2N2VHVmNhcWZqeDNlNWN1ZmlObG5uVFZHWVkzak5uRDJiWXJQS0JPTGRKMXVCRHpPWURTa1NVa1BObkxJdHd5bXRMZzlDUklLTmdIVW54MVlmdQ=='],
                'form_params' => [
                    'grant_type' => 'password',
                    'username' => 'nassef@ogold.app',
                    'password' => 'Nassefa!23123'
                ]
            ]);
            $data = $response->getBody()->getContents();
            return json_decode($data);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getOfferUUID($match_data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/documentation/config/api/partner/'.$match_data->partnerId.'/offers';
            $response = $client->request('GET', $url, [
                'headers' => ['Content-Type: application/x-www-form-urlencoded' , 'Authorization' => 'Bearer '. $match_data->access_token],
                ]);
            $data = $response->getBody()->getContents();
            $data_transform = json_decode($data);

            MatchData::updateOrCreate([
                'partner_id' => $match_data->partnerId
            ],[
                'access_token' => $match_data->access_token,
                'partner_id' => $match_data->partnerId,
                'offer_uuid' => $data_transform[0]->uuid
            ]);
            
        }catch(\Exception $e){
            return $e;
        }
    }

    public function loginAccount($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/manager/co-login';
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json' , 'Accept' => 'application/json'],
                'json' => [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'brokerId' => "97",
                ]
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            $dataMatch = MatchData::first();
            $dataMatch->update(['co_auth' => $decodedData->token , 'trading_api_token' => $decodedData->accounts[0]->tradingApiToken]);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function createUserInMatch($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $match_data = MatchData::first();
            $account = new \stdClass();
            $account->partnerId = $match_data->partner_id;
            $account->email = $data['email'];
            $account->name = $data['name'];
            $account->surname = $data['surname'];
            $account->phone = $data['phone'];
            $account->dateOfBirth = $data['dateOfBirth'];
            $account->country = $data['country'];
            $account->state = $data['state'];
            $account->city = $data['city'];
            $account->address = $data['address'];
            $account->bankAddress = $data['bankAddress'];
            $account->bankSwiftCode = $data['bankSwiftCode'];
            $account->bankAccount = $data['bankAccount'];
            $account->bankName = $data['bankName'];
            $account->accountName = $data['accountName'];
            $account->password = $data['password'];
            $account->role = "ROLE_USER";
            // $account->clientType = "Professional";
            $url = 'https://bo-mtrwl.match-trade.com/documentation/process/api/accounts/sync';
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json' , 'Authorization' => 'Bearer '. $match_data->access_token],
                'json' => [
                    'offerUuid' => $match_data->offer_uuid,
                    'createAsDepositedAccount' => true,
                    'account' => $account,
                ]
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            $decodedData->oneTimeToken;
            $dataMatch = MatchData::first();
            $dataMatch->update(['oneTimeToken' => $decodedData->oneTimeToken]);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateAccount($data , $model , $token)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/documentation/process/api/accounts?email='. $model->email.'&partnerId=97';
            $response = $client->request('PUT', $url, [
                'json' => $data,
                'headers' => ['Content-Type' => 'application/json' , 'Authorization' => 'Bearer '. $token->access_token]
            ]);
            
            $response->getBody()->getContents();
            return true;
        }catch(\Exception $e){
            return $e;
        }
    }
}