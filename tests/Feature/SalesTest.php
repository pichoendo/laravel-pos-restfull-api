<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Member;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalesTest extends TestCase
{

    use RefreshDatabase;

    public $token = "";
    public $su, $ad, $csh;
    public $token_caschier = "";
    public $token_admin = "";
    public $categories = array();
    public $items = array();
    public $members = array();
    public function setUp(): void
    {
        parent::setUp();

        $roleService = new RoleService();
        if (sizeof($this->categories) == 0) {
            $this->categories = Category::factory()->count(20)->create();
        }
        if (sizeof($this->items) == 0) {
            $this->items = Item::factory()->withCategory($this->categories)->count(20)->create();
            foreach ($this->items as $item) {
                ItemStock::factory()->withItem($item->id)->create();
            }
        }
        if (sizeof($this->members) == 0) {
            $this->members = Member::factory()->count(20)->create();
        }
        $role_su = Role::where('name', 'super')->first();
        if (!$role_su)
            $role_su = $roleService->create([
                'name'  => 'super',
                'basic_salary'  => 500000,
                'commission_percentage'  => 0.05,
            ]);

        $role_ad = Role::where('name', 'admin')->first();
        if (!$role_ad)
            $role_ad = $roleService->create([
                'name'  => 'admin',
                'basic_salary'  => 500000,
                'commission_percentage'  => 0.01,
            ]);

        $role_csh = Role::where('name', 'cashier')->first();
        if (!$role_csh)
            $role_csh = $roleService->create([
                'name'  => 'cashier',
                'basic_salary'  => 500000,
                'commission_percentage'  => 0.01,
            ]);

        $manage = Permission::where('name', 'manage_sales')->first();
        if (!$manage)
            $manage  = Permission::create(['name' => 'manage_sales']);

        $consume = Permission::where('name', 'consume_sales')->first();
        if (!$consume)
            $consume = Permission::create(['name' => 'consume_sales']);

        $role_su->givePermissionTo([$manage]);

        $role_csh->givePermissionTo([$manage]);

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

    public function test_it_success_to_create_sales_act_as_super_admin(): void
    {
        $item = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->su->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->su->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->su->role->commission_percentage), $this->su->commission);
        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
    }


    public function test_it_success_to_create_sales_act_as_admin(): void
    {
        $item = $this->items[rand(0, 19)];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->ad->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => 500,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => 50000,
            'total'         => 50500,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => 4,
                "price"      => 12500,
                "sub_total"  => 50000
            ]]
        ]);

        $response->assertStatus(403);
    }

    public function test_it_fail_to_create_sales_act_as_cashier(): void
    {
        $item = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->csh->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->csh->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->csh->role->commission_percentage), $this->csh->commission);
        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
    }

    public function test_it_success_to_create_sales_on_hold_act_as_super_admin(): void
    {
        $item = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->su->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'hold',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->su->commission);

        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
    }

    public function test_it_success_to_create_sales_on_hold_act_as_cashier(): void
    {
        $item = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->csh->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'hold',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->csh->commission);

        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
    }

    public function test_it_success_to_create_sales_on_hold_then_success_act_as_super_admin(): void
    {
        $item = array();
        $qty = array();
        $cart = array();
        $old_stock = array();
        $cart = array();
        $item[] = $this->items[rand(0, 6)];
    
        $old_stock[] = $item[0]->stock_count;


        $qty[] = rand(1, $old_stock[0]);


        $old_employe_point = $this->su->commission;
        $subtotal = $item[0]->price * $qty[0];
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;

        $cart[] = [
            "item_id"    => $item[0]->id,
            "qty"        => $qty[0],
            "price"      => $item[0]->price,
            "sub_total"  =>  $item[0]->price * $qty[0]
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'hold',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => $cart
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);

        $sales = $response->json()['result'];

        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->su->commission);

        $this->assertNotEquals($old_stock[0], $item[0]->stock_count);
        $this->assertEquals($old_stock[0] - $qty[0], $item[0]->stock_count);

        $cart[0] = [
            "item_id"    => $item[0]->id,
            "qty"        => $qty[0],
            "price"      => $item[0]->price,
            "sub_total"  =>  $item[0]->price * $qty[0]
        ];
        $qty[0] = rand(1, $old_stock[0]);


        $qty = array_reduce($cart, function ($a, $b) {
            return $a + $b['qty'];
        }, 0);

        $subtotal = array_reduce($cart, function ($a, $b) {
            return $a + ($b['qty'] * $b['price']);
        }, 0);




        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/sales/{$sales['uuid']}", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => $cart,

        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];

        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->su->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->su->role->commission_percentage), $this->su->commission);
        $this->assertNotEquals($old_stock[0], $item[0]->stock_count);
        $this->assertEquals($old_stock[0] - $cart[0]['qty'], $item[0]->stock_count);
    }


    public function test_it_success_to_create_sales_on_hold_then_success_act_as_cashier(): void
    {
        $item = array();
        $qty = array();
        $cart = array();
        $old_stock = array();
        $cart = array();
        $item[] = $this->items[rand(0, 6)];
        $item[] = $this->items[rand(7, 19)];

        $old_stock[] = $item[0]->stock_count;
        $old_stock[] = $item[1]->stock_count;

        $qty[] = rand(1, $old_stock[0]);
        $qty[] = rand(1, $old_stock[1]);

        $old_employe_point = $this->csh->commission;
        $subtotal = $item[0]->price * $qty[0];
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;

        $cart[] = [
            "item_id"    => $item[0]->id,
            "qty"        => $qty[0],
            "price"      => $item[0]->price,
            "sub_total"  =>  $item[0]->price * $qty[0]
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'hold',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => $cart
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);

        $sales = $response->json()['result'];

        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->su->commission);

        $this->assertNotEquals($old_stock[0], $item[0]->stock_count);
        $this->assertEquals($old_stock[0] - $qty[0], $item[0]->stock_count);

        $cart[] = [
            "item_id"    => $item[1]->id,
            "qty"        => $qty[1],
            "price"      => $item[1]->price,
            "sub_total"  =>  $item[1]->price * $qty[1]
        ];

        $qty = array_reduce($cart, function ($a, $b) {
            return $a + $b['qty'];
        }, 0);

        $subtotal = array_reduce($cart, function ($a, $b) {
            return $a + ($b['qty'] * $b['price']);
        }, 0);




        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->csh->token}",
        ])->putJson("/api/v1/sales/{$sales['uuid']}", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => $cart,

        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];

        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->csh->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->csh->role->commission_percentage), $this->csh->commission);
        $this->assertNotEquals($old_stock[0], $item[0]->stock_count);
        $this->assertEquals($old_stock[0] - $cart[0]['qty'], $item[0]->stock_count);
        $this->assertNotEquals($old_stock[1], $item[1]->stock_count);
        $this->assertEquals($old_stock[1] - $cart[1]['qty'], $item[1]->stock_count);
    }

    public function test_it_success_to_create_sales_on_hold_then_canceled_act_as_super_admin(): void
    {
        $item = $this->items[rand(0, 19)];
        $item2 = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->su->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $cart = [[
            "item_id"    => $item->id,
            "qty"        => $qty,
            "price"      => $item->price,
            "sub_total"  =>  $item->price * $qty
        ]];
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'hold',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => $cart
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $old_stock2 = $item2->stock_count;
        $qty2 = rand(1, $item2->stock_count);

        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->su->commission);

        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
        $qty = rand(1, $item2->stock_count);
        $cart[] = [
            "item_id"    => $item2->id,
            "qty"        => $qty2,
            "price"      => $item2->price,
            "sub_total"  =>  $item2->price * $qty2
        ];

        $qty = array_reduce($cart, function ($a, $b) {
            return $a + $b['qty'];
        }, 0);

        $subtotal = array_reduce($cart, function ($a, $b) {
            return $a + ($b['qty'] * $b['price']);
        }, 0);

        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->putJson("/api/v1/sales/{$sales['uuid']}", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'canceled',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [],

        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertEquals($old_employe_point, $this->su->commission);
        $this->assertEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock2, $item2->stock_count);
    }

    public function test_it_success_to_get_sales_act_as_super_admin(): void
    {
        $item = $this->items[rand(0, 19)];
        $old_stock = $item->stock_count;
        $qty = rand(1, $item->stock_count);
        $old_employe_point = $this->su->commission;
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->su->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->su->role->commission_percentage), $this->su->commission);
        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->su->token}",
        ])->getJson("/api/v1/sales/{$sales['uuid']}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
        $sales = $response->json()['result'];
        $this->assertEquals($sales['tax'], $tax);
        $this->assertEquals($sales['total'], $total);
        $this->assertNotEquals($old_employe_point, $this->su->commission);
        $this->assertEquals($old_employe_point + ($subtotal * $this->su->role->commission_percentage), $this->su->commission);
        $this->assertNotEquals($old_stock, $item->stock_count);
        $this->assertEquals($old_stock - $qty, $item->stock_count);
    }

    public function test_it_success_to_get_sales_act_as_admin(): void
    {
        $item = $this->items[rand(0, 19)];
        $qty = rand(1, $item->stock_count);
        $subtotal = $item->price * $qty;
        $tax = $subtotal * 0.1;
        $total = $subtotal + $tax;
        $response = $this->actingAs($this->su)->postJson("/api/v1/sales", [
            'tax'           => $tax,
            'discount'      => 0,
            'status'        => 'success',
            'sub_total'     => $subtotal,
            'total'         => $total,
            "cart"    => [[
                "item_id"    => $item->id,
                "qty"        => $qty,
                "price"      => $item->price,
                "sub_total"  =>  $item->price * $qty
            ]]
        ]);

        $sales = $response->json()['result'];

        $response2 = $this->actingAs($this->ad)->getJson("/api/v1/sales/{$sales['uuid']}");

        $response2->assertStatus(403);
        $response2->assertJsonStructure([
            'success',
            'message',
            'result'
        ]);
    }
}
