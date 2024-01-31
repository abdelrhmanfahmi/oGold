<?php

namespace App\Http\Controllers\Refinery;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveDateRequest;
use App\Http\Requests\ApproveOrderRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\UpdateOrderDeliveryRequest;
use App\Http\Resources\BuyGoldResourceAdmin;
use App\Http\Resources\DepositOrderResource;
use App\Http\Resources\OrderDateResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellGoldResourceAdmin;
use App\Http\Resources\UserResource;
use App\Http\Resources\WithdrawOrderResource;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\SellGoldRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ShipdayService;

class OrderDeliveryController extends Controller
{
    public function __construct(
        private MatchService $matchService ,
        private OrderRepositoryInterface $orderRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository,
        private BuyGoldRepositoryInterface $buyGoldRepository,
        private SellGoldRepositoryInterface $sellGoldRepository,
        private ShipdayService $shipdayService,
        private UserRepositoryInterface $userRepository
    )
    {
        $this->middleware('auth:api');
    }

    public function indexOrders(Request $request)
    {
        try{
            $paginate = Request()->paginate ?? true;
            $count = Request()->count ?? 10;

            if($request->type == 'withdraws'){
                $relations = ['client'];
                $withdraws = $this->withdrawRepository->all($count , $paginate , $relations);
                return WithdrawOrderResource::collection($withdraws);
            }

            if($request->type == 'deposits'){
                $relations = ['client'];
                $deposits = $this->depositRepository->all($count , $paginate , $relations);
                return DepositOrderResource::collection($deposits);
            }

            if($request->type == 'delivery'){
                $relations = ['products' , 'client' , 'address_book'];
                $orders = $this->orderRepository->all($count , $paginate , $relations);
                return OrderResource::collection($orders);
            }

            if($request->type == 'buy_golds'){
                $relations = ['client'];
                $orders = $this->buyGoldRepository->all($count , $paginate , $relations);
                return BuyGoldResourceAdmin::collection($orders);
            }

            if($request->type == 'sell_golds'){
                $relations = ['client'];
                $orders = $this->sellGoldRepository->all($count , $paginate , $relations);
                return SellGoldResourceAdmin::collection($orders);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function indexByData()
    {
        try{
            $count = Request()->count ?? 10;
            $paginate = Request()->paginate ?? true;
            $ordersByDate = $this->orderRepository->getDataByOrdersDate($count, $paginate, []);
            return OrderDateResource::collection($ordersByDate);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getOrdersPerDate(Request $request)
    {
        try{
            $relations = ['client','products','address_book'];
            $ordersPerDate = $this->orderRepository->getOrdersPerSpecificDate($request->date,$relations);
            return OrderResource::collection($ordersPerDate);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function checkOrderApproved(ApproveOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            if($orderData->status != 'pending'){
                return response()->json(['message' => 'this order approved before from admin!'] , 400);
            }
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
                    $this->shipdayService->approveOrderReadyToPickup($orderData->order_delivery_id);
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
            $this->shipdayService->approveOrderReadyToPickup($orderData->order_delivery_id);
            $this->orderRepository->update($orderData,['status' => 'ready_to_picked']);
        }
    }

    public function getUserInfo()
    {
        try{
            return UserResource::make(Auth::user());
        }catch(\Exception $e){
            return $e;
        }
    }

    public function cancelOrderDeliveryRefinary(CancelOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            $this->orderRepository->update($orderData , ['status' => 'canceled']);
            return response()->json(['message' => 'Order Cancelled Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateOrderDeliveryStatusRefinary(UpdateOrderDeliveryRequest $request , $order_id)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($order_id , []);
            $this->orderRepository->update($orderData , ['status' => $data['status']]);
            return response()->json(['message' => 'Order Updated Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }
}
