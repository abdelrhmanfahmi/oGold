<?php

namespace App\Services;

use App\Models\Order;
use App\Repository\Interfaces\OrderRepositoryInterface;

class TotalGramService {

    public function __construct(private OrderRepositoryInterface $orderRepository)
    {}

    public function calculateTotalService($orderId)
    {
        try{
            $totalGram = 0;
            $calcArr = [];
            $relations = ['products'];
            $i = 0;

            $data = $this->orderRepository->find($orderId , $relations);
            foreach($data->products as $dat){
                $calcArr[$i][$dat->pivot->quantity] = $dat['gram'];
                $i++;
            }

            foreach($calcArr as $smArr){
                foreach($smArr as $key => $value){
                    $totalGram += $key*$value;
                }
            }

            return $totalGram;
        }catch(\Exception $e){
            return $e;
        }
    }

    public function calculateTotalChargesService($orderId)
    {
        try{
            $totalCharge = 0;
            $relations = ['products'];

            $data = $this->orderRepository->find($orderId , $relations);
            foreach($data->products as $dat){
                $totalCharge += $dat->charge;
            }

            return $totalCharge;
        }catch(\Exception $e){
            return $e;
        }
    }


}
