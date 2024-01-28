<?php

namespace App\Services;

use App\Models\AddressBook;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use stdClass;


class ShipdayService {

    public function storeOrderDelivery($order, $user, $isCash)
    {
        $isCash = $isCash == 'cash' ? 'cash' : 'credit_card';
        $pickup_from = Setting::where('key' , 'pickup_address')->value('value');
        $delivery_fees = Setting::where('key' , 'shipping_fees')->value('value');
        $daysToDeliver = Setting::where('key' , 'delivery_period')->value('value');
        $currentDay = Carbon::now();
        $deliverExpectedDate = $currentDay->addDays((int)$daysToDeliver);
        $userAddress = AddressBook::where('user_id' , $user->id)->first();

        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic BgxsDwd00n.LNNn90QydrjgZ1K9dS13',
                'x-api-key' => env('SHIPDAY_API')
            ])->post("https://api.shipday.com/orders", [
                "orderNumber"=> strval($order->id),
                "customerName"=> $user->name,
                "customerAddress"=> $userAddress->address,
                "customerEmail"=> $user->email,
                "customerPhoneNumber"=> $user->phone,
                "restaurantName"=> "OGold",
                "restaurantAddress"=> $pickup_from,
                "restaurantPhoneNumber"=> "+14152392013",
                "expectedDeliveryDate"=> $deliverExpectedDate->format('Y-m-d'),
                "expectedPickupTime"=> Carbon::now()->format('H:i:s'),
                "expectedDeliveryTime"=> $deliverExpectedDate->format('H:i:s'),
                "deliveryFee"=> (int)$delivery_fees,
                "totalOrderCost"=> $order->total,
                "paymentMethod"=> $isCash
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function approveOrderReadyToPickup($orderId)
    {
        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic BgxsDwd00n.LNNn90QydrjgZ1K9dS13'
            ])->post("https://api.shipday.com/orders/".$orderId."/meta", [
                "readyToPickup"=> true
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

    public function storeOrderBuyAndDeliver($order, $user_id, $payment_method)
    {
        $user = User::where('id' , $user_id)->first();
        $pickup_from = Setting::where('key' , 'pickup_address')->value('value');
        $delivery_fees = Setting::where('key' , 'shipping_fees')->value('value');
        $daysToDeliver = Setting::where('key' , 'delivery_period')->value('value');
        $currentDay = Carbon::now();
        $deliverExpectedDate = $currentDay->addDays((int)$daysToDeliver);
        $userAddress = AddressBook::where('user_id' , $user->id)->first();

        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic BgxsDwd00n.LNNn90QydrjgZ1K9dS13',
                'x-api-key' => env('SHIPDAY_API')
            ])->post("https://api.shipday.com/orders", [
                "orderNumber"=> strval($order->id),
                "customerName"=> $user->name,
                "customerAddress"=> $userAddress->address,
                "customerEmail"=> $user->email,
                "customerPhoneNumber"=> $user->phone,
                "restaurantName"=> "OGold",
                "restaurantAddress"=> $pickup_from,
                "restaurantPhoneNumber"=> "+14152392013",
                "expectedDeliveryDate"=> $deliverExpectedDate->format('Y-m-d'),
                "expectedPickupTime"=> Carbon::now()->format('H:i:s'),
                "expectedDeliveryTime"=> $deliverExpectedDate->format('H:i:s'),
                "deliveryFee"=> (int)$delivery_fees,
                "totalOrderCost"=> $order->total,
                "paymentMethod"=> $payment_method
            ]);

            $decodedData = $response->json();
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

}
