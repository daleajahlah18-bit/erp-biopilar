<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemMenu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            ['name' => 'Dashboard', 'parent' => 'Dashboard'],
            
            ['name' => 'Products', 'parent' => 'Master Data'],
            ['name' => 'Suppliers', 'parent' => 'Master Data'],
            ['name' => 'Customers', 'parent' => 'Master Data'],
            ['name' => 'Units', 'parent' => 'Master Data'],
            ['name' => 'Projects', 'parent' => 'Master Data'],
            ['name' => 'Warehouses', 'parent' => 'Master Data'],

            ['name' => 'Purchase Release', 'parent' => 'Purchasing'],
            ['name' => 'Goods Receipt', 'parent' => 'Purchasing'],
            ['name' => 'Purchase Payment', 'parent' => 'Purchasing'],
            ['name' => 'Accounts Payable', 'parent' => 'Purchasing'],

            ['name' => 'Bill of Material', 'parent' => 'Production'],
            ['name' => 'Production Order', 'parent' => 'Production'],
            ['name' => 'Project Production', 'parent' => 'Production'],

            ['name' => 'Sales Order', 'parent' => 'Sales'],
            ['name' => 'Sales Invoice', 'parent' => 'Sales'],
            ['name' => 'Sales Payment', 'parent' => 'Sales'],
            ['name' => 'Accounts Receivable', 'parent' => 'Sales'],

            ['name' => 'Project Progress', 'parent' => 'Project Report'],
            ['name' => 'Daily Report', 'parent' => 'Project Report'],
            ['name' => 'Report Phase', 'parent' => 'Project Report'],
            ['name' => 'Survey Report', 'parent' => 'Project Report'],

            ['name' => 'Product Stock', 'parent' => 'Inventory'],
            ['name' => 'Stock Transfer', 'parent' => 'Inventory'],
            ['name' => 'Stock Opname', 'parent' => 'Inventory'],
            ['name' => 'Item Journal', 'parent' => 'Inventory'],

            ['name' => 'Asset Dashboard', 'parent' => 'Asset Management'],
            ['name' => 'Master Categories', 'parent' => 'Asset Management'],
            ['name' => 'Master Assets', 'parent' => 'Asset Management'],
            ['name' => 'Asset Reports', 'parent' => 'Asset Management'],

            ['name' => 'Users', 'parent' => 'User Management'],
            ['name' => 'Roles', 'parent' => 'User Management'],
            ['name' => 'Activity Logs', 'parent' => 'User Management'],
        ];

        foreach ($menus as $menu) {
            SystemMenu::updateOrCreate(['name' => $menu['name']], $menu);
        }
    }
}
