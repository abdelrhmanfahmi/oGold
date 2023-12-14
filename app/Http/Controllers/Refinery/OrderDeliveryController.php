<?php

namespace App\Http\Controllers\Refinery;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveOrderRequest;
use App\Http\Resources\BuyGoldResourceAdmin;
use App\Http\Resources\DepositOrderResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellGoldResourceAdmin;
use App\Http\Resources\WithdrawOrderResource;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\SellGoldRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Services\MatchService;
use Illuminate\Http\Request;

class OrderDeliveryController extends Controller
{
    public function __construct(
        private MatchService $matchService ,
        private OrderRepositoryInterface $orderRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository,
        private BuyGoldRepositoryInterface $buyGoldRepository,
        private SellGoldRepositoryInterface $sellGoldRepository
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

    public function checkOrderApproved(ApproveOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $this->orderRepository->find($data['order_id'] , []);
            $opendPositions = $this->matchService->getOpenedPositions($orderData->user_id);
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$orderData->total);
            return $this->matchService->closePositionsByOrderDate($getPositionsByOrder , $orderData->user_id, $orderData->total);
            return 1;
        }catch(\Exception $e){
            return $e;
        }
    }
}
