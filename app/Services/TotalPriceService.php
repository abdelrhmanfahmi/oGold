<?php

namespace App\Services;

use App\Models\Order;
use App\Repository\Interfaces\OrderRepositoryInterface;

class TotalPriceService {

    public function __construct(private OrderRepositoryInterface $orderRepository)
    {}

    public function calculateTotalService($orderId)
    {
        try{
            $totalPrice = 0;
            $calcArr = [];
            $relations = ['products'];
            
            $data = $this->orderRepository->find($orderId , $relations);
            foreach($data->products as $dat){
                $calcArr[$dat->pivot->quantity] = $dat['price'];
            }
            
            foreach($calcArr as $key => $value){
                $totalPrice += $key*$value;
            }

            return $totalPrice;
        }catch(\Exception $e){
            return $e;
        }
    }


}