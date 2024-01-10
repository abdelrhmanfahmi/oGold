<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressBookRequest;
use App\Http\Requests\UpdateAddressBookRequest;
use App\Http\Resources\AddressBookResource;
use App\Repository\Interfaces\AddressBookRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressBookController extends Controller
{
    public function __construct(private AddressBookRepositoryInterface $addressBookRepository)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
        //pagination request true or false
        // $paginate = Request()->paginate ?? true;
        $paginate = false;

        //count of pagination per page
        $count = Request()->count ?? 10;

        $address_books = $this->addressBookRepository->allForUsers($count , $paginate);
        return AddressBookResource::collection($address_books);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreAddressBookRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::user()->id;
            $this->addressBookRepository->create($data);
            return response()->json(['message' => 'Address Book Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateAddressBookRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = Auth::user()->id;
            $model = $this->addressBookRepository->find($id);
            $this->addressBookRepository->update($model , $data);
            return response()->json(['message' => 'Address Book Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
