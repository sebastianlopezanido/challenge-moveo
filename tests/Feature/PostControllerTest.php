<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // Crear roles y usuarios
        Role::create(['name' => 'User']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    #[Test]
    public function test_can_list_all_posts()
    {
        Post::factory(10)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    #[Test]
    public function test_can_create_a_post()
    {
        $postData = [
            'title' => 'New Post Title',
            'content' => 'This is the content of the post.',
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'New Post Title']);

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
            'title' => 'Updated Title',
            'content' => 'Updated content.',
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('posts', $updateData);
    }

    #[Test]
    public function test_cannot_update_other_users_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->putJson("/api/posts/{$post->id}", [
            'title' => 'Malicious Update',
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
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    #[Test]
    public function test_cannot_delete_other_users_post()
    {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403); // Forbidden
    }
}
