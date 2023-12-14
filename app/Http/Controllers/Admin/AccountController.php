<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStatusDepositRequest;
use App\Http\Requests\UpdateStatusWithdrawRequest;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use Illuminate\Http\Request;

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
            $data = $request->validated();
            $model = $this->depositRepository->find($id);
            $this->depositRepository->update($model,$data);
            return response()->json(['message' => 'Deposit Status Updated Successfully'],200);
        }catch(\Exception $e){
            return $e;
        }
    }
}
