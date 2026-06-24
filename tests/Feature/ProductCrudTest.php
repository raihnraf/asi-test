<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_from_product_routes(): void
    {
        $product = Product::factory()->create();

        $this->get('/products')->assertRedirect('/login');
        $this->get('/products/create')->assertRedirect('/login');
        $this->post('/products', [])->assertRedirect('/login');
        $this->get("/products/{$product->id}/edit")->assertRedirect('/login');
        $this->patch("/products/{$product->id}", [])->assertRedirect('/login');
        $this->delete("/products/{$product->id}")->assertRedirect('/login');
    }

    public function test_user_can_view_product_list(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $number) {
            Product::factory()->create([
                'name' => sprintf('Product %02d', $number),
                'sku' => sprintf('SKU-%03d', $number),
                'price' => $number === 1 ? 25000 : 10 + $number,
                'stock' => $number,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get('/products');

        $response->assertOk();
        $response->assertSee('Products');
        $response->assertSee('Product 01');
        $response->assertSee('SKU-001');
        $response->assertSee('25.000');
        $response->assertSee('10');
        $response->assertSee('Product 10');
        $response->assertDontSee('Product 11');
        $response->assertDontSee('Product 12');
    }

    public function test_user_can_search_product_list_by_name_or_sku(): void
    {
        $user = User::factory()->create();

        Product::factory()->create([
            'name' => 'Gaming Keyboard',
            'sku' => 'KEY-10001',
        ]);
        Product::factory()->create([
            'name' => 'Office Mouse',
            'sku' => 'MSE-20002',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/products?search=KEY-10001');

        $response->assertOk();
        $response->assertSee('Gaming Keyboard');
        $response->assertSee('KEY-10001');
        $response->assertDontSee('Office Mouse');
        $response->assertDontSee('MSE-20002');
    }

    public function test_user_can_create_product(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this
            ->actingAs($user)
            ->post('/products', [
                'name' => 'Gaming Keyboard',
                'sku' => 'KEY-10001',
                'price' => '199.999,50',
                'stock' => 8,
            ]);

        $response->assertRedirect(route('products.index', absolute: false));
        $response->assertSessionHas('status', 'Product created successfully.');

        $this->assertDatabaseHas('products', [
            'name' => 'Gaming Keyboard',
            'sku' => 'KEY-10001',
            'price' => '199999.50',
            'stock' => 8,
        ]);
    }

    public function test_product_validation_errors_are_reported(): void
    {
        $user = User::factory()->admin()->create();
        Product::factory()->create(['sku' => 'SKU-EXISTS']);

        $response = $this
            ->actingAs($user)
            ->from('/products/create')
            ->post('/products', [
                'name' => '',
                'sku' => 'SKU-EXISTS',
                'price' => '-1',
                'stock' => '-2',
            ]);

        $response->assertRedirect('/products/create');
        $response->assertSessionHasErrors(['name', 'sku', 'price', 'stock']);
    }

    public function test_user_can_edit_product(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'name' => 'Old Product',
            'sku' => 'OLD-001',
            'price' => 15.25,
            'stock' => 3,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch("/products/{$product->id}", [
                'name' => 'Updated Product',
                'sku' => 'NEW-001',
                'price' => '99.500,25',
                'stock' => 12,
            ]);

        $response->assertRedirect(route('products.index', absolute: false));
        $response->assertSessionHas('status', 'Product updated successfully.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'sku' => 'NEW-001',
            'price' => '99500.25',
            'stock' => 12,
        ]);
    }

    public function test_product_sku_can_remain_unchanged_when_updating(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'sku' => 'KEEP-001',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch("/products/{$product->id}", [
                'name' => 'Still Same SKU',
                'sku' => 'KEEP-001',
                'price' => '45.00',
                'stock' => 2,
            ]);

        $response->assertRedirect(route('products.index', absolute: false));
        $response->assertSessionHasNoErrors();
    }

    public function test_user_can_delete_product(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect(route('products.index', absolute: false));
        $response->assertSessionHas('status', 'Product deleted successfully.');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_product_with_sales_orders_cannot_be_deleted(): void
    {
        $user = User::factory()->admin()->create();
        $product = Product::factory()->create();
        SalesOrder::factory()->create([
            'product_id' => $product->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete("/products/{$product->id}");

        $response->assertRedirect(route('products.index', absolute: false));
        $response->assertSessionHas('error', 'Product cannot be deleted because it is already used in sales orders.');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_staff_can_view_products_but_cannot_manage_them(): void
    {
        $staff = User::factory()->staff()->create();
        $product = Product::factory()->create();

        $indexResponse = $this
            ->actingAs($staff)
            ->get('/products');

        $indexResponse->assertOk();
        $indexResponse->assertSee('Products');
        $indexResponse->assertDontSee('Create Product');
        $indexResponse->assertDontSee('Edit Product');
        $indexResponse->assertDontSee('Delete Product');
        $indexResponse->assertSee('View only');

        $this->actingAs($staff)->get('/products/create')->assertForbidden();
        $this->actingAs($staff)->post('/products', [
            'name' => 'Blocked Product',
            'sku' => 'BLK-10001',
            'price' => '99.99',
            'stock' => 2,
        ])->assertForbidden();
        $this->actingAs($staff)->get("/products/{$product->id}/edit")->assertForbidden();
        $this->actingAs($staff)->patch("/products/{$product->id}", [
            'name' => 'Blocked Update',
            'sku' => $product->sku,
            'price' => '99.99',
            'stock' => 2,
        ])->assertForbidden();
        $this->actingAs($staff)->delete("/products/{$product->id}")->assertForbidden();
    }
}
