<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveDeleteAccountRequest;
use App\Http\Requests\UpdateStatusDepositRequest;
use App\Http\Requests\UpdateStatusWithdrawRequest;
use App\Http\Resources\DeletionAprroveResource;
use App\Http\Resources\UserResource;
use App\Models\DeleteRequest;
use App\Models\User;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __construct(private WithdrawRepositoryInterface $withdrawRepository ,private DepositRepositoryInterface $depositRepository)
    {
        $this->middleware('auth:api');
    }

    public function updateWithdrawStatus(UpdateStatusWithdrawRequest $request ,$id)
    {
        try{
            $data = $request->validated();
            $model = $this->withdrawRepository->find($id);
            $this->withdrawRepository->update($model,$data);
            return response()->json(['message' => 'Withdraw Status Updated Successfully'],200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function updateDepositStatus(UpdateStatusDepositRequest $request ,$id)
    {
        try{
            $data = $request->validated();
            $model = $this->depositRepository->find($id);
            $this->depositRepository->update($model,$data);
            return response()->json(['message' => 'Deposit Status Updated Successfully'],200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function indexApproveDeletionRequest()
    {
        try{
            $approveRequestDelation = DeleteRequest::with(['client' => function($q){
                return $q->withTrashed();
            }])->get();
            return DeletionAprroveResource::collection($approveRequestDelation);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function approveRequestDeletion(ApproveDeleteAccountRequest $request, $deleteRequestId)
    {
        try{
            $data = $request->validated();
            $user_id = DeleteRequest::whereId($deleteRequestId)->value('user_id');
            if($data['status'] == 'approved'){
                $user = User::whereId($user_id)->first();
                if($user){
                    $user->delete();
                    DeleteRequest::whereId($deleteRequestId)->update(['status' => $data['status']]);
                    return response()->json(['message' => 'Account Deleted Successfully'] , 200);
                }
            }
            $user = User::onlyTrashed()->where('id' , $user_id)->first();
            $user->restore();
            DeleteRequest::where('id' , $deleteRequestId)->update(['status' => $data['status']]);
            return response()->json(['message' => 'Account Still Pending Or Request Rejected'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getUserTrashed()
    {
        try{
            $usersDeleted = User::onlyTrashed()->get();
            return UserResource::collection($usersDeleted);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function getUserInfo()
    {
        try{
            return UserResource::make(Auth::user());
        }catch(\Exception $e){
            return $e;
        }
    }
}
