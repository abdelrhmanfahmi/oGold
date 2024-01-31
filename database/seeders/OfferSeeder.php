<?php

namespace Database\Seeders;

use App\Models\Offers;
use App\Services\MatchService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function __construct(private MatchService $matchService)
    {}

    public function run(): void
    {
        $match_data = $this->matchService->getAccessToken();
        $offers = $this->matchService->getOfferUUID($match_data);

        foreach($offers as $offer){
            Offers::create([
                'offer_id' => $offer->uuid,
                'title' => $offer->name,
                'secret_key' => generateSecretKey()
            ]);
        }
    }
}
