<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // Crear roles de usuario y administrador
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Crear un usuario administrador y un usuario común
        $this->admin = User::factory()->create(['role_id' => Role::where('name', 'admin')->first()->id]);
        $this->user = User::factory()->create(['role_id' => Role::where('name', 'user')->first()->id]);
    }

    #[Test]
    public function test_an_admin_can_access_admin_endpoint()
    {
        // Actuar como administrador y hacer la solicitud al endpoint /admin
        $response = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Admin access granted',
                     'data' => [
                         'id' => $this->admin->id,
                         'name' => $this->admin->name,
                     ],
                 ]);
    }

    #[Test]
    public function test_a_non_admin_user_cannot_access_admin_endpoint()
    {
        // Actuar como usuario común y hacer la solicitud al endpoint /admin
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/admin');

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Unauthorized access',
                 ]);
    }
}
