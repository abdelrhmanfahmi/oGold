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
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            if($getPositionsByOrder == 0){
                return response()->json(['message' => 'there is no opened positions'],400);
            }else if($getPositionsByOrder == -1){
                return response()->json(['message' => 'Check Match Service Logged In'],403);
            }else{
                $order = $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
                $this->orderRepository->update($orderData , ['is_approved' => '1']);
                return response()->json(['message' => $order]);
            }
        }catch(\Exception $e){
            return $e;
        }
    }
}
