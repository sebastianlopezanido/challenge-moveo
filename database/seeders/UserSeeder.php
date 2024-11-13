<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'julian@example.com'],
            [
                'name' => 'Julian Casablancas',
                'role_id' => Role::where('name', 'user')->first()->id,
                'password' => bcrypt('password'),
            ]
        );
        
        User::firstOrCreate(
            ['email' => 'charliexcx@example.com'],
            [
                'name' => 'Charlie XCX',
                'role_id' => Role::where('name', 'user')->first()->id,
                'password' => bcrypt('password'),
            ]
        );
        
    }
}
