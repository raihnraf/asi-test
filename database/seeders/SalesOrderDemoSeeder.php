<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\SalesOrderService;
use Illuminate\Database\Seeder;

class SalesOrderDemoSeeder extends Seeder
{
    /**
     * @var list<array{sku:string, quantity:int, order_date:string}>
     */
    private const SALES_ORDERS = [
        ['sku' => 'KEY-10001', 'quantity' => 3, 'order_date' => '2026-06-01'],
        ['sku' => 'MSE-10002', 'quantity' => 5, 'order_date' => '2026-06-02'],
        ['sku' => 'MON-10003', 'quantity' => 2, 'order_date' => '2026-06-03'],
        ['sku' => 'DOC-10004', 'quantity' => 2, 'order_date' => '2026-06-03'],
        ['sku' => 'LST-10005', 'quantity' => 4, 'order_date' => '2026-06-04'],
        ['sku' => 'HDP-10006', 'quantity' => 2, 'order_date' => '2026-06-05'],
        ['sku' => 'WCM-10007', 'quantity' => 3, 'order_date' => '2026-06-06'],
        ['sku' => 'SSD-10008', 'quantity' => 2, 'order_date' => '2026-06-07'],
        ['sku' => 'CHR-10009', 'quantity' => 1, 'order_date' => '2026-06-08'],
        ['sku' => 'DSK-10010', 'quantity' => 1, 'order_date' => '2026-06-09'],
        ['sku' => 'PRT-10011', 'quantity' => 2, 'order_date' => '2026-06-10'],
        ['sku' => 'PPR-10012', 'quantity' => 6, 'order_date' => '2026-06-11'],
        ['sku' => 'RTR-10013', 'quantity' => 2, 'order_date' => '2026-06-12'],
        ['sku' => 'PRJ-10014', 'quantity' => 1, 'order_date' => '2026-06-13'],
        ['sku' => 'SPK-10015', 'quantity' => 2, 'order_date' => '2026-06-14'],
    ];

    public function run(): void
    {
        $salesOrderService = app(SalesOrderService::class);

        foreach (self::SALES_ORDERS as $attributes) {
            $product = Product::query()->where('sku', $attributes['sku'])->firstOrFail();

            $alreadySeeded = SalesOrder::query()
                ->where('product_id', $product->id)
                ->where('quantity', $attributes['quantity'])
                ->whereDate('order_date', $attributes['order_date'])
                ->exists();

            if ($alreadySeeded) {
                continue;
            }

            $salesOrderService->create([
                'product_id' => $product->id,
                'quantity' => $attributes['quantity'],
                'order_date' => $attributes['order_date'],
            ]);
        }
    }
}
