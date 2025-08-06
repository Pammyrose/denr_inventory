<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    public function run()
    {
        User::create([
 'email' => 'admin@denr.gov.ph',
 'password' => Hash::make('password'),
 'is_admin' => true,
 'email_verified_at' => now(),
]);




    }
}