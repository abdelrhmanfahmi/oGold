<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use App\Services\MatchService;

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
        //logic here
        $user = User::where('email' , env('EMAILUPDATEPRICE'))->first();
        $products = Product::all();
        $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
        if(!is_string($buyPrice)){
            foreach($products as $product){
                $product->update(['buy_price' => $buyPrice[0]->ask]);
            }
            \Log::info('work fine one');
        }else{
            //login with user
            $this->matchService->loginAccountForCronJob();
            $buyPrice = $this->matchService->getMarketWatchSymbolPerUser($user->id);
            foreach($products as $product){
                $product->update(['buy_price' => $buyPrice[0]->ask]);
            }
            \Log::info('work fine two');
        }
        \Log::info('work fine three');
    }
}
