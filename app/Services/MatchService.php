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
            $authUser = Auth::user();
            $authUser->update(['co_auth' => $decodedData->token , 'trading_api_token' => $decodedData->accounts[0]->tradingApiToken , 'trading_uuid' => $decodedData->accounts[0]->uuid]);
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
                    'Cookie' =>  'rt=' . Auth::user()->co_auth.';co-auth='.Auth::user()->co_auth,
                ]
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
            $buyPrice = $this->getMarketWatchSymbol();
            return ['buyResponse' => $decodedData , 'buy_price' => $buyPrice[0]->ask];
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
            // here make withdraw for authenticated user per request for sell gold
            $sellPriceNow = $this->getMarketWatchSymbol();

            //get net price of gold by multiply (gramGoldNow * $volumeOfUser Request)
            $priceWillWithdrawed = $volume * $sellPriceNow[0]->bid;

            //run withdraw request match service
            $token = $this->getAccessToken();
            $paymentGateWayUUid = $this->getPayment($token);
            $this->makeWithdraw($user_id, $priceWillWithdrawed, $token, $paymentGateWayUUid);
            return ['sellResponse' => $decodedData, 'sellPrice' => $sellPriceNow[0]->bid];


        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function closePositionsByOrderDatePerAdmin($arrayOfPositionsToClose, $user_id, $volume)
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
            // // here make withdraw for authenticated user per request for sell gold
            $sellPriceNow = $this->getMarketWatchSymbolPerUser($user_id);

            //get net price of gold by multiply (gramGoldNow * $volumeOfUser Request)
            $priceWillWithdrawed = $volume * $sellPriceNow[0]->bid;

            //run withdraw request match service
            $token = $this->getAccessToken();
            $paymentGateWayUUid = $this->getPayment($token);
            $this->makeWithdraw($user_id, $priceWillWithdrawed, $token, $paymentGateWayUUid);
            return $decodedData;

        }catch(\GuzzleHttp\Exception\BadResponseException $e){
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function getPositionsByOrderAdminRefinaryRole($dataOpenedPositions,$totalGold)
    {
        try{
            if(!is_string($dataOpenedPositions) && count($dataOpenedPositions->positions) > 0){
                foreach($dataOpenedPositions->positions as $d){
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
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.env('ACCOUNT_KYC').'/verification/'.env('FILE_KYC_FRONT');
            }else if($data['type'] == 'back'){
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.env('ACCOUNT_KYC').'/verification/'.env('FILE_KYC_BACK');
            }else{
                $url = 'https://platform.ogold.app/manager/verification/document?path=assets/'.env('BROKERID').'/account/'.env('ACCOUNT_KYC').'/verification/'.env('FILE_KYC_PROOF');
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
