<?php

namespace App\Rules;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\SalesOrder;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class HasAvailableStock implements ValidationRule
{
    public function __construct(
        private readonly ?int $productId,
        private readonly ?SalesOrder $salesOrder = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->productId === null) {
            return;
        }

        $product = Product::query()->find($this->productId);

        if (! $product) {
            return;
        }

        $availableStock = $product->stock;

        if ($this->salesOrder !== null && $this->salesOrder->product_id === $product->id) {
            $availableStock += $this->salesOrder->quantity;
        }

        if ((int) $value > $availableStock) {
            $fail(InsufficientStockException::MESSAGE);
        }
    }
}
