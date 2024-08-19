<?php

namespace Tests\Feature;

use App\Services\EmployeeService;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public $role;
    public $token = "";

    public function setUp(): void
    {
        parent::setUp();
        $roleService = new RoleService();

        $this->role = $roleService->create([
            'name'  => 'super',
            'basic_salary'  => 500000,
            'commission_percentage'  => 0.1,

        ]);

        $service = new EmployeeService();
        $service->create([
            'name'  => 'bagus',
            'phone_no'  => '12343',
            'role_id' =>  $this->role->id,
            'address' => '',
            'email' => 'palintang@gmail.com',
            'username' => 'super_vise',
            'password' => '123123',
            'password_confirmation' => '123123',
        ]);
    }



    public function test_it_success_to_login_with_valid_credentials(): void
    {
        $response = $this->post('/api/v1/login', [
            'password' => '123123',
            "username" => 'super_vise'
        ]);
        $response->assertStatus(200);

        $this->token = $response->json()['result']['token'];
        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'token',
                'user' => [
                    'id',
                    'uuid',
                    'name',
                    'email',
                    'role_id',
                ],
            ],
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])->get('/api/v1/me');

        $response2->assertStatus(200);
        $response2->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => "Bearer $this->token"
        ])->post('/api/v1/logout');

        $response2->assertStatus(200);
        $response2->assertJson([
            "success" => true,
            "message" => "logout successful",
            "result" => []
        ]);
    }


    public function test_it_fails_to_login_with_invalid_credentials(): void
    {
        $response = $this->post('/api/v1/login', [
            'password' => '1231232',
            "username" => 'super_viseor'
        ])->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
            'errors' => []
        ]);

        $response->assertStatus(401);
    }

    public function test_it_fails_to_login_with_uncomplete_form(): void
    {
        $response = $this->post('/api/v1/login', [
            "username" => 'super_viseor'
        ])->assertJson([
            "message" => "Validation Failed",
        ])->assertJsonStructure([
            'errors',
        ]);

        $response->assertStatus(422);
    }

    public function test_it_fails_when_reach_rate_limited(): void
    {

        for ($i = 0; $i < 60; $i++) {
            $this->post('/api/v1/login', [
                'password' => '123123',
                'username' => 'super_vise'
            ]);
        }
        $response = $this->post('/api/v1/login', [
            'password' => '123123',
            "username" => 'super_vise'
        ]);

        $response->assertStatus(429);
    }
}
