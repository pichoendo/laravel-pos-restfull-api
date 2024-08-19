<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Item;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;


class CategoryTest extends TestCase
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

        $manage  = Permission::create(['name' => 'manage_category']);
        $manages = Permission::create(['name' => 'manage_item']);
        $consume = Permission::create(['name' => 'consume_category']);

        $role_su->givePermissionTo([$manage, $manages]);
        $role_ad->givePermissionTo($manage);
        $role_csh->givePermissionTo($consume);

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

    public function test_it_success_to_create_category_act_as_super_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson('/api/v1/category/', ["name" => "category", "file" => ""]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_create_category_act_as_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->postJson('/api/v1/category/', ["name" => "category", "file" => ""]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_create_category_act_as_cashier(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson('/api/v1/category/', ["name" => "category", "file" => ""]);

        $response->assertStatus(403);
    }

    public function test_it_success_to_update_category_act_as_super_admin(): void
    {
        $uuid = Category::factory()->create()->uuid;


        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/category/$uuid", ["name" => "category2", "file" => ""]);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_update_category_act_as_admin(): void
    {
        $uuid = Category::factory()->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->putJson("/api/v1/category/$uuid", ["name" => "category2", "file" => ""]);
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_fail_to_update_category_act_as_cashier(): void
    {


        $uuid = Category::factory()->create()->uuid;
        $response = $this->withToken($this->csh->token, 'Bearer')->putJson("/api/v1/category/$uuid", ["name" => "category2", "file" => ""]);
        $response->assertStatus(403);


        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_delete_category_act_as_super_admin(): void
    {
        $response = $this->withToken($this->ad->token, 'Bearer')->postJson('/api/v1/category/', ["name" => "category", "file" => ""]);

        $response->assertStatus(201);

        $uuid = json_decode($response->getContent(), null)->result->uuid;

        $response = $this->withToken($this->ad->token, 'Bearer')->deleteJson("/api/v1/category/$uuid");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_to_delete_category_act_as_admin(): void
    {
        $response = $this->withToken($this->ad->token, 'Bearer')->postJson('/api/v1/category/', ["name" => "category", "file" => ""]);

        $response->assertStatus(201);

        $uuid = json_decode($response->getContent(), null)->result->uuid;

        $response = $this->withToken($this->ad->token, 'Bearer')->deleteJson("/api/v1/category/$uuid");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_fail_to_delete_category_act_as_cashier(): void
    {

        $uuid = Category::factory()->create()->uuid;
        $response = $this->withToken($this->csh->token, 'Bearer')->deleteJson("/api/v1/category/$uuid");

        $response->assertStatus(403);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_category_act_as_super_admin(): void
    {
        $category = Category::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category/{$category->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name',$category->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_category_act_as_admin(): void
    {
        $category = Category::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/category/{$category->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name',$category->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_category_act_as_cashier(): void
    {
        $category = Category::factory()->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/category/{$category->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.name',$category->name);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_list_category_act_as_super_admin(): void
    {
        Category::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }

    public function test_it_success_when_get_list_category_act_as_admin(): void
    {
        Category::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/category");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/category?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }

    public function test_it_success_when_get_list_category_act_as_cashier(): void
    {
        Category::factory()->count(15)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/category");

        $response->assertJsonStructure([
            'message',
            'result'
        ]);

        $response->assertJsonCount(10, 'result.data');
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/category?page=2");

        $response->assertJsonStructure([
            'success',
            'message',
            'result' => [
                'data'
            ]
        ]);
        $response->assertJsonCount(5, 'result.data');
    }






    public function test_it_fail_when_get_category_with_unknown_uuid(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category/unkwons-id");

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_items_category(): void
    {
        $categories = Category::factory()->count(10)->create();
        $items = Item::factory()->withCategory([$categories[0]])->count(10)->create();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category/{$categories[0]->uuid}/items");

        $response->assertStatus(200);
        $response->assertJsonCount(sizeof($items), 'result.data');

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/item/{$items[5]->uuid}", ['category_id' => $categories[7]->id]);

        $response->assertStatus(200);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/category/{$categories[0]->uuid}/items");

        $response->assertStatus(200);
        $response->assertJsonCount(sizeof($items) - 1, 'result.data');
    }
}
