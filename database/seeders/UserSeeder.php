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
            'surname' => 'admin_surname',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '01123635566',
            'dateOfBirth' => '1999-01-20',
            'country' => 'Egypt',
            'state' => 'Alex',
            'city' => 'Alasfra',
            'address' => 'address test one',
            'type' => 'admin'
        ]);

        $admin->assignRole('admin');

        $client = User::create([
            'name' => 'client',
            'surname' => 'client_surname',
            'email' => 'client@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '01287888477',
            'dateOfBirth' => '1999-04-20',
            'country' => 'Egypt',
            'state' => 'Alex',
            'city' => 'Sidibishr',
            'address' => 'address test two',
            'type' => 'client'
        ]);

        $client->assignRole('client');

        $refinery = User::create([
            'name' => 'refinery',
            'surname' => 'refinery_surname',
            'email' => 'refinery@gmail.com',
            'password' => Hash::make('12345678'),
            'phone' => '01228343407',
            'dateOfBirth' => '1999-09-20',
            'country' => 'Egypt',
            'state' => 'Alex',
            'city' => 'Almandra',
            'address' => 'address test three',
            'type' => 'refinery'
        ]);

        $refinery->assignRole('refinery');
    }
}
