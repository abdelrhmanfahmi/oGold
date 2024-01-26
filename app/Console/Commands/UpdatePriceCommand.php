<?php

namespace App\Console\Commands;

use App\Models\Catalog;
use App\Models\MatchData;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use App\Services\MatchService;
use Illuminate\Support\Facades\DB;

class UpdatePriceCommand extends Command
{
    public function __construct(private MatchService $matchService)
    {
        parent::__construct();
    }

    protected $signature = 'update:cron';

    protected $description = 'Command description';

    public function handle()
    {
        $user = User::where('email' , env('EMAILUPDATEPRICE'))->first();
        $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        $catalogs = Catalog::with('products')->get();
        if(count($catalogs) > 0){
            if(!is_string($buyPrice)){
                foreach($catalogs as $catalog){
                    foreach($catalog->products as $product){
                        $totalPrice = ($buyPrice[0]->ask * $product->gram) + $product->charge + ($catalog->preimum_fees * $product->gram);
                        DB::table('catalog_products')
                        ->where('catalog_id' , $catalog->id)
                        ->where('product_id' , $product->id)
                        ->update(['total_price' => $totalPrice]);
                    }
                }
                \Log::info('work fine one');
            }else{
                //login with user
                $this->matchService->loginAccountForCronJob();
                $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
                foreach($catalogs as $catalog){
                    foreach($catalog->products as $product){
                        $totalPrice = ($buyPrice[0]->ask * $product->gram) + $product->charge + ($catalog->preimum_fees * $product->gram);
                        DB::table('catalog_products')
                        ->where('catalog_id' , $catalog->id)
                        ->where('product_id' , $product->id)
                        ->update(['total_price' => $totalPrice]);
                    }
                }
                \Log::info('work fine two');
            }
        }else{
            \Log::info('no catalogs yet');
        }

    }
}
