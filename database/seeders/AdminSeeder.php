<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
           'role' => 'admin', 
           'first_name' => 'admin', 
           'last_name' => 'admin', 
           'email' => 'info@hnhsofttechsolutions.com', 
           'password' => Hash::make(12345678), 
           'phone_no' => "12345678", 
        ]);
    }
}
