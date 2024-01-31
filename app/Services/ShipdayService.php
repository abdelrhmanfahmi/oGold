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

    public function __construct(private MatchService $matchService)
    {}

    public function storeOrderDelivery($order, $user, $isCash)
    {
        $user = User::where('email' , env('EMAILUPDATEPRICE'))->first();
        $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        if(is_string($buyPrice)){
                $this->matchService->loginAccountForCronJob();
                $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        }
        $isCash == 'cash' ? 'cash' : 'credit_card';
        $pickup_from = Setting::where('key' , 'pickup_address')->value('value');
        $pickup_from_phone = Setting::where('key' , 'oGold-phone')->value('value');
        $delivery_fees = Setting::where('key' , 'shipping_fees')->value('value');
        $daysToDeliver = Setting::where('key' , 'delivery_period')->value('value');
        $currentDay = Carbon::now();
        $deliverExpectedDate = $currentDay->addDays((int)$daysToDeliver);
        $userAddress = AddressBook::where('user_id' , $order->user_id)->first();
        $totalAmount = ($order->total * $buyPrice[0]->ask) + $delivery_fees;

        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.env('SHIPDAY_API')
            ])->post("https://api.shipday.com/orders", [
                "orderNumber"=> strval($order->id),
                "customerName"=> $user->name,
                "customerAddress"=> $userAddress->address,
                "customerEmail"=> $user->email,
                "customerPhoneNumber"=> $user->phone,
                "restaurantName"=> "OGold",
                "restaurantAddress"=> $pickup_from,
                "restaurantPhoneNumber"=> $pickup_from_phone,
                "expectedDeliveryDate"=> $deliverExpectedDate->format('Y-m-d'),
                "expectedPickupTime"=> Carbon::now()->format('H:i:s'),
                "expectedDeliveryTime"=> $deliverExpectedDate->format('H:i:s'),
                "deliveryFee"=> (int)$delivery_fees,
                "totalOrderCost"=> $totalAmount,
                "paymentMethod"=> $isCash
            ]);

            $decodedData = $response->json();
            $order->update(['order_delivery_id' => $decodedData['orderId']]);
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
                'Authorization' => 'Basic '.env('SHIPDAY_API')
            ])->put("https://api.shipday.com/orders/".$orderId."/meta", [
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
        $user = User::where('email' , env('EMAILUPDATEPRICE'))->first();
        $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        if(is_string($buyPrice)){
                $this->matchService->loginAccountForCronJob();
                $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        }
        $user = User::where('id' , $user_id)->first();
        $pickup_from = Setting::where('key' , 'pickup_address')->value('value');
        $pickup_from_phone = Setting::where('key' , 'oGold-phone')->value('value');
        $delivery_fees = Setting::where('key' , 'shipping_fees')->value('value');
        $daysToDeliver = Setting::where('key' , 'delivery_period')->value('value');
        $currentDay = Carbon::now();
        $deliverExpectedDate = $currentDay->addDays((int)$daysToDeliver);
        $userAddress = AddressBook::where('user_id' , $order->user_id)->first();
        $totalAmount = ($order->total * $buyPrice[0]->ask) + $delivery_fees;

        try{
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic '.env('SHIPDAY_API')
            ])->post("https://api.shipday.com/orders", [
                "orderNumber"=> strval($order->id),
                "customerName"=> $user->name,
                "customerAddress"=> $userAddress->address,
                "customerEmail"=> $user->email,
                "customerPhoneNumber"=> $user->phone,
                "restaurantName"=> "OGold",
                "restaurantAddress"=> $pickup_from,
                "restaurantPhoneNumber"=> $pickup_from_phone,
                "expectedDeliveryDate"=> $deliverExpectedDate->format('Y-m-d'),
                "expectedPickupTime"=> Carbon::now()->format('H:i:s'),
                "expectedDeliveryTime"=> $deliverExpectedDate->format('H:i:s'),
                "deliveryFee"=> (int)$delivery_fees,
                "totalOrderCost"=> $totalAmount,
                "paymentMethod"=> $payment_method
            ]);

            $decodedData = $response->json();
            $order->update(['order_delivery_id' => $decodedData['orderId']]);
            return $decodedData;
        }catch (\GuzzleHttp\Exception\BadResponseException $e) {
            return $e->getResponse()->getBody()->getContents();
        }
    }

}
