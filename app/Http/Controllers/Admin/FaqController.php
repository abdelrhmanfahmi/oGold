<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use App\Http\Resources\FaqResource;
use App\Repository\Interfaces\FaqRepositoryInterface;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(private FaqRepositoryInterface $faqRepository)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            //pagination request true or false
            $paginate = Request()->paginate ?? true;

            //count of pagination per page
            $count = Request()->count ?? 10;

            $faqs = $this->faqRepository->all($paginate , $count);
            return FaqResource::collection($faqs);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreFaqRequest $request)
    {
        try{
            $data = $request->validated();
            $this->faqRepository->create($data);
            return response()->json(['message' => 'Faqs Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $setting = $this->faqRepository->find($id);
            return FaqResource::make($setting);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateFaqRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->faqRepository->find($id);
            $this->faqRepository->update($model , $data);
            return response()->json(['message' => 'Faqs Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->faqRepository->delete($id);
            return response()->json(['message' => 'Faqs Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
