<?php

namespace App\Services;

use App\Models\MatchData;
use Illuminate\Support\Facades\Auth;
use App\Models\MatchSymbol;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use stdClass;

class MatchService {

    public function getAccessToken()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/proxy/auth/oauth/token';
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type: application/x-www-form-urlencoded' , 'Authorization' => 'Basic '.env('OAUTH_TOKEN')],
                'form_params' => [
                    'grant_type' => env('GRANT_TYPE'),
                    'username' => env('USERNAMEGOLD'),
                    'password' => env('PASSWORDGOLD')
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
                    'brokerId' => env('BROKERID'),
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);

            //get refresh token
            $headers = $response->getHeaders();
            $stringCuts = $headers['Set-Cookie'][1];
            $refreshToken = substr($stringCuts,3 , -122);

            $authUser = Auth::user();
            $authUser->update(['refresh_token_id' => $refreshToken , 'co_auth' => $decodedData->token , 'trading_api_token' => $decodedData->accounts[0]->tradingApiToken , 'trading_uuid' => $decodedData->accounts[0]->uuid , 'client_trading_id' => $decodedData->accounts[0]->tradingAccountId]);
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

    public function sendVerificationCode($email)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/manager/user/verification-email';
            $objSended = new \stdClass();
            $objSended->partnerId = env('BROKERID');
            $objSended->email = $email;
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json' , 'Accept' => 'application/json'],
                'json' => $objSended,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function confirmEmailVerification($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/manager/user/verification-email/confirm';
            $objSendedConfirmation = new \stdClass();
            $objSendedConfirmation->partnerId = env('BROKERID');
            $objSendedConfirmation->email = $data['email'];
            $objSendedConfirmation->verificationCode = $data['code'];
            $response = $client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json' , 'Accept' => 'application/json'],
                'json' => $objSendedConfirmation,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function forgetPassowrdInMatch($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $account = new \stdClass();
            $account->partnerId = env('BROKERID');
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

    public function refreshTokenInMatch()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/manager/refresh-token';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json' ,
                    'Content-Type' => 'application/json' ,
                    'Cookie' =>  'rt=' . Auth::user()->refresh_token_id.';co-auth='.Auth::user()->co_auth,
                ]
            ]);
            $headers = $response->getHeaders();
            $stringCutsCoAuth = $headers['Set-Cookie'][0];
            $stringCutsRefreshToken = $headers['Set-Cookie'][1];
            $newCoAuth = substr($stringCutsCoAuth,8 , -79);
            $refreshToken = substr($stringCutsRefreshToken,3 , -122);
            $authUser = Auth::user();
            $authUser->update(['refresh_token_id' => $refreshToken , 'co_auth' => $newCoAuth]);
            return true;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function updateAccount($data , $model , $token)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/documentation/process/api/accounts?email='. $model->email.'&partnerId='.env('BROKERID');
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
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/quotations?symbols=GoldGram24c';
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

    public function getMarketWatchSymbolMarkup()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/quotations?symbols=GoldGram24c&applyMarkup=true';
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

    public function getMarketWatchSymbolPerUser($user_id)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/quotations?symbols=GoldGram24c';
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
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/position/open';
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
            $buyPrice = $this->getMarketWatchSymbolMarkup();
            return ['buyResponse' => $decodedData , 'buy_price' => $buyPrice[0]->ask];
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function openPositionForUser($data)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $userRecieved = User::findOrFail($data['recieved_user_id']);
            $dataDahab = new \stdClass();
            $dataDahab->instrument = 'GoldGram24c';
            $dataDahab->orderSide = 'BUY';
            $dataDahab->volume = $data['volume'];
            $dataDahab->slPrice = 0;
            $dataDahab->tpPrice = 0;
            $dataDahab->isMobile = true;
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/position/open';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Auth-trading-api' => $userRecieved->trading_api_token,
                    'Cookie' => 'co-auth=' . $userRecieved->co_auth
                ],
                'json' => $dataDahab,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            $buyPrice = $this->getMarketWatchSymbolMarkup();
            return ['buyResponse' => $decodedData , 'buy_price' => $buyPrice[0]->ask];
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function makeOrderSubmitForBuyGold($orderStringId,$data)
    {
        try{
            $dataToken = $this->loginAsManager();
            $userRecieved = User::findOrFail($data['recieved_user_id']);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/order/submit", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "orderMask" => [
                    "symbol"=> "GoldGram24c",
                    "clientId" => $userRecieved->client_trading_id,
                    "clientOrderId"=> $orderStringId,
                    "ordType"=> 0,
                    "volume"=> $data['volume']
                ]
            ]);

            return $response->json();
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
//            $dataDahab->positionId = $data['positionId'];

            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/positions/close';
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
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/balance';
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
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/open-positions';
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

    public function getPositionsByOrder($dataOpenedPositions,$totalGoldPending,$data)
    {
        try{
            $checkHasTotalVolume= 0;
            $arrClosedPositions = [];

            foreach($dataOpenedPositions->positions as $d){
                $checkHasTotalVolume += $d->volume;
            }

            //net gold that user can sell from them
            $totalGoldCanSold = $checkHasTotalVolume - $totalGoldPending;

            if(count($dataOpenedPositions->positions) == 0){ // check if user has positions to close
                return 0;
            }else if($data['volume'] > $checkHasTotalVolume){ // check if user request to sell gold bigger than he has
                return -1;
            }else if($data['volume'] > $totalGoldCanSold){
                // check if user request to sell gold that (total gold he has 200 gold but in order pending total 100 gold , then he can't sell bigger than 100 gold)
                return -2;
            }else{
                foreach($dataOpenedPositions->positions as $d){
                    $checkHasTotalVolume += $d->volume;
                    $newObj = new \stdClass();
                    $newObj->openTime = $d->openTime;
                    $newObj->positionId = $d->id;
                    $newObj->volume = $d->volume;
                    $newObj->orderSide = $d->side;
                    $newObj->instrument = $d->symbol;
                    $arrClosedPositions[] = $newObj;
                }
                $sortedObjects = collect($arrClosedPositions)->sortBy('openTime')->values()->all();

                //here logic for check quanity sended from mobile to close positions according to it
                $arrToClosePositionsPerQuantity = [];
                $totalVolumePerQuantity = 0;
                $collectedNotClosed = 0;
                $reminderVolume = 0;
                $positionId = '';
                foreach($sortedObjects as $sorted){
                    $totalVolumePerQuantity += $sorted->volume;
                    if($totalVolumePerQuantity <= $data['volume']){
                        array_push($arrToClosePositionsPerQuantity , $sorted);
                        $collectedNotClosed += $sorted->volume;
                    }else{
                        $reminderVolume = $data['volume'] - $collectedNotClosed;
                        $positionId = $sorted->positionId;
                        break;
                    }
                }
                return ['originalClose' => $arrToClosePositionsPerQuantity , 'reminder' => $reminderVolume , 'positionId' => $positionId];
            }
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePositionsByOrderDatePerUser($arrayOfPositionsToClose, $user_id, $volume)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/positions/close';

            if(count($arrayOfPositionsToClose['originalClose']) > 0){
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'co-auth' => $user->co_auth,
                        'Auth-trading-api' => $user->trading_api_token,
                        'Cookie' => 'co-auth='. $user->co_auth
                    ],
                    'json' => $arrayOfPositionsToClose['originalClose'],
                ]);
                $result = $response->getBody()->getContents();
                $decodedData = json_decode($result);
            }

            if($arrayOfPositionsToClose['reminder'] != 0){
                $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/position/close-partially';
                $dataDahabToClosePartialy = new \stdClass();
                $dataDahabToClosePartialy->instrument = 'GoldGram24c';
                $dataDahabToClosePartialy->orderSide = 'BUY';
                $dataDahabToClosePartialy->volume = $arrayOfPositionsToClose['reminder'];
                $dataDahabToClosePartialy->positionId = $arrayOfPositionsToClose['positionId'];
                $dataDahabToClosePartialy->isMobile = false;
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'co-auth' => $user->co_auth,
                        'Auth-trading-api' => $user->trading_api_token,
                        'Cookie' => 'co-auth='. $user->co_auth
                    ],
                    'json' => $dataDahabToClosePartialy,
                ]);
                $result = $response->getBody()->getContents();
                $decodedData = json_decode($result);
            }
            $sellPrice = $this->getMarketWatchSymbolMarkup();
            return ['sellResponse' => $decodedData , 'sellPrice' => $sellPrice[0]->bid];


        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getPositionsByOrderAdminRefinaryRole($dataOpenedPositions,$totalGold)
    {
        try{
            if(!is_string($dataOpenedPositions) && count($dataOpenedPositions['positionInfo']) > 0){
                foreach($dataOpenedPositions['positionInfo'] as $d){
                    $newObj = new \stdClass();
                    $newObj->openTime = $d['openTime'];
                    $newObj->positionId = $d['rMask']['simple']['clientOrderId'];
                    $newObj->volume = $d['volume'];
                    $newObj->orderSide = 'BUY';
                    $newObj->instrument = 'GoldGram24c';
                    $arrClosedPositions[] = $newObj;
                }
                $sortedObjects = collect($arrClosedPositions)->sortBy('openTime')->values()->all();

                //here logic for check quanity sended from mobile to close positions according to it
                $arrToClosePositionsPerQuantity = [];
                $totalVolumePerQuantity = 0;
                $collectedNotClosed = 0;
                $reminderVolume = 0;
                $positionId = '';
                foreach($sortedObjects as $sorted){
                    $totalVolumePerQuantity += $sorted->volume;
                    if($totalVolumePerQuantity <= $totalGold){
                        array_push($arrToClosePositionsPerQuantity , $sorted);
                        $collectedNotClosed += $sorted->volume;
                    }else{
                        $reminderVolume = $totalGold - $collectedNotClosed;
                        $positionId = $sorted->positionId;
                        break;
                    }
                }
                return ['originalClose' => $arrToClosePositionsPerQuantity , 'reminder' => $reminderVolume , 'positionId' => $positionId];
            }else if(is_string($dataOpenedPositions)){
                return -1;
            }else{
                return 0;
            }


        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePositionsByOrderDatePerAdmin($arrayOfPositionsToClose, $user_id, $volume)
    {
        try{
            $dataToken = $this->loginAsManager();
            $user = User::findOrFail($user_id);

            if(count($arrayOfPositionsToClose['originalClose']) > 0){
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])->post("https://grpc-mtrwl.match-trade.com/v1/position/close", [
                    "auth" => [
                        "managerID" => env('MANAGER_ID'),
                        "token" => $dataToken->token
                    ],
                    "clientPositionsToClose" => [
                        [
                            "comment" => "string",
                            "positionOrderId"=> $arrayOfPositionsToClose['originalClose'][0]->positionId,
                            "clientId"=> $user->client_trading_id,
                            "instrument"=> "GoldGram24c"
                        ]
                    ]
                ]);
                $decodedData = $response->json();
            }

            if($arrayOfPositionsToClose['reminder'] != 0){
                //here to get all positions of auth user and filter on array of all positions to get id that close partialy and edit volume of it
                $dataAll = $this->getAllPositionForAuthUser($user_id);
                $specific_value = $arrayOfPositionsToClose['positionId'];
                $filtered_array = array_filter($dataAll['positionInfo'], function ($obj) use ($specific_value) {
                    return $obj['rMask']['simple']['clientOrderId'] == $specific_value;
                });

                //here edit volume in reminder to close total position of it
                $editPosition = $this->editVolumeForUser($user_id , array_values($filtered_array)[0]['id'] , $arrayOfPositionsToClose['positionId'] , $arrayOfPositionsToClose['reminder']);
                if($editPosition['status'] == 'EDIT_POSITION_SUCCESS'){
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])->post("https://grpc-mtrwl.match-trade.com/v1/position/close", [
                        "auth" => [
                            "managerID" => env('MANAGER_ID'),
                            "token" => $dataToken->token
                        ],
                        "clientPositionsToClose" => [
                            [
                                "comment" => "string",
                                "positionOrderId"=> $arrayOfPositionsToClose['positionId'],
                                "clientId"=> $user->client_trading_id,
                                "instrument"=> "GoldGram24c"
                            ]
                        ]
                    ]);

                    $decodedData = $response->json();
                }
            }

            return $decodedData;

        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getAllPositionForAuthUser($user_id)
    {
        try{
            $dataToken = $this->loginAsManager();
            $user = User::findOrFail($user_id);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/position/getAll", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "clientIds" => [
                    $user->client_trading_id
                ]
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function editVolumeForUser($user_id , $idForPositinEdit , $positionId , $reminder)
    {
        try{
            $dataToken = $this->loginAsManager();
            $user = User::findOrFail($user_id);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/position/editVolume", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "newValue"=> (int) $reminder,
                "positionOrderId"=> $positionId,
                "clientId"=> $user->client_trading_id,
                "instrument"=> "GoldGram24c",
                "partialPositionId"=> (int) $idForPositinEdit
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePositionsByOrderDateForGift($arrayOfPositionsToClose, $user_id, $volume)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/positions/close';

            if(count($arrayOfPositionsToClose['originalClose']) > 0){
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'co-auth' => $user->co_auth,
                        'Auth-trading-api' => $user->trading_api_token,
                        'Cookie' => 'co-auth='. $user->co_auth
                    ],
                    'json' => $arrayOfPositionsToClose['originalClose'],
                ]);
                $result = $response->getBody()->getContents();
                $decodedData = json_decode($result);
            }

            if($arrayOfPositionsToClose['reminder'] != 0){
                $url = 'https://platform.ogold.app/mtr-api/'.env('SYSTEMUUID').'/position/close-partially';
                $dataDahabToClosePartialy = new \stdClass();
                $dataDahabToClosePartialy->instrument = 'GoldGram24c';
                $dataDahabToClosePartialy->orderSide = 'BUY';
                $dataDahabToClosePartialy->volume = $arrayOfPositionsToClose['reminder'];
                $dataDahabToClosePartialy->positionId = $arrayOfPositionsToClose['positionId'];
                $dataDahabToClosePartialy->isMobile = false;
                $response = $client->request('POST', $url, [
                    'headers' => [
                        'co-auth' => $user->co_auth,
                        'Auth-trading-api' => $user->trading_api_token,
                        'Cookie' => 'co-auth='. $user->co_auth
                    ],
                    'json' => $dataDahabToClosePartialy,
                ]);
                $result = $response->getBody()->getContents();
                $decodedData = json_decode($result);
            }

            return ['sellResponse' => $decodedData];
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function loginAsManager()
    {
        try{
            $client = new \GuzzleHttp\Client();
            $manager = new \stdClass();
            $manager->password = env('MANAGER_PASSWORD');
            $manager->managerID = env('MANAGER_ID');
            $url = 'https://grpc-mtrwl.match-trade.com/v1/register/register';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => $manager
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function creditOut($price)
    {
        try{
            $dataToken = $this->loginAsManager();
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/balance/creditOut", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "comment" => 'string',
                "amount" => (int) ceil($price),
                "clientId" => Auth::user()->client_trading_id
            ]);

            return $response->json();

        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function creditIn($price,$recieved_id)
    {
        try{
            $dataToken = $this->loginAsManager();
            $userRecieved = User::findOrFail($recieved_id);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/balance/creditIn", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "comment" => 'string',
                "amount" => (int) ceil($price),
                "clientId" => $userRecieved->client_trading_id
            ]);

            return $response->json();
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function withdrawMoneyManager($price)
    {
        try{
            $dataToken = $this->loginAsManager();
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/balance/withdrawMoney", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "amount" => (int) ceil($price)*100,
                "clientId" => Auth::user()->client_trading_id
            ]);

            return $response->json();
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function depositMoneyManager($price,$recieved_id)
    {
        try{
            $dataToken = $this->loginAsManager();
            $userRecieved = User::findOrFail($recieved_id);
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post("https://grpc-mtrwl.match-trade.com/v1/balance/depositMoney", [
                "auth" => [
                    "managerID" => env('MANAGER_ID'),
                    "token" => $dataToken->token
                ],
                "amount" => (int) ceil($price) * 100,
                "clientId" => $userRecieved->client_trading_id
            ]);

            return $response->json();
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

    public function getPayment($token)
    {
        try{
            $client = new \GuzzleHttp\Client();
            $url = 'https://bo-mtrwl.match-trade.com/documentation/payment/partner/'.env('BROKERID').'/payment-gateways';
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token->access_token
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData[2]->uuid;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function makeWithdraw($user_id, $data , $token , $paymentGateWay)
    {
        try{
            $user = User::findOrFail($user_id);
            $client = new \GuzzleHttp\Client();
            $dataWithdraw = new \stdClass();
            $dataWithdraw->paymentGatewayUuid = $paymentGateWay;
            $dataWithdraw->tradingAccountUuid = $user->trading_uuid;
            $dataWithdraw->currency = 'GoldGram24c';
            $dataWithdraw->amount = $data;
            $dataWithdraw->netAmount = $data;
            $dataWithdraw->remark = 'test';

            $url = 'https://bo-mtrwl.match-trade.com/documentation/payment/api/partner/'.env('BROKERID').'/withdraws/manual';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token->access_token
                ],
                'json' => $dataWithdraw,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function makeDeposit($data , $token , $paymentGateWay)
    {
        try{
            $user = User::findOrFail($data['user_id']);
            $client = new \GuzzleHttp\Client();
            $dataDeposit = new \stdClass();
            $dataDeposit->paymentGatewayUuid = $paymentGateWay;
            $dataDeposit->tradingAccountUuid = $user->trading_uuid;
            $dataDeposit->currency = $data['currency'];
            $dataDeposit->amount = $data['amount'];
            $dataDeposit->netAmount = $data['amount'];
            $dataDeposit->remark = 'test';

            $url = 'https://bo-mtrwl.match-trade.com/documentation/payment/api/partner/'.env('BROKERID').'/deposits/manual';
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token->access_token
                ],
                'json' => $dataDeposit,
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function uploadFileKYC($data)
    {
        try{
            $user = Auth::user();
            $client = new \GuzzleHttp\Client();
            if($data['type'] == 'front'){
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.$user->trading_uuid.'/verification/'.env('FILE_KYC_FRONT');
            }else if($data['type'] == 'back'){
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.$user->trading_uuid.'/verification/'.env('FILE_KYC_BACK');
            }else{
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.$user->trading_uuid.'/verification/'.env('FILE_KYC_PROOF');
            }
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Cookie' => 'co-auth=' . $user->co_auth
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($data['file']->getPathname(), 'r'),
                        'filename' => $data['file']->getClientOriginalName(),
                    ],
                ],
            ]);
            $result = $response->getBody()->getContents();
            $decodedData = json_decode($result);
            return $decodedData;
        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }
}
