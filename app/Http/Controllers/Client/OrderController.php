<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelOrderRequest;
use App\Http\Resources\BuyGoldResource;
use App\Http\Resources\SellGoldResource;
use App\Http\Resources\ClientDepositResource;
use App\Http\Resources\ClientWithdrawResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderUserResource;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\SellGoldRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository,
        private BuyGoldRepositoryInterface $buyGoldRepository,
        private SellGoldRepositoryInterface $sellGoldRepository,
    )
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try{
            // $paginate = Request()->paginate ?? true;
            $paginate = false;
            $count = Request()->count ?? 10;

            if($request->type == 'withdraws'){
                $withdraws = $this->withdrawRepository->allForUsers($count , $paginate , []);
                return ClientWithdrawResource::collection($withdraws);
            }

            if($request->type == 'deposits'){
                $deposits = $this->depositRepository->allForUsers($count , $paginate , []);
                return ClientDepositResource::collection($deposits);
            }

            if($request->type == 'delivery'){
                $relations = ['products' , 'address_book'];
                $orders = $this->orderRepository->allForUsers($count , $paginate , $relations);
                return OrderUserResource::collection($orders);
            }

            if($request->type == 'buy_golds'){
                $orders = $this->buyGoldRepository->allForUsers($count , $paginate , []);
                return BuyGoldResource::collection($orders);
            }

            if($request->type == 'sell_golds'){
                $orders = $this->sellGoldRepository->allForUsers($count , $paginate , []);
                return SellGoldResource::collection($orders);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function cancelOrder(CancelOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $model = $this->orderRepository->find($data['order_id'],[]);
            $this->authorize('update',$model);
            $this->orderRepository->update($model,['status' => 'canceled']);
            return response()->json(['message' => 'Order Canceled Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
