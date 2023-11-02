<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'type' => 'admin'
        ]);

        $admin->assignRole('admin');

        $client = User::create([
            'name' => 'client',
            'email' => 'client@gmail.com',
            'password' => Hash::make('12345678'),
            'type' => 'client'
        ]);

        $client->assignRole('client');

        $provider = User::create([
            'name' => 'provider',
            'email' => 'provider@gmail.com',
            'password' => Hash::make('12345678'),
            'type' => 'provider'
        ]);

        $provider->assignRole('provider');
    }
}
