<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use stdClass;


class ShipdayService {

    public function storeOrderDelivery()
    {
        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic BgxsDwd00n.LNNn90QydrjgZ1K9dS13',
                'x-api-key' => env('SHIPDAY_API')
            ])->post("https://api.shipday.com/orders", [
                "orderNumber"=> "99qT5A",
                "customerName"=> "Mr. Jhon Mason",
                "customerAddress"=> "556 Crestlake Dr, San Francisco, CA 94132, USA",
                "customerEmail"=> "jhonMason@gmail.com",
                "customerPhoneNumber"=> "+14152392212",
                "restaurantName"=> "Popeyes Louisiana Kitchen",
                "restaurantAddress"=> "890 Geneva Ave, San Francisco, CA 94112, United States",
                "restaurantPhoneNumber"=> "+14152392013",
                "expectedDeliveryDate"=> "2021-06-03",
                "expectedPickupTime"=> "17:45:00",
                "expectedDeliveryTime"=> "19:22:00",
                "pickupLatitude"=> 41.53867,
                "pickupLongitude"=> -72.0827,
                "deliveryLatitude"=> 41.53867,
                "deliveryLongitude"=> -72.0827,
                "tips"=> 2.5,
                "tax"=> 1.5,
                "discountAmount"=> 1.5,
                "deliveryFee"=> 3,
                "totalOrderCost"=> 13.47,
                "deliveryInstruction"=> "fast",
                "orderSource"=> "Seamless",
                "additionalId"=> "4532",
                "clientRestaurantId"=> 12,
                "paymentMethod"=> "credit_card",
                "creditCardType"=> "visa",
                "creditCardId"=> 1234
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function approveOrderReadyToPickup()
    {
        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic BgxsDwd00n.LNNn90QydrjgZ1K9dS13'
            ])->post("https://api.shipday.com/orders", [
                "readyToPickup"=> true
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

}
