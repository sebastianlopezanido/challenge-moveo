<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;

    public function setUp(): void
    {
        parent::setUp();

        // Crear roles,usuario y post
        Role::create(['name' => 'user']);
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');

        $this->post = Post::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function test_can_list_all_comments_for_a_post()
    {
        Comment::factory(5)->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this->getJson("/api/posts/{$this->post->id}/comments");

        $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => [ // Cada comentario en 'data' debe tener la estructura especificada
                    'id',
                    'content',
                    'user_id',
                    'post_id',
                    'created_at',
                    'updated_at',
                ]
            ],
            'message',
        ])
        ->assertJson([
            'status' => 'success',
            'message' => 'Comments retrieved successfully',
        ]);
    }

    #[Test]
    public function test_can_create_a_comment()
    {
        $commentData = [
            'content' => 'Que buen post!.',
        ];

        $response = $this->postJson("/api/posts/{$this->post->id}/comments", $commentData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['content' => 'Que buen post!.']);

        $this->assertDatabaseHas('comments', [
            'content' => 'Que buen post!.',
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function test_user_can_comment_on_another_users_post()
    {
        // Crear otro usuario y su post
        $otherUser = User::factory()->create();
        $otherUserPost = Post::factory()->create(['user_id' => $otherUser->id]);

        // Datos del comentario
        $commentData = [
            'content' => 'Alto post wacho!',
        ];

        // Actuar como el usuario actual y comentar en el post de otro usuario
        $response = $this->postJson("/api/posts/{$otherUserPost->id}/comments", $commentData);

        // Verificar que el comentario se haya creado correctamente
        $response->assertStatus(201)
                ->assertJsonFragment(['content' => 'Alto post wacho!']);

        // Verificar que el comentario está en la base de datos con la relación correcta
        $this->assertDatabaseHas('comments', [
            'content' => 'Alto post wacho!',
            'post_id' => $otherUserPost->id,
            'user_id' => $this->user->id,
        ]);
    }


    #[Test]
    public function test_can_show_a_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $response = $this->getJson("/api/comments/{$comment->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => $comment->content]);
    }

    #[Test]
    public function test_can_update_own_comment()
    {
        $comment = Comment::factory()->create(['post_id' => $this->post->id, 'user_id' => $this->user->id]);

        $updateData = [
            'content' => 'Que buen post! Xd.',
        ];

        $response = $this->putJson("/api/comments/{$comment->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment(['content' => 'Que buen post! Xd.']);

        $this->assertDatabaseHas('comments', $updateData);
    }

    #[Test]
     public function test_cannot_update_other_users_comment()
     {
         $otherUser = User::factory()->create();
         $comment = Comment::factory()->create(['post_id' => $this->post->id, 'user_id' => $otherUser->id]);
 
         $response = $this->putJson("/api/comments/{$comment->id}", [
             'content' => 'Malicious update.',
         ]);
 
         $response->assertStatus(403); // Forbidden
     }

    #[Test]
    public function test_can_delete_own_comment()
    {
       $comment = Comment::factory()->create(['post_id'=> $this->post->id, 'user_id' => $this->user->id]);
       $response = $this->deleteJson("/api/comments/{$comment->id}");

       $response->assertStatus(204);
       $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

    }

    #[Test]
    public function test_cannot_delete_other_users_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $this->post->id, 'user_id' => $otherUser->id]);

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(403); // Forbidden
    }


}
