<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public $token = "";
    public $su, $ad, $csh;
    public $token_caschier = "";
    public $token_admin = "";

    public function setUp(): void
    {
        parent::setUp();

        $roleService = new RoleService();

        $role_su = $roleService->create([
            'name'  => 'super',
            'basic_salary'  => 500000,
            'commission_percentage'  => 0.1,
        ]);

        $role_ad = $roleService->create([
            'name'  => 'admin',
            'basic_salary'  => 500000,
            'commission_percentage'  => 0.1,
        ]);

        $role_csh = $roleService->create([
            'name'  => 'cashier',
            'basic_salary'  => 500000,
            'commission_percentage'  => 0.1,
        ]);

        $manage  = Permission::create(['name' => 'manage_employee']);
        $consume = Permission::create(['name' => 'consume_employee']);

        $role_su->givePermissionTo([$manage]);
        $role_ad->givePermissionTo([$consume]);
        $role_csh->givePermissionTo([$consume]);

        $this->su = Employee::factory()->withRole($role_su->id)->create();
        $this->su->assignRole($role_su);
        $this->su->token = $this->su->createToken("authFor$role_su->id")->plainTextToken;

        $this->ad = Employee::factory()->withRole($role_ad->id)->create();
        $this->ad->assignRole($role_ad);
        $this->ad->token = $this->ad->createToken("authFor$role_ad->id")->plainTextToken;

        $this->csh = Employee::factory()->withRole($role_csh->id)->create();
        $this->csh->assignRole($role_csh);
        $this->csh->token = $this->csh->createToken("authFor$role_csh->id")->plainTextToken;
    }

    public function test_it_success_to_create_employee_act_as_super_admin(): void
    {

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson('/api/v1/employee', [
            'name' => "Suryono",
            'username' => "suryono",
            'email' => "suryono@gmail.com",
            'role_id' => $this->su->role_id,
            'address' => "Jl Kemangni no 12 Raya",
            'phone_no' => "0813-2000-193",
            'password' => "123123",
            'password_confirmation' => "123123"
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_create_employee_act_as_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->postJson('/api/v1/employee', [
            'name' => "Suryono",
            'username' => "suryono",
            'email' => "suryono@gmail.com",
            'role_id' => "1",
            'address' => "Jl Kemangni no 12 Raya",
            'phone_no' => "0813-2000-193",
            'password' => Hash::make("password")
        ]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_create_employee_act_as_cashier(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson('/api/v1/employee', [
            'name' => "Suryono",
            'username' => "suryono",
            'email' => "suryono@gmail.com",
            'role_id' => "1",
            'address' => "Jl Kemangni no 12 Raya",
            'phone_no' => "0813-2000-193",
            'password' => Hash::make("password")
        ]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_update_employee_act_as_super_admin(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/employee/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_update_employee_act_as_admin(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->putJson("/api/v1/employee/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_fail_to_update_employee_act_as_cashier(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->putJson("/api/v1/employee/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_delete_employee_act_as_super_admin(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;
        $response = $this->withToken($this->su->token, 'Bearer')->deleteJson("/api/v1/employee/$uuid");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_delete_employee_act_as_admin(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;
        $response = $this->withToken($this->ad->token, 'Bearer')->deleteJson("/api/v1/employee/$uuid");
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_delete_employee_act_as_cashier(): void
    {
        $uuid = Employee::factory()->withRole($this->su->role_id)->create()->uuid;
        $response = $this->withToken($this->csh->token, 'Bearer')->deleteJson("/api/v1/employee/$uuid");
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_employee_act_as_super_admin(): void
    {
        $employee = Employee::factory()->withRole($this->su->role_id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/employee/{$employee->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $employee->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_employee_act_as_admin(): void
    {
        $employee = Employee::factory()->withRole($this->su->role_id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/employee/{$employee->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $employee->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_employee_act_as_cashier(): void
    {
        $employee = Employee::factory()->withRole($this->su->role_id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/employee/{$employee->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $employee->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_when_get_list_employee_act_as_super_admin(): void
    {
        Employee::factory()->withRole($this->su->role_id)->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/employee");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/employee?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(8, 'result.data');
    }

    public function test_it_success_when_get_list_employee_act_as_admin(): void
    {
        Employee::factory()->withRole($this->su->role_id)->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/employee");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/employee?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(8, 'result.data');
    }

    public function test_it_success_when_get_list_employee_act_as_cashier(): void
    {
        Employee::factory()->withRole($this->su->role_id)->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/employee");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/employee?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(8, 'result.data');
    }

    public function test_it_fail_when_get_employee_with_unknown_uuid(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/employee/unkwons-id");

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }
}
