<?php

use Illuminate\Support\Facades\Auth;

function getBuyPrice()
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
    }catch(\Exception $e){
        return $e;
    }
}

function generateSecretKey()
{
    $n=50;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;

}
