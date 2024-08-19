<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\Member;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $role = Role::create([
                'name' => 'Supervisior',
                'basic_salary' => 1500000,
                'commission_percentage' => 0.01
            ]);

           $e= Employee::create([
                'name' => 'Echo',
                'username' => 'super_vise',
                'email' => 'roomworkstudio@gmail.com',
                'role_id' => $role->id,
                'password' => Hash::make('123123'),
            ]);

            $manages[]  = Permission::create(['name' => 'manage_category']);
            $manages[] = Permission::create(['name' => 'manage_item']);
            $consume = Permission::create(['name' => 'consume_category']);
    
            $role->givePermissionTo($manages);
            $role->givePermissionTo($consume);
            $e->assignRole($role);
            Member::create([
                'name' => 'Member',
                'email' => 'roomworkstudico@gmail.com',
                'phone_no' => '0823',
            ]);

            $cat=Category::create([
                'name' => 'Asian Food',
                'images' => "",
            ]);

            $item = Item::create([
                'name' => 'Asian Food',
                'category_id' => $cat->id,
                'price' => 200,
            ]);

            ItemStock::create([
                'item_id' => $item->id,
                'cogs' => 150,
                'qty' => 200,
            ]);
        });
    }
}
