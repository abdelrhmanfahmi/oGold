<?php

namespace Database\Seeders;

use App\Models\Offers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Offers::create([
            'offer_id' => 'fabb2b6c-400d-4772-b037-d9717db01ec8',
            'title' => 'fabrica_test',
            'secret_key' => generateSecretKey()
        ]);
    }
}
