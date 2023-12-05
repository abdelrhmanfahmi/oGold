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
            'name' => 'Product One',
            'gram' => 10,
            'image' => null,
            'is_active' => '1',
        ]);
        Product::create([
            'name' => 'Product Two',
            'gram' => 20,
            'image' => null,
            'is_active' => '1',
        ]);
        Product::create([
            'name' => 'Product Three',
            'gram' => 30,
            'image' => null,
            'is_active' => '1',
        ]);
        Product::create([
            'name' => 'Product Four',
            'gram' => 40,
            'image' => null,
            'is_active' => '1',
        ]);
        Product::create([
            'name' => 'Product Five',
            'gram' => 50,
            'image' => null,
            'is_active' => '1',
        ]);
    }
}
