<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGiftRequest;
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

    public function sendGift(SendGiftRequest $request)
    {
        try{
            $data = $request->validated();
            $totalGoldPending = $this->getTotalGoldPendingPerUser($data['sent_user_id']);
            $opendPositions = $this->matchService->getOpenedPositions($data['sent_user_id']);
            // dd($opendPositions);
            if(!is_string($opendPositions)){
                $arrayOfPositionsToClose = $this->matchService->getPositionsByOrder($opendPositions,$totalGoldPending,$data);
                if($arrayOfPositionsToClose == 0){
                    return response()->json(['message' => 'you have not positions to close'] , 400);
                }else if($arrayOfPositionsToClose == -1){
                    return response()->json(['message' => 'you cannot sell gold smaller than you have'] , 400);
                }else if($arrayOfPositionsToClose == -2){
                    return response()->json(['message' => 'you cannot sell gold bigger than your pending order gold, wait approve admin to see your net gold'] , 400);
                }else{
                    $order = $this->matchService->closePositionsByOrderDateForGift($arrayOfPositionsToClose , $data['sent_user_id'], $data['volume']);
                    if($order == 'Qfx response exception: while closing positions, status: 3, response: Failed to close any position!'){
                        return response()->json(['message' => 'Cannot Close Any Positions Right Now'] , 400);
                    }

                    if($order['sellResponse']->status == 'OK'){
                        dd('hi');
                        // $data['sell_price'] = $order['sellPrice'];
                        // $this->sellGoldRepository->create($data);
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
