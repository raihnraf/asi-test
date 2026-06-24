<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'product_id' => fn () => Product::factory()->create()->id,
            'product_name_snapshot' => fn (array $attributes) => Product::query()->findOrFail($attributes['product_id'])->name,
            'product_sku_snapshot' => fn (array $attributes) => Product::query()->findOrFail($attributes['product_id'])->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'order_date' => fake()->date(),
        ];
    }
}
