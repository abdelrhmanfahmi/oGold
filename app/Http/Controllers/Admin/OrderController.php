<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Repository\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderRepositoryInterface $orderRepository)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            //pagination is true or false
            $paginate = Request()->paginate ?? true;
            //check if requst has count
            $count = Request()->count ?? 10;
            //check if Product has relation
            $relations = ['products' , 'client' , 'deliveries'];
            $orders = $this->orderRepository->all($count , $paginate , $relations);
            return OrderResource::collection($orders);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreOrderRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $request->only('address' , 'payment_type' , 'user_id');
            $order = $this->orderRepository->create($orderData);

            $order->products()->attach($data['products']);
            return response()->json(['message' => 'Order Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $relations = ['client' , 'products'];
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
            $orderData = $request->only('address' , 'payment_type' , 'user_id');
            $model = $this->orderRepository->find($id , []);
            $order = $this->orderRepository->update($model , $orderData);

            $order->products()->sync($data['products']);
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
