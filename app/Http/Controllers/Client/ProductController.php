<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyGoldRequestClient;
use App\Http\Requests\ExchangeGoldRequest;
use App\Http\Requests\SellGoldRequestClient;
use App\Http\Requests\StoreDepositRequest;
use App\Http\Requests\StoreWithdrawRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SymbolResource;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Services\FileService;
use App\Services\MatchService;
use App\Services\TotalVolumesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(
        private MatchService $matchService,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private FileService $fileService,
        private TotalVolumesService $totalVolumesService,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository
        )
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            //pagination is true or false
            // $paginate = Request()->paginate ?? true;

            $paginate = false;
            //check if requst has count
            $count = Request()->count ?? 10;
            //check if Product has relation
            $relations = ['orders'];
            $products = $this->productRepository->all($count ,$paginate , $relations);
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
            $getOpenedPositions = $this->matchService->getOpenedPositions(Auth::id());
            $totalVolumes = $this->totalVolumesService->getTotalVolumes($getOpenedPositions);
            $balanceDataInMatch = $this->matchService->getBalanceMatch();
            if(!is_string($balanceDataInMatch)){
                $balanceDataInMatch->totalVolumes = $totalVolumes;
            }
            return response()->json(['data' => $balanceDataInMatch]);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function storeWithdraw(StoreWithdrawRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $token = $this->matchService->getAccessToken();
            $paymentGateWayUUid = $this->matchService->getPayment($token);
            $this->matchService->makeWithdraw($data , $token , $paymentGateWayUUid);
            $this->withdrawRepository->create($data);
            return response()->json(['message' => 'Withdraw Order Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function storeDeposit(StoreDepositRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $token = $this->matchService->getAccessToken();
            $paymentGateWayUUid = $this->matchService->getPayment($token);
            $this->matchService->makeWithdraw($data , $token , $paymentGateWayUUid);
            $this->depositRepository->create($data);
            return response()->json(['message' => 'Deposit Order Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
