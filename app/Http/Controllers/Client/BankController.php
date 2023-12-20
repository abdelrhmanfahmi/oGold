<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankRequest;
use App\Http\Requests\UpdateBankRequest;
use App\Http\Resources\BankResource;
use App\Repository\Interfaces\BankRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankController extends Controller
{
    public function __construct(private BankRepositoryInterface $bankRepository)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            // $paginate = Request()->paginate ?? true;
            $paginate = false;
            $count = Request()->count ?? 10;
            $relations = ['client'];
            $bankDetails = $this->bankRepository->allForUsers($count,$paginate,$relations);
            return BankResource::collection($bankDetails);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreBankRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::user()->id;
            $this->bankRepository->create($data);
            return response()->json(['message' => 'Bank Details Created Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $relations = ['client'];
            $bank_details = $this->bankRepository->find($id,$relations);
            return BankResource::make($bank_details);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateBankRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->bankRepository->find($id);
            $this->bankRepository->update($model , $data);
            return response()->json(['message' => 'Bank Details Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function delete($id)
    {
        try{
            $this->bankRepository->delete($id);
            return response()->json(['message' => 'Bank Details Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
