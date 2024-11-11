<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'User']);
    }

    #[Test]
    public function test_registers_a_new_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Mario Bros',
            'email' => 'mariobros@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'mariobros@example.com',
        ]);
    }

    #[Test]
    public function test_logs_in_a_user()
    {
        $user = User::factory()->create([
            'email' => 'leomessi@example.com',
            'password' => Hash::make('password'),
        ]);


        $response = $this->postJson('/api/login', [
            'email' => 'leomessi@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                 ]);
    }

    #[Test]
    public function test_logs_out_a_user()
    {
        $user = User::factory()->create([
            'name' => 'Julian Casablancas',
            'email' => 'minorbutmayor@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Logged out successfully',
                 ]);
    }

    #[Test]
    public function test_user_can_see_auth_routes(): void
    {

        $user = User::factory()->create([
            'email' => 'leomessi@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'leomessi@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                 ]);

        //fronted

        $token = $response->json('access_token');

        $response2 = $this->withHeader('Authorization',"Bearer {$token}")->get('/api/user');

        $response2->assertStatus(200);
        $response2->assertJson([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
