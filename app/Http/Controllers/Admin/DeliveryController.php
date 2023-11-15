<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Http\Resources\DeliveryResource;
use App\Repository\Interfaces\DeliveryRepositoryInterface;
use App\Services\TotalPriceService;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryRepositoryInterface $deliveryRepository , private TotalPriceService $totalPriceService)
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
            $relations = ['order'];
            $delivery = $this->deliveryRepository->all($paginate , $count , $relations);
            return DeliveryResource::collection($delivery);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreDeliveryRequest $request)
    {
        try{
            $data = $request->validated();
            $data['total_price'] = $this->totalPriceService->calculateTotalService($data['order_id']);
            $this->deliveryRepository->create($data);
            return response()->json(['message' => 'Delivery Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $relations = ['order'];
            $delivery = $this->deliveryRepository->find($id , $relations);
            return DeliveryResource::make($delivery);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateDeliveryRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->deliveryRepository->find($id , []);
            $data['total_price'] = $this->totalPriceService->calculateTotalService($data['order_id']);
            $this->deliveryRepository->update($model , $data);

            return response()->json(['message' => 'Delivery Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->deliveryRepository->delete($id);
            return response()->json(['message' => 'Delivery Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
