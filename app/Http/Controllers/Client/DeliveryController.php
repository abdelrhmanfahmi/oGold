<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientDeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Http\Resources\OrderDeliveryResource;
use App\Repository\DeliveryRepository;
use App\Repository\Interfaces\DeliveryRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Services\TotalGramService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository ,
        private DeliveryRepositoryInterface $deliveryRepository ,
        private TotalGramService $totalGramService
        )
    {
        $this->middleware('auth:api');
    }

    public function getOrdersDelivery()
    {
        try{
            //pagination is true or false
            $paginate = Request()->paginate ?? true;
            //check if requst has count
            $count = Request()->count ?? 10;
            //check if Product has relation
            $relations = ['products' , 'deliveries'];
            $ordersDelivery = $this->orderRepository->getDeliveryOrders($paginate , $count , $relations);
            return OrderDeliveryResource::collection($ordersDelivery);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function storeOrderDelivery(StoreClientDeliveryRequest $request)
    {
        try{
            $data = $request->validated();
            $data['status'] = 'pending';
            $data['total'] = $this->totalGramService->calculateTotalService($data['order_id']);
            $this->deliveryRepository->create($data);
            return response()->json(['message' => 'Your Order Stored Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }
}
