<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_snapshots_price_and_calculates_total_without_float_drift(): void
    {
        $product = Product::factory()->create([
            'price' => '0.29',
            'stock' => 5,
        ]);

        $order = app(SalesOrderService::class)->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'order_date' => '2026-06-24',
        ]);

        $this->assertSame('0.29', $order->unit_price);
        $this->assertSame('0.87', $order->total_price);
        $this->assertSame($product->name, $order->product_name_snapshot);
        $this->assertSame($product->sku, $order->product_sku_snapshot);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_created_order_keeps_original_price_snapshot_after_product_price_changes(): void
    {
        $product = Product::factory()->create([
            'price' => '125.75',
            'stock' => 10,
        ]);

        $order = app(SalesOrderService::class)->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'order_date' => '2026-06-24',
        ]);

        $product->update(['price' => '999.99']);

        $this->assertSame('125.75', $order->fresh()->unit_price);
        $this->assertSame('251.50', $order->fresh()->total_price);
    }

    public function test_update_for_same_product_keeps_original_unit_price_snapshot(): void
    {
        $product = Product::factory()->create([
            'price' => '125.75',
            'stock' => 10,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => $product->name,
            'product_sku_snapshot' => $product->sku,
            'quantity' => 2,
            'unit_price' => '125.75',
            'total_price' => '251.50',
            'order_date' => '2026-06-24',
        ]);

        $product->decrement('stock', 2);
        $product->update(['price' => '199.99']);

        $updatedOrder = app(SalesOrderService::class)->update($salesOrder, [
            'product_id' => $product->id,
            'quantity' => 4,
            'order_date' => '2026-06-25',
        ]);

        $this->assertSame('125.75', $updatedOrder->unit_price);
        $this->assertSame('503.00', $updatedOrder->total_price);
        $this->assertSame($product->name, $updatedOrder->product_name_snapshot);
        $this->assertSame($product->sku, $updatedOrder->product_sku_snapshot);
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_update_for_same_product_keeps_original_name_and_sku_snapshots(): void
    {
        $product = Product::factory()->create([
            'name' => 'Monitor 4K',
            'sku' => 'MON-4000',
            'price' => '125.75',
            'stock' => 10,
        ]);

        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $product->id,
            'product_name_snapshot' => 'Monitor 4K',
            'product_sku_snapshot' => 'MON-4000',
            'quantity' => 2,
            'unit_price' => '125.75',
            'total_price' => '251.50',
            'order_date' => '2026-06-24',
        ]);

        $product->decrement('stock', 2);
        $product->update([
            'name' => 'Renamed Monitor',
            'sku' => 'REN-9999',
        ]);

        $updatedOrder = app(SalesOrderService::class)->update($salesOrder, [
            'product_id' => $product->id,
            'quantity' => 4,
            'order_date' => '2026-06-25',
        ]);

        $this->assertSame('Monitor 4K', $updatedOrder->product_name_snapshot);
        $this->assertSame('MON-4000', $updatedOrder->product_sku_snapshot);
    }
}
