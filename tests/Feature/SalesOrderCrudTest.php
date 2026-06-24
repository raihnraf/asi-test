<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_sales_order_routes(): void
    {
        $salesOrder = SalesOrder::factory()->create();

        $this->get('/sales-orders')->assertRedirect('/login');
        $this->get('/sales-orders/create')->assertRedirect('/login');
        $this->post('/sales-orders', [])->assertRedirect('/login');
        $this->get("/sales-orders/{$salesOrder->id}/edit")->assertRedirect('/login');
        $this->patch("/sales-orders/{$salesOrder->id}", [])->assertRedirect('/login');
        $this->delete("/sales-orders/{$salesOrder->id}")->assertRedirect('/login');
    }

    public function test_user_can_view_sales_order_list_with_related_product_data(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Monitor 4K',
            'sku' => 'MON-4000',
            'price' => 350.5,
        ]);

        SalesOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 350.5,
            'total_price' => 701.0,
            'order_date' => '2026-06-24',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/sales-orders');

        $response->assertOk();
        $response->assertSee('Sales Orders');
        $response->assertSee('2026-06-24');
        $response->assertSee('Monitor 4K');
        $response->assertSee('MON-4000');
        $response->assertSee('350.50');
        $response->assertSee('701.00');
    }

    public function test_user_can_search_sales_order_list(): void
    {
        $user = User::factory()->create();
        $matchingProduct = Product::factory()->create([
            'name' => 'Monitor 4K',
            'sku' => 'MON-4000',
        ]);
        $otherProduct = Product::factory()->create([
            'name' => 'Office Chair',
            'sku' => 'CHR-1000',
        ]);

        SalesOrder::factory()->create([
            'product_id' => $matchingProduct->id,
            'order_date' => '2026-06-24',
        ]);
        SalesOrder::factory()->create([
            'product_id' => $otherProduct->id,
            'order_date' => '2026-06-10',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/sales-orders?search=MON-4000');

        $response->assertOk();
        $response->assertSee('Monitor 4K');
        $response->assertSee('MON-4000');
        $response->assertDontSee('Office Chair');
        $response->assertDontSee('CHR-1000');
    }

    public function test_user_can_export_sales_orders_to_csv(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Monitor 4K',
            'sku' => 'MON-4000',
        ]);

        SalesOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 350.50,
            'total_price' => 701.00,
            'order_date' => '2026-06-24',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/sales-orders/export');

        $response->assertOk();
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment; filename=sales-orders.csv', (string) $response->headers->get('content-disposition'));

        $content = $response->streamedContent();

        $this->assertStringContainsString('"Order Date",Product,SKU,Quantity,"Unit Price","Total Price"', $content);
        $this->assertStringContainsString('2026-06-24,"Monitor 4K",MON-4000,2,350.50,701.00', $content);
    }

    public function test_sales_order_csv_export_respects_search_filter(): void
    {
        $user = User::factory()->create();
        $matchingProduct = Product::factory()->create([
            'name' => 'Monitor 4K',
            'sku' => 'MON-4000',
        ]);
        $otherProduct = Product::factory()->create([
            'name' => 'Office Chair',
            'sku' => 'CHR-1000',
        ]);

        SalesOrder::factory()->create([
            'product_id' => $matchingProduct->id,
            'order_date' => '2026-06-24',
        ]);
        SalesOrder::factory()->create([
            'product_id' => $otherProduct->id,
            'order_date' => '2026-06-10',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/sales-orders/export?search=MON-4000');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Monitor 4K', $content);
        $this->assertStringContainsString('MON-4000', $content);
        $this->assertStringNotContainsString('Office Chair', $content);
        $this->assertStringNotContainsString('CHR-1000', $content);
    }

    public function test_user_can_create_sales_order_with_auto_calculated_total(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 125.75,
            'stock' => 10,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/sales-orders', [
                'product_id' => $product->id,
                'quantity' => 3,
                'order_date' => '2026-06-24',
            ]);

        $response->assertRedirect(route('sales-orders.index', absolute: false));
        $response->assertSessionHas('status', 'Sales order created successfully.');

        $this->assertDatabaseHas('sales_orders', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => '125.75',
            'total_price' => '377.25',
            'order_date' => '2026-06-24',
        ]);

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_sales_order_validation_errors_are_reported(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/sales-orders/create')
            ->post('/sales-orders', [
                'product_id' => 999999,
                'quantity' => 0,
                'order_date' => 'not-a-date',
            ]);

        $response->assertRedirect('/sales-orders/create');
        $response->assertSessionHasErrors(['product_id', 'quantity', 'order_date']);
    }

    public function test_sales_order_quantity_cannot_exceed_available_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2]);

        $response = $this
            ->actingAs($user)
            ->from('/sales-orders/create')
            ->post('/sales-orders', [
                'product_id' => $product->id,
                'quantity' => 3,
                'order_date' => '2026-06-24',
            ]);

        $response->assertRedirect('/sales-orders/create');
        $response->assertSessionHasErrors(['quantity']);
        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_user_can_edit_sales_order_and_recalculate_total(): void
    {
        $user = User::factory()->create();
        $oldProduct = Product::factory()->create(['price' => 80, 'stock' => 10]);
        $newProduct = Product::factory()->create(['price' => 150, 'stock' => 6]);
        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $oldProduct->id,
            'quantity' => 2,
            'unit_price' => 80,
            'total_price' => 160,
        ]);

        $oldProduct->decrement('stock', 2);

        $response = $this
            ->actingAs($user)
            ->patch("/sales-orders/{$salesOrder->id}", [
                'product_id' => $newProduct->id,
                'quantity' => 4,
                'order_date' => '2026-06-25',
            ]);

        $response->assertRedirect(route('sales-orders.index', absolute: false));
        $response->assertSessionHas('status', 'Sales order updated successfully.');

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'product_id' => $newProduct->id,
            'quantity' => 4,
            'unit_price' => '150.00',
            'total_price' => '600.00',
            'order_date' => '2026-06-25',
        ]);

        $this->assertSame(10, $oldProduct->fresh()->stock);
        $this->assertSame(2, $newProduct->fresh()->stock);
    }

    public function test_updating_sales_order_for_same_product_keeps_original_unit_price_snapshot(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 125.75,
            'stock' => 10,
        ]);
        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 125.75,
            'total_price' => 251.50,
            'order_date' => '2026-06-24',
        ]);

        $product->decrement('stock', 2);
        $product->update(['price' => 199.99]);

        $response = $this
            ->actingAs($user)
            ->patch("/sales-orders/{$salesOrder->id}", [
                'product_id' => $product->id,
                'quantity' => 4,
                'order_date' => '2026-06-25',
            ]);

        $response->assertRedirect(route('sales-orders.index', absolute: false));
        $response->assertSessionHas('status', 'Sales order updated successfully.');

        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'unit_price' => '125.75',
            'total_price' => '503.00',
            'order_date' => '2026-06-25',
        ]);

        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_user_can_delete_sales_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $product->decrement('stock', 3);

        $response = $this
            ->actingAs($user)
            ->delete("/sales-orders/{$salesOrder->id}");

        $response->assertRedirect(route('sales-orders.index', absolute: false));
        $response->assertSessionHas('status', 'Sales order deleted successfully.');
        $this->assertDatabaseMissing('sales_orders', ['id' => $salesOrder->id]);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_staff_can_create_sales_orders_but_cannot_edit_or_delete_them(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create([
            'stock' => 10,
            'price' => 125.75,
        ]);
        $salesOrder = SalesOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 125.75,
            'total_price' => 251.50,
        ]);

        $product->decrement('stock', 2);

        $indexResponse = $this
            ->actingAs($staff)
            ->get('/sales-orders');

        $indexResponse->assertOk();
        $indexResponse->assertSee('Create Sales Order');
        $indexResponse->assertDontSee('Edit Sales Order');
        $indexResponse->assertDontSee('Delete Sales Order');
        $indexResponse->assertSee('View only');

        $this->actingAs($staff)->get('/sales-orders/create')->assertOk();

        $createResponse = $this
            ->actingAs($staff)
            ->post('/sales-orders', [
                'product_id' => $product->id,
                'quantity' => 3,
                'order_date' => '2026-06-26',
            ]);

        $createResponse->assertRedirect(route('sales-orders.index', absolute: false));
        $this->assertDatabaseHas('sales_orders', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => '125.75',
            'total_price' => '377.25',
            'order_date' => '2026-06-26',
        ]);

        $this->actingAs($staff)->get("/sales-orders/{$salesOrder->id}/edit")->assertForbidden();
        $this->actingAs($staff)->patch("/sales-orders/{$salesOrder->id}", [
            'product_id' => $product->id,
            'quantity' => 1,
            'order_date' => '2026-06-27',
        ])->assertForbidden();
        $this->actingAs($staff)->delete("/sales-orders/{$salesOrder->id}")->assertForbidden();
    }
}
