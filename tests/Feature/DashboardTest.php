<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_analytics_summary_cards(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        SalesOrder::factory()->create([
            'product_id' => $product->id,
            'total_price' => 250.50,
        ]);
        SalesOrder::factory()->create([
            'product_id' => $product->id,
            'total_price' => 349.50,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Total Revenue');
        $response->assertSee('600');
        $response->assertSee('Total Products');
        $response->assertSee('1');
        $response->assertSee('Total Transactions');
        $response->assertSee('2');
    }
}
