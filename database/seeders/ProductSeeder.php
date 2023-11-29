<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'product name one',
            'gram' => 10,
            'image' => null,
        ]);
        Product::create([
            'name' => 'product name two',
            'gram' => 20,
            'image' => null,
        ]);
    }
}
