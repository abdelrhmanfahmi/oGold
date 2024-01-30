<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveDateRequest;
use App\Http\Requests\ApproveOrderRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use App\Services\ShipdayService;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct(
        private MatchService $matchService ,
        private OrderRepositoryInterface $orderRepository,
        private ShipdayService $shipdayService,
        private UserRepositoryInterface $userRepository
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
            if(isset($opendPositions['status'])){
                return response()->json(['message' => 'Authentication error ! manager must be log in'] , 401);
            }
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            if($getPositionsByOrder == 0){
                return response()->json(['message' => 'there is no opened positions'],400);
            }else if($getPositionsByOrder == -1){
                return response()->json(['message' => 'Authentication Error !'],401);
            }else{
                $order = $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);
                if($order['status'] == 'SUCCESS'){
                    $user = $this->userRepository->findByEmail(env('EMAILUPDATEPRICE'));
                    $sellPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
                    if(!is_string($sellPrice)){
                        $priceWillBeDeducted = $orderData->total * $sellPrice[0]->bid;
                    }else{
                        $this->matchService->loginAccountForCronJob();
                        $sellPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
                        $priceWillBeDeducted = $orderData->total * $sellPrice[0]->bid;
                    }

                    $this->matchService->withdrawMoneyManager($priceWillBeDeducted , $orderData->user_id);
                    //here call api for approve order to ready to pick up delivery integration
                    $this->shipdayService->approveOrderReadyToPickup($data['order_id']);
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
            $checkAuth = $this->getOrdersByDate($orderIds);
            if($checkAuth == -1){
                return response()->json(['message' => 'Authentication error !'] , 401);
            }
            if($checkAuth == -2){
                return response()->messgae(['message' => 'Authentication error ! manager must be log in'] , 401);
            }
            return response()->json(['message' => 'Order Approved Successfully'] , 200);

        }catch(\Exception $e){
            return $e;
        }
    }

    protected function getOrdersByDate($ids)
    {
        $user = $this->userRepository->findByEmail(env('EMAILUPDATEPRICE'));
        $sellPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        if(is_string($sellPrice)){
            return -1;
        }
        foreach($ids as $id){
            $orderData = $this->orderRepository->find($id , []);
            $opendPositions = $this->matchService->getAllPositionForAuthUser($orderData->user_id);
            if(isset($opendPositions['status'])){
                return -2;
            }
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $orderData->user_id, $orderData->total);

            if(!is_string($sellPrice)){
                $priceWillBeDeducted = $orderData->total * $sellPrice[0]->bid;
            }else{
                $this->matchService->loginAccountForCronJob();
                $sellPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
                $priceWillBeDeducted = $orderData->total * $sellPrice[0]->bid;
            }

            $this->matchService->withdrawMoneyManager($priceWillBeDeducted , $orderData->user_id);
            //here call api for approve order to ready to pick up delivery integration
            $this->shipdayService->approveOrderReadyToPickup($id);
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
