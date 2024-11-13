<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        Comment::firstOrCreate(
            ['content' => 'Hola, soy charlie!'],
            [
                'post_id' => Post::where('title', 'Mi primer post')->first()->id,
                'user_id' => User::where('email', 'charliexcx@example.com')->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        Comment::firstOrCreate(
            ['content' => 'Wow!'],
            [
                'post_id' => Post::where('title', 'Algo interesante')->first()->id,
                'user_id' => User::where('email', 'julian@example.com')->first()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
    }
}
