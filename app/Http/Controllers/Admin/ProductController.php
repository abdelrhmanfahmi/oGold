<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Repository\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductRepositoryInterface $productRepository)
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            //pagination is true or false
            $paginate = Request()->paginate ?? true;
            //check if requst has count
            $count = Request()->count ?? 10;
            //check if Product has relation
            $relations = ['orders'];
            $products = $this->productRepository->all($paginate , $count , $relations);
            return ProductResource::collection($products);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreProductRequest $request)
    {
        try{
            $data = $request->validated();
            $this->productRepository->create($data);
            return response()->json(['message' => 'Product Created Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $relations = ['orders'];
            $product = $this->productRepository->find($id , $relations);
            return ProductResource::make($product);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateProductRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->productRepository->find($id , []);
            $this->productRepository->update($model , $data);
            return response()->json(['message' => 'Product Updated Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->productRepository->delete($id);
            return response()->json(['message' => 'Product Deleted Successfully']);
        }catch(\Exception $e){
            return $e;
        }
    }
}
