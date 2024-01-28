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
use App\Models\Deposit;
use App\Models\Withdraw;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\ProductRepositoryInterface;
use App\Repository\Interfaces\SellGoldRepositoryInterface;
use App\Repository\Interfaces\SettingRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use App\Services\FileService;
use App\Services\MatchService;
use App\Services\ShipdayService;
use App\Services\TotalGramService;
use App\Services\TotalVolumesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function __construct(
        private MatchService $matchService,
        private OrderRepositoryInterface $orderRepository,
        private ProductRepositoryInterface $productRepository,
        private FileService $fileService,
        private TotalVolumesService $totalVolumesService,
        private WithdrawRepositoryInterface $withdrawRepository,
        private DepositRepositoryInterface $depositRepository,
        private TotalGramService $totalGramService,
        private SettingRepositoryInterface $settingRepository,
        private BuyGoldRepositoryInterface $buyGoldRepository,
        private SellGoldRepositoryInterface $sellGoldRepository,
        private ShipdayService $shipdayService
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
            $products = $this->productRepository->all($count ,$paginate , []);
            return ProductResource::collection($products);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getMarketWatch()
    {
        try{
            $symbols = $this->matchService->getMarketWatchSymbolPerUser(Auth::id());
            if(!is_string($symbols)){
                $this->matchService->saveSymbols($symbols);
                return SymbolResource::collection($symbols);
            }else{
                return response()->json($symbols , 401);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function buyGold(BuyGoldRequestClient $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            //get buy price & user balance
            $buyPrice = $this->matchService->getMarketWatchSymbolPerUser(Auth::id());
            $userBalance = $this->matchService->getBalanceMatch();
            // check if they authenticated or not
            if(!is_string($buyPrice) && !is_string($userBalance)){
                //calculate total volume price
                $totalVolumePrice = $buyPrice[0]->ask * (int) $data['volume'];
                //get wallet per user
                $userBalance->wallet = number_format((float)$userBalance->balance - $userBalance->margin,2,'.','');

                if($userBalance->wallet > $totalVolumePrice){
                    $order = $this->matchService->openPosition($data);
                    if(!is_string($order)){
                        $data['buy_price'] = $order['buy_price'];
                        $this->buyGoldRepository->create($data);
                        return response()->json(['data' => $order['buyResponse']] , 200);
                    }else{
                        return response()->json(['message' => 'The market is closed. Try again later !'] , 403);
                    }
                }else{
                    return response()->json(['message' => 'balance insufficient, please add more funds !'] , 403);
                }
            }else{
                return response()->json(['message' => 'Authentication Error !'] , 403);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function sellGold(SellGoldRequestClient $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['symbol'] = 'GoldGram24c';
            $totalGoldPending = $this->getTotalGoldPendingPerUser();
            $opendPositions = $this->matchService->getOpenedPositions(Auth::id());
            if(!is_string($opendPositions)){
                $arrayOfPositionsToClose = $this->matchService->getPositionsByOrder($opendPositions,$totalGoldPending,$data);
                if($arrayOfPositionsToClose == 0){
                    return response()->json(['message' => 'you have not positions to close'] , 400);
                }else if($arrayOfPositionsToClose == -1){
                    return response()->json(['message' => 'you cannot sell gold smaller than you have'] , 400);
                }else if($arrayOfPositionsToClose == -2){
                    return response()->json(['message' => 'you cannot sell gold bigger than your pending order gold, wait approve admin to see your net gold'] , 400);
                }else{
                    $order = $this->matchService->closePositionsByOrderDatePerUser($arrayOfPositionsToClose , Auth::id(), $data['volume']);
                    if($order == 'Qfx response exception: while closing positions, status: 3, response: Failed to close any position!'){
                        return response()->json(['message' => 'The market is closed. Try again later !'] , 400);
                    }
                    if(is_string($order)){
                        $returnedError = json_decode($order);
                        return response()->json(['message' => 'The market is closed. Try again later !'] , 400);
                    }
                    if($order['sellResponse']->status == 'OK'){
                        $data['sell_price'] = $order['sellPrice'];
                        $this->sellGoldRepository->create($data);
                    }else{
                        return response()->json(['message' => 'something wrong!'] , 500);
                    }
                    return response()->json(['data' => $order['sellResponse']] , 200);
                }
            }else{
                return response()->json(['message' => 'Authentication error'] , 401);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function exchangeGold(ExchangeGoldRequest $request)
    {
        try{
            $data = $request->validated();
            $orderData = $request->only('user_id','address_book_id');
            $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($data['user_id']);
            if(is_string($buyPrice)){
                return response()->json(['message' => 'Authentication error !'] , 400);
            }
            $orderData['buy_price'] = $buyPrice[0]->ask;
            $orderData['status'] = 'pending';
            $order = $this->orderRepository->create($orderData);

            $order->products()->attach($data['products']);
            $data['total'] = $this->totalGramService->calculateTotalService($order->id);
            $data['total_charges'] = $this->totalGramService->calculateTotalChargesService($order->id);
            $updatedOrder = $this->orderRepository->find($order->id ,[]);
            $this->orderRepository->update($updatedOrder , ['total' => $data['total'] , 'total_charges' => $data['total_charges']]);

            //here call api integration of shipday
            $this->shipdayService->storeOrderDelivery($updatedOrder , Auth::user() , 'cash');
            return response()->json(['message' => 'Transaction Done Successfully, Wait For Order Approval From Admin'] , 200);
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
                $keyImage = $this->settingRepository->findByKey()->first();
                $balanceDataInMatch->wallet = number_format((float)$balanceDataInMatch->balance - $balanceDataInMatch->margin,2,'.','');
                $balanceDataInMatch->totalVolumes = $totalVolumes;
                $balanceDataInMatch->imageKey = env('APP_URL').'/uploads/'.$keyImage->image;
                return response()->json(['data' => $balanceDataInMatch]);
            }else{
                return response()->json($balanceDataInMatch , 401);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function storeWithdraw(StoreWithdrawRequest $request)
    {
        try{
            $this->authorize('store' , Withdraw::class);
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['status'] = 'pending';
            $data['currency'] = 'AED';
            // $token = $this->matchService->getAccessToken();
            // $paymentGateWayUUid = $this->matchService->getPayment($token);
            // $this->matchService->makeWithdraw($data , $token , $paymentGateWayUUid);
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
            $data['status'] = 'pending';
            $data['currency'] = 'AED';
            // $token = $this->matchService->getAccessToken();
            // $paymentGateWayUUid = $this->matchService->getPayment($token);
            // $this->matchService->makeDeposit($data , $token , $paymentGateWayUUid);
            $this->depositRepository->create($data);
            return response()->json(['message' => 'Deposit Order Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getOpenPositions()
    {
        try{
            $openPosition = $this->matchService->getOpenedPositions(Auth::id());
            if(!is_string($openPosition)){
                return response()->json(['data' => $openPosition]);
            }else{
                return response()->json(['message' => 'authenticated error'] , 403);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    protected function getTotalGoldPendingPerUser()
    {
        try{
            $totalGoldPending = $this->orderRepository->findByUserId(Auth::id());
            $countTotalGold = 0;
            foreach($totalGoldPending as $goldPending){
                $countTotalGold += $goldPending->total;
            }

            return $countTotalGold;
        }catch(\Exception $e){
            return $e;
        }
    }
}
