<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGiftRequest;
use App\Http\Resources\GiftResource;
use App\Models\Gift;
use App\Models\User;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiftController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private MatchService $matchService
    )
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        try{
            if($request->type == 'sender'){
                $gifts = Gift::where('sender_user_id' , Auth::id())
                ->with('sender')
                ->get();
                return GiftResource::collection($gifts);
            }else{
                $gifts = Gift::where('recieved_user_id' , Auth::id())
                ->with('recieved')
                ->get();
                return GiftResource::collection($gifts);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function sendGift(SendGiftRequest $request)
    {
        try{
            $data = $request->validated();
            $totalGoldPending = $this->getTotalGoldPendingPerUser($data['sender_user_id']);
            $opendPositions = $this->matchService->getOpenedPositions($data['sender_user_id']);
            $userBalance = $this->matchService->getBalanceMatch();
            // $sellPriceNow = $this->matchService->getMarketWatchSymbol();
            $sellPriceNow = $this->matchService->getMarketWatchSymbolMarkup();

            $userRecieved = User::find($data['recieved_user_id']);
            if($userRecieved->co_auth != null){
                if(!is_string($opendPositions) && !is_string($userBalance) && !is_string($sellPriceNow)){
                    $priceWillSentForGift = $data['volume'] * $sellPriceNow[0]->bid;
                    if($priceWillSentForGift <= $userBalance->balance){ //check if user has much price in his balance to send gift
                        $arrayOfPositionsToClose = $this->matchService->getPositionsByOrder($opendPositions,$totalGoldPending,$data);
                        if($arrayOfPositionsToClose == 0){
                            return response()->json(['message' => 'you have not positions to close'] , 400);
                        }else if($arrayOfPositionsToClose == -1){
                            return response()->json(['message' => 'you cannot sell gold smaller than you have'] , 400);
                        }else if($arrayOfPositionsToClose == -2){
                            return response()->json(['message' => 'you cannot sell gold bigger than your pending order gold, wait approve admin to see your net gold'] , 400);
                        }else{
                            $order = $this->matchService->closePositionsByOrderDateForGift($arrayOfPositionsToClose , $data['sender_user_id'], $data['volume']);
                            if($order == 'Qfx response exception: while closing positions, status: 3, response: Failed to close any position!'){
                                return response()->json(['message' => 'Cannot Close Any Positions Right Now'] , 400);
                            }
                            if(is_string($order)){
                                $returnedError = json_decode($order);
                                return response()->json(['message' => $returnedError->errorMessage] , 400);
                            }
                            if($order['sellResponse']->status == 'OK'){
                                // start credit out
                                $priceCreditOut = $priceWillSentForGift + ($priceWillSentForGift * 0.5);
                                $returnedData = $this->matchService->withdrawMoneyManager($priceCreditOut);

                                // here handling exception of withdraw that account now is demo
                                if($returnedData['status'] != 'OPERATION_SUCCESS'){
                                    return response()->json(['message' => $returnedData['message']] , 400);
                                }else{
                                    //start credit in
                                    $dataCreditIn = $this->matchService->depositMoneyManager($priceCreditOut,$data['recieved_user_id']);
                                    if($dataCreditIn['status'] != 'OPERATION_SUCCESS'){
                                        return response()->json(['message' => $dataCreditIn['message']] , 400);
                                    }else{
                                        //start buy gold
                                        $clientOrderStringId = $this->generateGoldStatement();
                                        $this->matchService->makeOrderSubmitForBuyGold($clientOrderStringId,$data);

                                        //here check if price will be deducted from price credit in user or not
                                        $buyPriceWithVolume = $sellPriceNow[0]->ask * $data['volume'];
                                        if($priceCreditOut > $buyPriceWithVolume){
                                            $priceWillBeDeducted = $priceCreditOut - $buyPriceWithVolume;
                                            //start credit out commission of company
                                            $res = $this->matchService->withdrawMoneyManager($priceWillBeDeducted);
                                            if($res['status'] != 'OPERATION_SUCCESS'){
                                                return response()->json(['message' => $dataCreditIn['message']] , 400);
                                            }
                                        }

                                        // if($buyGoldResponse['buyResponse']->status == 'OK'){
                                            Gift::create([
                                                'volume' => $data['volume'],
                                                'total_price' => $buyPriceWithVolume,
                                                'sender_user_id' => $data['sender_user_id'],
                                                'recieved_user_id' => $data['recieved_user_id'],
                                                'message' => $data['message'],
                                                'client_order_id' => $clientOrderStringId,
                                                'commision' => $priceWillBeDeducted
                                            ]);
                                            return response()->json(['message' => 'Gift Send Successfully'] , 200);
                                        // }else{
                                        //     return response()->json(['message' => 'something wrong in buy gold!'] , 500);
                                        // }
                                    }
                                }
                            }else{
                                return response()->json(['message' => 'something wrong in sell gold!'] , 500);
                            }
                        }
                    }else{
                        return response()->json(['message' => 'you dont have much money to withdraw to send to gift'] , 400);
                    }
                }else{
                    return response()->json(['message' => 'Authentication error'] , 401);
                }
            }else{
                return response()->json(['message' => 'Failed! This account is inactive'] ,400);
            }



        }catch(\Exception $e){
            return $e;
        }
    }

    protected function getTotalGoldPendingPerUser($user_id)
    {
        try{
            $totalGoldPending = $this->orderRepository->findByUserId($user_id);
            $countTotalGold = 0;
            foreach($totalGoldPending as $goldPending){
                $countTotalGold += $goldPending->total;
            }

            return $countTotalGold;
        }catch(\Exception $e){
            return $e;
        }
    }

    protected function generateGoldStatement()
    {
        try{
            $string = "O-GOLD-GIFT-";
            $lastGiftOrderId = Gift::latest()->first();
            if($lastGiftOrderId == null){
                $finalString = $string.'1';
            }else{
                $lastOrderId = $lastGiftOrderId->client_order_id;
                preg_match_all('!\d+!', $lastOrderId, $matches);
                $lastNumber = $matches[0][0];
                $finalString = $string.++$lastNumber;
            }
            return $finalString;
        }catch(\Exception $e){
            return $e;
        }
    }
}
