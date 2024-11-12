<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Comment;


class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // Crear roles y usuarios
        Role::create(['name' => 'user']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    #[Test]
    public function test_can_list_all_posts()
    {
        Post::factory(10)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data',
            'message',
        ])
        ->assertJson([
            'status' => 'success',
            'message' => 'Posts retrieved successfully',
        ]);
    }

    #[Test]
    public function test_can_create_a_post()
    {
        $postData = [
            'title' => 'Este es un nuevo post',
            'content' => 'Este es el contenido del post.',
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'Este es un nuevo post']);

        $this->assertDatabaseHas('posts', $postData);
    }

    #[Test]
    public function test_can_show_a_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $post->title]);
    }

    #[Test]
    public function test_can_update_own_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Este es un post actualizado',
            'content' => 'Este es el contenido actualizado del post.',
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Este es un post actualizado']);

        $this->assertDatabaseHas('posts', $updateData);
    }

    #[Test]
    public function test_cannot_update_other_users_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Malicioso Update',
            'content' => 'Unauthorized content.',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function test_can_delete_own_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204); // No Content
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    #[Test]
    public function test_can_soft_delete_post_with_comments()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);

        foreach ($comments as $comment) {
            $this->assertSoftDeleted('comments', ['id' => $comment->id]);
        }
    }

    #[Test]
    public function test_cannot_delete_other_users_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403); // Forbidden
    }

    #[Test]
    public function test_can_retrieve_paginated_posts_with_default_limit()
    {
        Post::factory(15)->create(); // Crear 15 posts

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                ->assertJsonFragment(['status' => 'success'])
                ->assertJsonStructure([
                    'data' => [
                        'posts' => [
                            '*' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
                        ],
                        'links', // Enlaces de paginación
                        'meta'   // Información de paginación
                    ],
                    'message',
                ]);

        // Verificar que se devuelvan 10 posts de manera predeterminada
        $this->assertCount(10, $response->json('data.posts'));
    }

    #[Test]
    public function test_can_retrieve_paginated_posts_with_custom_limit()
    {
        Post::factory(20)->create(); // Crear 20 posts

        // Establecer un límite de 5 posts por página
        $response = $this->getJson('/api/posts?limit=5');

        $response->assertStatus(200)
                ->assertJsonFragment(['status' => 'success'])
                ->assertJsonStructure([
                    'data' => [
                        'posts' => [
                            '*' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
                        ],
                        'links',
                        'meta'
                    ],
                    'message',
                ]);

        // Verificar que se devuelvan 5 posts según el límite
        $this->assertCount(5, $response->json('data.posts'));
    }

    #[Test]
    public function test_can_retrieve_paginated_posts_on_second_page()
    {
        Post::factory(15)->create(); // Crear 15 posts

        // Solicitar la segunda página con el límite predeterminado de 10 posts por página
        $response = $this->getJson('/api/posts?page=2');

        $response->assertStatus(200)
                ->assertJsonFragment(['status' => 'success'])
                ->assertJsonStructure([
                    'data' => [
                        'posts' => [
                            '*' => ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
                        ],
                        'links',
                        'meta'
                    ],
                    'message',
                ]);

        // Verificar que en la segunda página solo haya 5 posts (15 posts en total con 10 en la primera página)
        $this->assertCount(5, $response->json('data.posts'));
    }
}
