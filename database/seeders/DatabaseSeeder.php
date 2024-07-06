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

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Role::create([
                'name' => 'Supervisior',
                'basic_salary' => 1500000,
                'commission_percentage' => 0.01
            ]);
            Employee::create([
                'name' => 'Echo',
                'username' => 'super_vise',
                'email' => 'roomworkstudio@gmail.com',
                'role_id' => 1,
                'password' => Hash::make('123123'),
            ]);

            Member::create([
                'name' => 'Member',
                'email' => 'roomworkstudio@gmail.com',
                'phone_no' => '0823',
            ]);

            Category::create([
                'name' => 'Asian Food',
                'images' => "",
            ]);

            $item = Item::create([
                'name' => 'Asian Food',
                'category_id' => 1,
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
