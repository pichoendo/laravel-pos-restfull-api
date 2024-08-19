<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Member;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MemberTest extends TestCase
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

        $manage  = Permission::create(['name' => 'manage_member']);
        $consume = Permission::create(['name' => 'consume_member']);

        $role_su->givePermissionTo([$manage]);
        $role_ad->givePermissionTo([$manage]);
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

    public function test_it_success_to_create_member_act_as_super_admin(): void
    {

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson('/api/v1/member', [
            'name' => "Suryono",
            'email' => "suryono@gmail.com",
            'phone_no' => "0813-2000-193",
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_create_member_act_as_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->postJson('/api/v1/member', [
            'name' => "Suryono",
            'email' => "suryono@gmail.com",
            'phone_no' => "0813-2000-193",
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_create_member_act_as_cashier(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson('/api/v1/member', [
            'name' => "Suryono",
            'email' => "suryono@gmail.com",
            'phone_no' => "0813-2000-193",
        ]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_update_member_act_as_super_admin(): void
    {
        $uuid = Member::factory()->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/member/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_update_member_act_as_admin(): void
    {
        $uuid = Member::factory()->create()->uuid;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->putJson("/api/v1/member/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_fail_to_update_member_act_as_cashier(): void
    {
        $uuid = Member::factory()->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->putJson("/api/v1/member/$uuid", ["name" => "coupon 2"]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_delete_member_act_as_super_admin(): void
    {
        $uuid = Member::factory()->create()->uuid;
        $response = $this->withToken($this->su->token, 'Bearer')->deleteJson("/api/v1/member/$uuid");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_delete_member_act_as_admin(): void
    {
        $uuid = Member::factory()->create()->uuid;
        $response = $this->withToken($this->ad->token, 'Bearer')->deleteJson("/api/v1/member/$uuid");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_delete_member_act_as_cashier(): void
    {
        $uuid = Member::factory()->create()->uuid;
        $response = $this->withToken($this->csh->token, 'Bearer')->deleteJson("/api/v1/member/$uuid");
        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_member_act_as_super_admin(): void
    {
        $member = Member::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/member/{$member->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $member->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_member_act_as_admin(): void
    {
        $member = Member::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/member/{$member->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $member->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_member_act_as_cashier(): void
    {
        $member = Member::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/member/{$member->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name', $member->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_when_get_list_member_act_as_super_admin(): void
    {
        Member::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/member");
   
        $response->assertJsonCount(10, 'result.data');
        $response->assertJsonStructure([
            'message',
            'result'
        ]);


        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/member?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }
    public function test_it_success_when_get_list_member_act_as_admin(): void
    {
        Member::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/member");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/member?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }

    public function test_it_success_when_get_list_member_act_as_cashier(): void
    {
        Member::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/member");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/member?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }

    public function test_it_fail_when_get_member_with_unknown_uuid(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/member/unkwons-id");

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }
}
