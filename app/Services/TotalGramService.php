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
            
            $data = $this->orderRepository->find($orderId , $relations);
            foreach($data->products as $dat){
                $calcArr[$dat->pivot->quantity] = $dat['gram'];
            }
            
            foreach($calcArr as $key => $value){
                $totalGram += $key*$value;
            }

            return $totalGram;
        }catch(\Exception $e){
            return $e;
        }
    }


}