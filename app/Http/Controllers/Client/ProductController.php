<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyGoldRequestClient;
use App\Http\Requests\ExchangeGoldRequest;
use App\Http\Requests\SellGoldRequestClient;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SymbolResource;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Services\FileService;
use App\Services\MatchService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private MatchService $matchService,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private FileService $fileService)
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
            $relations = ['orders'];
            $products = $this->productRepository->all($paginate , $count , $relations);
            return ProductResource::collection($products);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getMarketWatch()
    {
        try{
            $symbols = $this->matchService->getMarketWatchSymbol();
            $this->matchService->saveSymbols($symbols);
            return SymbolResource::collection($symbols);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function buyGold(BuyGoldRequestClient $request)
    {
        try{
            $data = $request->validated();
            $order = $this->matchService->openPosition($data);

            return response()->json(['data' => $order] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function sellGold(SellGoldRequestClient $request)
    {
        try{
            $data = $request->validated();
            $order = $this->matchService->closePosition($data);
            return response()->json(['data' => $order] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function exchangeGold(ExchangeGoldRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $request->only('user_id');
            $order = $this->orderRepository->create($orderData);
            $order->products()->attach($data['products']);

            return response()->json(['message' => 'Transaction Done Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getBalance()
    {
        try{
            return $this->matchService->getBalanceMatch();
        }catch(\Exception $e){
            return $e;
        }
    }
}