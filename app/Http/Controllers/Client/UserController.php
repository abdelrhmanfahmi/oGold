<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuyAndDeliverRequest;
use App\Http\Requests\CheckUserExistsRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UploadFileKYCRequest;
use App\Http\Resources\GiftUserResource;
use App\Http\Resources\KycResource;
use App\Models\Contact;
use App\Models\DeleteRequest;
use App\Models\KYC;
use App\Repository\Interfaces\BuyGoldRepositoryInterface;
use App\Repository\Interfaces\OrderRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use App\Services\ShipdayService;
use App\Services\TotalGramService;
use App\Services\TotalVolumesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private MatchService $matchService,
        private TotalVolumesService $totalVolumesService,
        private UserRepositoryInterface $userRepository ,
        private OrderRepositoryInterface $orderRepository,
        private TotalGramService $totalGramService,
        private ShipdayService $shipdayService,
        private BuyGoldRepositoryInterface $buyGoldRepository
    )
    {
        $this->middleware('auth:api');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try{
            $data = $request->validated();
            $model = $this->userRepository->find(Auth::user()->id);
            if(Hash::check($data['old_password'] , Auth::user()->password)){
                $this->userRepository->update($model , $data);
                return response()->json(['message' => 'Password Updated Successfully']);
            }else{
                return response()->json(['message' => 'Old Password Invalid'], 422);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function deleteAccount(DeleteAccountRequest $request)
    {
        try{
            $data = $request->validated();
            $data['status'] = 'pending';
            $hasOrdersPending = Auth::user()->orders->where('status' , 'pending')->count();
            $getOpenedPositions = $this->matchService->getOpenedPositions(Auth::id());
            $totalVolumes = $this->totalVolumesService->getTotalVolumes($getOpenedPositions);
            $balance = $this->matchService->getBalanceMatch();
            $balance->wallet = number_format((float)$balance->balance - $balance->margin,2,'.','');
            if($hasOrdersPending != 0 || $balance->wallet != 0 || $totalVolumes != 0){
                DeleteRequest::create($data);
                return response()->json(['message' => 'Request Sent To Admin, Wait For Approval'], 200);
            }else{
                $user = Auth::user();
                $user->delete();
                return response()->json(['message' => 'Account Deleted Successfully'], 200);
            }
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getFileUrls()
    {
        try{
            $data = KYC::where('user_id' , Auth::id())->get();
            return KycResource::collection($data);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function UploadKYCFile(UploadFileKYCRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $result = $this->matchService->uploadFileKYC($data);
            if($result != ''){
                $kycFile = KYC::updateOrCreate([
                    'user_id' => $data['user_id'],
                    'type' => $data['type']
                ],[
                    'type' => $data['type'],
                    'user_id' => $data['user_id'],
                    'file' => env('EXTERNAL_PATH_KYC').'/'.$result->fileUrl
                ]);
                return response()->json(['data' => $kycFile],200);
            }else{
                return response()->json(['message' => 'Authentication Error'] , 401);
            }

        }catch(\Exception $e){
            return $e;
        }
    }

    public function getUsersByPhone(CheckUserExistsRequest $request)
    {
        try{
            $data = $request->validated();
            $phones = $this->userRepository->getAllPhones();

            //first check if you send to yourself
            if(Auth::user()->phone == $data['phone']){
                return response()->json(['message' => 'You Cannot Send Gift To Yourself !'] , 400);
            }

            //second check if this phone exists in system
            if(!in_array($data['phone'] , $phones)){
                return response()->json(['message' => 'This phone number is not registered on OGOLD !'] , 400);
            }

            $user = $this->userRepository->findByPhone($data['phone']);
            //check if user is active or not
            if($user->client_trading_id == null){
                return response()->json(['message' => 'This User Is Not Active Yet !'] , 400);
            }

            return GiftUserResource::make($user);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function storeContact(StoreContactRequest $request)
    {
        try{
            $data = $request->validated();
            Contact::create($data);
            return response()->json(['message' => 'Your Request Stored Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function buyAndDeliver(BuyAndDeliverRequest $request)
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

            //here buy gold for this user using total
            $userBalance = $this->matchService->getBalanceMatchBuyAndDeliver($data['user_id']);
            // check if they authenticated or not
            if(!is_string($buyPrice) && !is_string($userBalance)){
                //calculate total volume price
                $totalVolumePrice = $buyPrice[0]->ask * (int) $data['total'];
                //get wallet per user
                $userBalance->wallet = number_format((float)$userBalance->balance - $userBalance->margin,2,'.','');

                if($userBalance->wallet > $totalVolumePrice){
                    $order = $this->matchService->openPositionBuyAndDeliver($data['user_id'],$data['total']);
                    if(!is_string($order)){
                        $dataSaved['buy_price'] = $order['buy_price'];
                        $dataSaved['volume'] = $data['total'];
                        $dataSaved['symbol'] = 'GoldGram24c';
                        $dataSaved['user_id'] = $data['user_id'];
                        $this->buyGoldRepository->create($dataSaved);
                        // return response()->json(['data' => $order['buyResponse']] , 200);
                    }else{
                        return response()->json(['message' => 'The market is closed. Try again later !'] , 403);
                    }
                }else{
                    return response()->json(['message' => 'balance insufficient, please add more funds !'] , 403);
                }
            }else{
                return response()->json(['message' => 'Authentication Error !'] , 403);
            }

            //here call api integration of shipday
            $this->shipdayService->storeOrderBuyAndDeliver($updatedOrder , $data['user_id'] , $data['payment_method']);

            //here for approve order
            $opendPositions = $this->matchService->getAllPositionForAuthUser($data['user_id']);
            $getPositionsByOrder = $this->matchService->getPositionsByOrderAdminRefinaryRole($opendPositions,$data['total']);
            if($getPositionsByOrder == 0){
                return response()->json(['message' => 'there is no opened positions'],400);
            }else if($getPositionsByOrder == -1){
                return response()->json(['message' => 'Authentication Error !'],401);
            }else{
                $orderSubmited = $this->matchService->closePositionsByOrderDatePerAdmin($getPositionsByOrder , $data['user_id'], $data['total']);
                if($orderSubmited['status'] == 'SUCCESS'){
                    $priceWillBeDeducted = $data['total'] * $buyPrice[0]->ask;
                    $this->matchService->withdrawMoneyManager($priceWillBeDeducted , $data['user_id']);
                    //here call api for approve order to ready to pick up delivery integration
                    $this->shipdayService->approveOrderReadyToPickup($updatedOrder->id);
                    $this->orderRepository->update($updatedOrder,['status' => 'ready_to_picked']);
                }else{
                    return response()->json(['message' => 'Something Went Wrong !'] , 400);
                }
            }
            return response()->json(['message' => 'Order Submit Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }
}
