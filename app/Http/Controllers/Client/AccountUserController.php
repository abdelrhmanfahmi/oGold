<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientDepositResource;
use App\Http\Resources\ClientWithdrawResource;
use App\Http\Resources\DepositOrderResource;
use App\Http\Resources\WithdrawOrderResource;
use App\Repository\Interfaces\DepositRepositoryInterface;
use App\Repository\Interfaces\WithdrawRepositoryInterface;
use Illuminate\Http\Request;

class AccountUserController extends Controller
{
    public function __construct(private WithdrawRepositoryInterface $withdrawRepository, private DepositRepositoryInterface $depositRepository)
    {
        $this->middleware('auth:api');
    }

    public function listWithdraws()
    {
        try{
            // $paginate = Request()->paginate ?? true;
            $paginate = false;
            //check if requst has count
            $count = Request()->count ?? 10;
            $data = $this->withdrawRepository->allForUsers($count,$paginate,[]);
            return ClientWithdrawResource::collection($data);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function listDeposits()
    {
        try{
            // $paginate = Request()->paginate ?? true;
            $paginate = false;
            //check if requst has count
            $count = Request()->count ?? 10;
            $data = $this->depositRepository->allForUsers($count,$paginate,[]);
            return ClientDepositResource::collection($data);
        }catch(\Exception $e){
            return $e;
        }
    }
}
