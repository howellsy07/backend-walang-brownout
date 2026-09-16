<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Discrepancy;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect([
            ['name' => 'Howell Sy', 'email' => 'manager@walangbrownout.test', 'role' => 'Manager', 'department' => 'Management'],
            ['name' => 'Aryana Vecina', 'email' => 'supervisor@walangbrownout.test', 'role' => 'Supervisor', 'department' => 'Warehouse'],
            ['name' => 'Mika Santos', 'email' => 'sales@walangbrownout.test', 'role' => 'Staff', 'department' => 'Sales'],
            ['name' => 'Paolo Cruz', 'email' => 'warehouse@walangbrownout.test', 'role' => 'Staff', 'department' => 'Warehouse'],
        ])->map(fn ($row) => User::create($row));

        $products = collect([
            ['name' => 'Portable AC 1.5HP', 'code' => 'PAC-150', 'category' => 'Cooling', 'price' => 18999, 'stock_quantity' => 18, 'reorder_level' => 20, 'location' => 'Storage A'],
            ['name' => 'Portable AC 1.0HP', 'code' => 'PAC-100', 'category' => 'Cooling', 'price' => 14999, 'stock_quantity' => 0, 'reorder_level' => 12, 'location' => 'Sales Floor'],
            ['name' => 'Air Purifier Filter', 'code' => 'APF-009', 'category' => 'Filters', 'price' => 1299, 'stock_quantity' => 42, 'reorder_level' => 15, 'location' => 'Storage B', 'expiry_date' => now()->addDays(18)->toDateString()],
            ['name' => 'Air Purifier Pro', 'code' => 'APR-220', 'category' => 'Air Care', 'price' => 8499, 'stock_quantity' => 24, 'reorder_level' => 8, 'location' => 'Storage A'],
            ['name' => 'Smart Thermostat', 'code' => 'STH-045', 'category' => 'Controls', 'price' => 3699, 'stock_quantity' => 12, 'reorder_level' => 10, 'location' => 'Storage A'],
            ['name' => 'Thermostat Battery', 'code' => 'THB-012', 'category' => 'Accessories', 'price' => 299, 'stock_quantity' => 65, 'reorder_level' => 20, 'location' => 'Storage B'],
        ])->map(fn ($row) => Product::create($row));

        foreach (range(1, 12) as $month) {
            $product = $products[$month % $products->count()];
            InventoryTransaction::create([
                'product_id' => $product->id,
                'user_id' => $users[2]->id,
                'type' => 'sale',
                'quantity' => 2 + ($month % 5),
                'unit_price' => $product->price,
                'department' => 'Sales',
                'source_location' => 'Sales Floor',
                'reference_number' => 'SALE-2026-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT),
                'remarks' => 'Seeded sale record',
                'transaction_date' => now()->setMonth($month)->startOfMonth()->addDays(8)->toDateString(),
            ]);
        }

        InventoryTransaction::create(['product_id' => $products[3]->id, 'user_id' => $users[3]->id, 'type' => 'stock_in', 'quantity' => 12, 'unit_price' => $products[3]->price, 'department' => 'Warehouse', 'destination_location' => 'Storage A', 'reference_number' => 'DR-20419', 'transaction_date' => now()->toDateString()]);
        InventoryTransaction::create(['product_id' => $products[0]->id, 'user_id' => $users[2]->id, 'type' => 'sale', 'quantity' => 2, 'unit_price' => $products[0]->price, 'department' => 'Sales', 'source_location' => 'Sales Floor', 'reference_number' => 'OR-20420', 'transaction_date' => now()->toDateString()]);

        Discrepancy::create(['product_id' => $products[4]->id, 'submitted_by' => $users[3]->id, 'system_quantity' => 45, 'physical_quantity' => 12, 'difference' => -33, 'location' => 'Storage A', 'status' => 'Pending Review', 'remarks' => 'Physical count differed from the previous system record.']);
        Discrepancy::create(['product_id' => $products[2]->id, 'submitted_by' => $users[1]->id, 'system_quantity' => 42, 'physical_quantity' => 40, 'difference' => -2, 'location' => 'Storage B', 'status' => 'Pending Review']);

        ActivityLog::create(['user_id' => $users[3]->id, 'action' => 'recorded Stock In for Air Purifier Pro', 'record_type' => 'Transaction', 'record_id' => 13, 'department' => 'Warehouse', 'location' => 'Storage A', 'details' => 'Quantity: 12']);
        ActivityLog::create(['user_id' => $users[2]->id, 'action' => 'recorded Sale for Portable AC 1.5HP', 'record_type' => 'Transaction', 'record_id' => 14, 'department' => 'Sales', 'location' => 'Sales Floor', 'details' => 'Quantity: 2']);
        ActivityLog::create(['user_id' => $users[1]->id, 'action' => 'submitted a physical count for Air Purifier Filter', 'record_type' => 'Discrepancy', 'record_id' => 2, 'department' => 'Warehouse', 'location' => 'Storage B']);
    }
}
