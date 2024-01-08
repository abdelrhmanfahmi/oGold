<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveDateRequest;
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
                return response()->json(['message' => 'Authentication Error !'],401);
            }else{
                $order = $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
                if($order['status'] == 'SUCCESS'){
                    $this->orderRepository->update($orderData,['status' => 'ready_to_picked']);
                    return response()->json(['message' => 'Order Approved Successfully'] , 200);
                }else{
                    return response()->json(['message' => 'Something Went Wrong !'] , 400);
                }
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function checkOrderApprovedByDate(ApproveDateRequest $request)
    {
        try{
            $data = $request->validated();
            $orderIds = $this->orderRepository->getOrdersIdsByDate($data['date'] , []);
            $this->getOrdersByDate($orderIds);
            return response()->json(['message' => 'Order Approved Successfully'] , 200);

        }catch(\Exception $e){
            return $e;
        }
    }

    protected function getOrdersByDate($ids)
    {
        foreach($ids as $id){
            $orderData = $this->orderRepository->find($id , []);
            $opendPositions = $this->matchService->getOpenedPositions($orderData->user_id);
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
            $this->orderRepository->update($orderData,['status' => 'ready_to_picked']);
        }
    }
}
