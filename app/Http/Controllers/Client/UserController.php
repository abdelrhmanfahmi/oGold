<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UploadFileKYCRequest;
use App\Models\DeleteRequest;
use App\Models\KYC;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use App\Services\TotalVolumesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MatchService $matchService,
        private TotalVolumesService $totalVolumesService
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
            if($hasOrdersPending != 0 || $balance->balance != 0 || $totalVolumes != 0){
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
}
