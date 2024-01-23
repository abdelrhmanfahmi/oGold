<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveDateRequest;
use App\Http\Requests\ApproveOrderRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Services\MatchService;
use App\Services\ShipdayService;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct(
        private MatchService $matchService ,
        private OrderRepositoryInterface $orderRepository,
        private ShipdayService $shipdayService
        )
    {
        $this->middleware('auth:api');
    }

    public function checkOrderApproved(ApproveOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            // $opendPositions = $this->matchService->getOpenedPositions($orderData->user_id);
            $opendPositions = $this->matchService->getAllPositionForAuthUser($orderData->user_id);
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            if($getPositionsByOrder == 0){
                return response()->json(['message' => 'there is no opened positions'],400);
            }else if($getPositionsByOrder == -1){
                return response()->json(['message' => 'Authentication Error !'],401);
            }else{
                $order = $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
                if($order['status'] == 'SUCCESS'){
                    $priceWillBeDeducted = $orderData->total * $orderData->buy_price;
                    $this->matchService->withdrawMoneyManager($priceWillBeDeducted , $orderData->user_id);
                    //here call api for approve order to ready to pick up delivery integration
                    // $this->shipdayService->approveOrderReadyToPickup();
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
            $opendPositions = $this->matchService->getAllPositionForAuthUser($orderData->user_id);
            // for($i = 0 ; $i < count($opendPositions['positionInfo']) ; $i++){
                $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
                $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
            // }
            $priceWillBeDeducted = $orderData->total * $orderData->buy_price;
            $this->matchService->withdrawMoneyManager($priceWillBeDeducted , $orderData->user_id);
            //here call api for approve order to ready to pick up delivery integration
            // $this->shipdayService->approveOrderReadyToPickup();
            $this->orderRepository->update($orderData,['status' => 'ready_to_picked']);
        }
    }

    public function cancelOrderDelivery(CancelOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            $this->authorize('update',$orderData);
            $this->orderRepository->update($orderData , ['status' => 'canceled']);
            return response()->json(['message' => 'Order Cancelled Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }
}
