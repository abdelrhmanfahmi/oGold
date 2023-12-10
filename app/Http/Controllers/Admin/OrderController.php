<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\DepositOrderResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\WithdrawOrderResource;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Services\TotalGramService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository,
        private TotalGramService $totalGramService
    )
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
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


        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $request->only('user_id','address_book_id','status');
            $order = $this->orderRepository->create($orderData);

            $order->products()->attach($data['products']);
            $data['total'] = $this->totalGramService->calculateTotalService($order->id);
            $updatedOrder = $this->orderRepository->find($order->id ,[]);
            $this->orderRepository->update($updatedOrder , ['total' => $data['total']]);
            return response()->json(['message' => 'Order Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $relations = ['client' , 'products' , 'address_book'];
            $order = $this->orderRepository->find($id , $relations);
            return OrderResource::make($order);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateOrderRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $orderData = $request->only('user_id','address_book_id','status');
            $model = $this->orderRepository->find($id , []);
            $order = $this->orderRepository->update($model , $orderData);

            $order->products()->sync($data['products']);
            $data['total'] = $this->totalGramService->calculateTotalService($id);
            $this->orderRepository->update($model , ['total' => $data['total']]);
            return response()->json(['message' => 'Order Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->orderRepository->delete($id);
            return response()->json(['message' => 'Order Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
