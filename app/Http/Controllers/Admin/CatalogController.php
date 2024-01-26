<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCatalogRequest;
use App\Http\Requests\UpdateCatalogRequest;
use App\Http\Resources\CatalogResource;
use App\Models\Product;
use App\Models\User;
use App\Repository\Interfaces\CatalogRepositoryInterface;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class CatalogController extends Controller
{
    public function __construct(
        private CatalogRepositoryInterface $catalogRepository,
        private MatchService $matchService,
        private UserRepositoryInterface $userRepository
    )
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try{
            $paginate = Request()->paginate ?? true;
            $count = Request()->count ?? 10;
            $relations = ['products'];
            $catalogs = $this->catalogRepository->all($count , $paginate , $relations);
            return CatalogResource::collection($catalogs);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function store(StoreCatalogRequest $request)
    {
        try{
            $data = $request->validated();
            $data['uuid'] = Uuid::uuid4();
            $catalog = $this->catalogRepository->create($data);
            // $dataTotal = $this->getTotalPriceWithCatalog($data['products'] , $data['preimum_fees']);
            $catalog->products()->attach($data['products']);
            return response()->json(['message' => 'Catalog Stored Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function show($id)
    {
        try{
            $catalog = $this->catalogRepository->find($id , ['products']);
            return $catalog;
        }catch(\Exception $e){
            return $e;
        }
    }

    public function update(UpdateCatalogRequest $request , $id)
    {
        try{
            $data = $request->validated();
            $model = $this->catalogRepository->find($id , []);
            $catalog = $this->catalogRepository->update($model,$data);
            // $dataTotal = $this->getTotalPriceWithCatalog($data['products'] , $data['preimum_fees']);
            $catalog->products()->sync($data['products']);
            return response()->json(['message' => 'Catalog Updated Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function destroy($id)
    {
        try{
            $this->catalogRepository->delete($id);
            return response()->json(['message' => 'Catalog Deleted Successfully'] , 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    protected function getTotalPriceWithCatalog($products , $preimum_fees)
    {
        try{
            $arrResult = [];
            $user = $this->userRepository->findByEmail(env('EMAILUPDATEPRICE'));
            $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
            if($preimum_fees == null){
                $preimum_fees = 0;
            }
            foreach($products as $key => $product){
                foreach($product as $p){
                    $dataProduct = Product::whereId($p)->first();
                    $totalPrice = ($buyPrice[0]->ask * $dataProduct->gram) + $dataProduct->charge + ($preimum_fees * $dataProduct->gram);
                    $arrResult [$key]['product_id'] = $p;
                    $arrResult [$key]['total_price'] = $totalPrice;
                }
            }

            return $arrResult;
        }catch(\Exception $e){
            return $e;
        }
    }
}
