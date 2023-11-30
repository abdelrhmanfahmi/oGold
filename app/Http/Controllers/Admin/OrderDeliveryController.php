<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveOrderRequest;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Services\MatchService;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct(
        private MatchService $matchService ,
        private OrderRepositoryInterface $orderRepository
        )
    {
        $this->middleware('auth:api');
    }

    public function checkOrderApproved(ApproveOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            $opendPositions = $this->matchService->getOpenedPositions($orderData->user_id);
            $getPositionsByOrder = $this->matchService->getPositionsByOrder($opendPositions);
            return $this->matchService->closePositionsByOrderDate($getPositionsByOrder , $orderData->user_id);
            return 1;
        }catch(\Exception $e){
            return $e;
        }
    }
}
