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
            'key' => 'image-home-page',
            'value' => '',
            'image' => 'ahly.png'
        ]);
        Setting::create([
            'key' => 'oGold-name',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-phone',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-facebook-link',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-telegram-link',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-instagram-link',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-linkedin-link',
            'value' => '',
            'image' => ''
        ]);
        Setting::create([
            'key' => 'oGold-whatsapp-link',
            'value' => '',
            'image' => ''
        ]);
    }
}
