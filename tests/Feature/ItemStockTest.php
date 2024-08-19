<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStock;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ItemStockTest extends TestCase
{
    use RefreshDatabase;

    public $token = "";
    public $su, $ad, $csh;
    public $token_caschier = "";
    public $token_admin = "";
    public $categories = array();
    public $items = "";

    public function setUp(): void
    {
        parent::setUp();

        $roleService = new RoleService();
        $this->categories = Category::factory()->count(20)->create();
        $this->items = Item::factory()->withCategory($this->categories)->create();
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

        $manage  = Permission::create(['name' => 'manage_item_stock']);
        $consume = Permission::create(['name' => 'consume_item_stock']);

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

    public function test_it_success_to_create_itemStock_act_as_super_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/item/{$this->items->uuid}/stock", [
            'cogs' => 5000,
            'qty' => 200,
        ]);
      
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $response->assertJsonPath('result.item_name',$this->items->name);
        $response->assertJsonPath('result.cogs',5000);
        $response->assertJsonPath('result.qty',200);
    }


    public function test_it_success_to_create_itemStock_act_as_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->postJson("/api/v1/item/{$this->items->uuid}/stock", [
            'cogs' => 5000,
            'qty' => 200,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $response->assertJsonPath('result.item_name',$this->items->name);
        $response->assertJsonPath('result.cogs',5000);
        $response->assertJsonPath('result.qty',200);
    }

    public function test_it_fail_to_create_itemStock_act_as_cashier(): void
    {
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson("/api/v1/item/{$this->items->uuid}/stock", [
            'cogs' => 5000,
            'qty' => 200,
        ]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    /* public function test_it_success_to_update_itemStock_act_as_super_admin(): void
    {
        $uuid = ItemStock::factory()->withItem($this->items->id)->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/item/stock/$uuid", ["qty" => 300, 'cogs' => 1400]);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_success_to_update_itemStock_act_as_admin(): void
    {

        $uuid = ItemStock::factory()->withItem($this->items->id)->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/item/stock/$uuid", ["qty" => 300, 'cogs' => 1400]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }


    public function test_it_fail_to_update_itemStock_act_as_cashier(): void
    {
        $uuid = ItemStock::factory()->withItem($this->items->id)->create()->uuid;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/item/{$this->items->uuid}/stock/$uuid", ["qty" => 300]);

        $response->assertStatus(403);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

  */
    public function test_it_success_when_get_itemStock_act_as_super_admin(): void
    {
        $itemStock = ItemStock::factory()->withItem($this->items->id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/item/stock/{$itemStock->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.qty', $itemStock->qty);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_itemStock_act_as_admin(): void
    {
        $itemStock = ItemStock::factory()->withItem($this->items->id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->getJson("/api/v1/item/stock/{$itemStock->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.qty', $itemStock->qty);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }

    public function test_it_success_when_get_itemStock_act_as_cashier(): void
    {
        $itemStock = ItemStock::factory()->withItem($this->items->id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->getJson("/api/v1/item/stock/{$itemStock->uuid}");

        $response->assertStatus(200);
        $response->assertJsonPath('result.qty', $itemStock->qty);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }



    public function test_it_fail_when_get_itemStock_with_unknown_uuid(): void
    {
        $itemStock = ItemStock::factory()->withItem($this->items->id)->create();
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/item/stock/dfsdfsere");

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }
}
