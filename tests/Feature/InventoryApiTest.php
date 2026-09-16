<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_products_can_be_listed(): void
    {
        $this->seed();
        $this->getJson('/api/products')->assertOk()->assertJsonCount(6, 'data');
    }

    public function test_a_product_can_be_created(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Window AC 1.0HP',
            'code' => 'WAC-100',
            'category' => 'Cooling',
            'price' => 12999,
            'stock_quantity' => 10,
            'reorder_level' => 4,
            'location' => 'Storage A',
        ]);

        $response->assertCreated()->assertJsonPath('data.code', 'WAC-100');
        $this->assertDatabaseHas('products', ['code' => 'WAC-100']);
    }

    public function test_a_sale_reduces_stock_and_creates_an_activity_log(): void
    {
        $this->seed();
        $product = \App\Models\Product::where('code', 'PAC-150')->firstOrFail();
        $startingStock = $product->stock_quantity;

        $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => 2,
            'department' => 'Sales',
            'source_location' => 'Sales Floor',
        ])->assertCreated();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => $startingStock - 2]);
        $this->assertDatabaseHas('activity_logs', ['record_type' => 'Transaction']);
    }
}
