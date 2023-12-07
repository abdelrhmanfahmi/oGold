<?php

namespace App\Services;

use App\Models\MatchData;
use Illuminate\Support\Facades\Auth;
use App\Models\MatchSymbol;
use App\Models\User;
use Carbon\Carbon;

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
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
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

        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
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
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            $authUser = Auth::user();
            $authUser->update(['co_auth' => $decodedData->token , 'trading_api_token' => $decodedData->accounts[0]->tradingApiToken]);
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
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
            // $account->dateOfBirth = $data['dateOfBirth'];
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
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function forgetPassowrdInMatch($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $account = new \stdClass();
            $account->partnerId = 97;
            $account->email = $data['email'];
            $account->systemLink = "https://platform.ogold.app/change-password";
            $url = 'https://platform.ogold.app/manager/user/request-password-reset';
            $response = $client->request('POST', $url, [
                'json' => $account,
                'headers' => ['Content-Type' => 'application/json']
            ]);

            $response->getBody()->getContents();
            return true;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function changePassowrdInMatch($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $account = new \stdClass();
            $account->currentPassword = $data['current_password'];
            $account->newPassword = $data['password'];
            $url = 'https://platform.ogold.app/manager/user/change-password';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json' ,
                    'co-auth' => 'Bearer ' . Auth::user()->co_auth,
                    'Cookie' =>  'co-auth=' . Auth::user()->co_auth,
                ],
                'json' => $account,
            ]);
            $response->getBody()->getContents();
            return true;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
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
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getMarketWatchSymbol()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/quotations?symbols=GoldGram24c';
            $response = $client->request('GET', $url, [
                'headers' => [
                    'co-auth' => Auth::user()->co_auth,
                    'Auth-trading-api' => Auth::user()->trading_api_token,
                    'Cookie' => 'co-auth='. Auth::user()->co_auth
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function openPosition($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $dataDahab = new \stdClass();
            $dataDahab->instrument = $data['symbol'];
            $dataDahab->orderSide = 'BUY';
            $dataDahab->volume = $data['volume'];
            $dataDahab->slPrice = 0;
            $dataDahab->tpPrice = 0;
            $dataDahab->isMobile = true;
            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/position/open';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Auth-trading-api' => Auth::user()->trading_api_token,
                    'Cookie' => 'co-auth=' . Auth::user()->co_auth
                ],
                'json' => $dataDahab,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePosition($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $dataDahab = new \stdClass();
            $dataDahab->instrument = $data['symbol'];
            $dataDahab->orderSide = 'BUY';
            $dataDahab->volume = $data['volume'];
            $dataDahab->positionId = $data['positionId'];

            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/position/close';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Auth-trading-api' => Auth::user()->trading_api_token,
                    'Cookie' => 'co-auth=' . Auth::user()->co_auth
                ],
                'json' => $dataDahab,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getBalanceMatch()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/balance';
            $response = $client->request('GET', $url, [
                'headers' => [
                    'co-auth' => Auth::user()->co_auth,
                    'Auth-trading-api' => Auth::user()->trading_api_token,
                    'Cookie' => 'co-auth='. Auth::user()->co_auth
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getOpenedPositions($user_id)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/open-positions';
            $response = $client->request('GET', $url, [
                'headers' => [
                    'co-auth' => $user->co_auth,
                    'Auth-trading-api' => $user->trading_api_token,
                    'Cookie' => 'co-auth='. $user->co_auth
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getPositionsByOrder($data)
    {
        try{
            $arrClosedPositions = [];
            foreach($data->positions as $d){
                $newObj = new \stdClass();
                $newObj->openTime = $d->openTime;
                $newObj->positionId = $d->id;
                $newObj->volume = $d->volume;
                $newObj->orderSide = $d->side;
                $newObj->instrument = $d->symbol;
                $arrClosedPositions[] = $newObj;
            }
            $sortedObjects = collect($arrClosedPositions)->sortBy('openTime')->values()->all();
            return $sortedObjects;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePositionsByOrderDate($openedPositionsToClose , $user_id)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/7d0f0ade-3dc0-4c0e-884e-08d7b7961926/positions/close';

            $response = $client->request('POST', $url, [
                'headers' => [
                    'co-auth' => $user->co_auth,
                    'Auth-trading-api' => $user->trading_api_token,
                    'Cookie' => 'co-auth='. $user->co_auth
                ],
                'json' => $openedPositionsToClose,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;

        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function saveSymbols($symbols)
    {
        try{
            $arrInsert = [];
            foreach($symbols as $symbol){
                $arrInsert['symbol'] = $symbol->symbol;
                $arrInsert['alias'] = $symbol->alias;
                $arrInsert['created_at'] = Carbon::now();
                $arrInsert['updated_at'] = Carbon::now();
            }
            MatchSymbol::updateOrInsert([
                'symbol' => $arrInsert['symbol']
            ] , $arrInsert);
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }
}
