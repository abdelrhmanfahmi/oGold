<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGiftRequest;
use App\Http\Resources\GiftResource;
use App\Models\Gift;
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

    public function index()
    {
        try{
            $gifts = Gift::where('sender_user_id' , Auth::id())
            ->orWhere('recieved_user_id' , Auth::id())
            ->with('sender','recieved')
            ->get();
            return GiftResource::collection($gifts);
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
            $sellPriceNow = $this->matchService->getMarketWatchSymbol();

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

                        if($order['sellResponse']->status == 'OK'){
                            // start credit out
                            $priceCreditOut = $priceWillSentForGift + ($priceWillSentForGift * 0.5);
                            $returnedData = $this->matchService->creditOut($priceCreditOut);
                            if($returnedData['status'] == 500){
                                return response()->json(['message' => $returnedData['message']] , 400);
                            }else{
                                //start credit in
                                $dataCreditIn = $this->matchService->creditIn($priceCreditOut,$data['recieved_user_id']);
                                if($dataCreditIn['status'] == 500){
                                    return response()->json(['message' => $dataCreditIn['message']] , 400);
                                }else{
                                    //start buy gold
                                    $buyGoldResponse = $this->matchService->openPositionForUser($data);

                                    //here check if price will be deducted from price credit in user or not
                                    $buyPriceWithVolume = $buyGoldResponse['buy_price'] * $data['volume'];
                                    if($priceCreditOut > $buyPriceWithVolume){
                                        $priceWillBeDeducted = $priceCreditOut - $buyPriceWithVolume;

                                        //start credit out commission of company
                                        $res = $this->matchService->creditOut($priceWillBeDeducted);
                                        if($res['status'] == 500){
                                            return response()->json(['message' => $dataCreditIn['message'] .' in deduction commision'] , 400);
                                        }
                                    }

                                    if($buyGoldResponse['buyResponse']->status == 'OK'){
                                        Gift::create([
                                            'volume' => $data['volume'],
                                            'sender_user_id' => $data['sender_user_id'],
                                            'recieved_user_id' => $data['recieved_user_id']
                                        ]);
                                        return response()->json(['message' => 'Gift Send Successfully'] , 200);
                                    }else{
                                        return response()->json(['message' => 'something wrong in buy gold!'] , 500);
                                    }
                                }
                            }
                        }else{
                            return response()->json(['message' => 'something wrong in sell gold!'] , 500);
                        }
                        return response()->json(['data' => $order['sellResponse']] , 200);
                    }
                }else{
                    return response()->json(['message' => 'you dont have much money to credit out to send to gift'] , 400);
                }
            }else{
                return response()->json(['message' => 'Authentication error'] , 401);
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
}
