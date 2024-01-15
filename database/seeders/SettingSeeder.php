<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'key' => 'shipping_fees',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'terms',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'privacy',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'about',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'contact-us',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'image-home-page',
            'value' => '',
            'image' => 'ahly.png'
        ]);
    }
}
