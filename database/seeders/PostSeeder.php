<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;


class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::firstOrCreate(
            ['title' => 'Mi primer post'],
            [
                'user_id' => User::where('email', 'julian@example.com')->first()->id,
                'content' => 'Hola a todos, soy Julian y soy cantante',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        Post::firstOrCreate(
            ['title' => 'Algo interesante'],
            [
                'user_id' => User::where('email', 'charliexcx@example.com')->first()->id,
                'content' => 'Nieva en el desierto del Sahara',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
    }
}
