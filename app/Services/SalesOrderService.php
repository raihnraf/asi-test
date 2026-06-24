<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

final class SalesOrderService
{
    /**
     * @param  array{product_id:int|string, quantity:int|string, order_date:string}  $attributes
     */
    public function create(array $attributes): SalesOrder
    {
        return DB::transaction(function () use ($attributes): SalesOrder {
            $product = $this->lockProduct((int) $attributes['product_id']);
            $quantity = (int) $attributes['quantity'];
            $unitPrice = $product->price;

            $this->ensureSufficientStock($product, $quantity);
            $product->decrement('stock', $quantity);

            return SalesOrder::query()->create(
                $this->salesOrderPayload(
                    product: $product,
                    existingSalesOrder: null,
                    quantity: $quantity,
                    unitPrice: $unitPrice,
                    orderDate: $attributes['order_date'],
                ),
            );
        });
    }

    /**
     * @param  array{product_id:int|string, quantity:int|string, order_date:string}  $attributes
     */
    public function update(SalesOrder $salesOrder, array $attributes): SalesOrder
    {
        return DB::transaction(function () use ($salesOrder, $attributes): SalesOrder {
            $lockedSalesOrder = $this->lockSalesOrder($salesOrder->id);
            $currentProduct = $this->lockProduct($lockedSalesOrder->product_id);
            $requestedProductId = (int) $attributes['product_id'];
            $requestedQuantity = (int) $attributes['quantity'];

            // Release the original reservation before validating the replacement quantity.
            $currentProduct->increment('stock', $lockedSalesOrder->quantity);

            $selectedProduct = $requestedProductId === $lockedSalesOrder->product_id
                ? $currentProduct
                : $this->lockProduct($requestedProductId);

            $this->ensureSufficientStock($selectedProduct, $requestedQuantity);

            $unitPrice = $requestedProductId === $lockedSalesOrder->product_id
                ? $lockedSalesOrder->unit_price
                : $selectedProduct->price;

            $selectedProduct->decrement('stock', $requestedQuantity);

            $lockedSalesOrder->update(
                $this->salesOrderPayload(
                    product: $selectedProduct,
                    existingSalesOrder: $requestedProductId === $lockedSalesOrder->product_id ? $lockedSalesOrder : null,
                    quantity: $requestedQuantity,
                    unitPrice: $unitPrice,
                    orderDate: $attributes['order_date'],
                ),
            );

            return $lockedSalesOrder->refresh();
        });
    }

    public function delete(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder): void {
            $lockedSalesOrder = $this->lockSalesOrder($salesOrder->id);
            $product = $this->lockProduct($lockedSalesOrder->product_id);

            $product->increment('stock', $lockedSalesOrder->quantity);
            $lockedSalesOrder->delete();
        });
    }

    private function lockProduct(int $productId): Product
    {
        return Product::query()
            ->lockForUpdate()
            ->findOrFail($productId);
    }

    private function lockSalesOrder(int $salesOrderId): SalesOrder
    {
        return SalesOrder::query()
            ->lockForUpdate()
            ->findOrFail($salesOrderId);
    }

    private function ensureSufficientStock(Product $product, int $requestedQuantity): void
    {
        if ($product->stock < $requestedQuantity) {
            throw InsufficientStockException::forRequestedQuantity();
        }
    }

    /**
     * @return array{product_id:int, product_name_snapshot:string, product_sku_snapshot:string, quantity:int, unit_price:string, total_price:string, order_date:string}
     */
    private function salesOrderPayload(Product $product, ?SalesOrder $existingSalesOrder, int $quantity, string $unitPrice, string $orderDate): array
    {
        return [
            'product_id' => $product->id,
            'product_name_snapshot' => $existingSalesOrder?->product_name_snapshot ?? $product->name,
            'product_sku_snapshot' => $existingSalesOrder?->product_sku_snapshot ?? $product->sku,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $this->calculateTotalPrice($unitPrice, $quantity),
            'order_date' => $orderDate,
        ];
    }

    private function calculateTotalPrice(string $unitPrice, int $quantity): string
    {
        return $this->centsToDecimal($this->decimalToCents($unitPrice) * $quantity);
    }

    private function decimalToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function centsToDecimal(int $amount): string
    {
        return sprintf('%d.%02d', intdiv($amount, 100), $amount % 100);
    }
}
