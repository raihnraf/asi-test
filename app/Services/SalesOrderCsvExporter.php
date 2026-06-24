<?php

namespace App\Services;

use App\Models\SalesOrder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SalesOrderCsvExporter
{
    private const FILE_NAME = 'sales-orders.csv';

    private const HEADERS = ['Order Date', 'Product', 'SKU', 'Quantity', 'Unit Price', 'Total Price'];

    private const DOWNLOAD_HEADERS = [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ];

    public function download(string $search): StreamedResponse
    {
        return response()->streamDownload(function () use ($search): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, self::HEADERS);

            foreach ($this->rows($search) as $salesOrder) {
                fputcsv($handle, [
                    substr((string) $salesOrder->order_date, 0, 10),
                    $salesOrder->product_name,
                    $salesOrder->product_sku,
                    $salesOrder->quantity,
                    $salesOrder->unit_price,
                    $salesOrder->total_price,
                ]);
            }

            fclose($handle);
        }, self::FILE_NAME, self::DOWNLOAD_HEADERS);
    }

    private function rows(string $search): \Generator
    {
        yield from SalesOrder::query()
            ->search($search)
            ->join('products', 'products.id', '=', 'sales_orders.product_id')
            ->latest('sales_orders.order_date')
            ->latest('sales_orders.id')
            ->select([
                'sales_orders.id',
                'sales_orders.order_date',
                'sales_orders.quantity',
                'sales_orders.unit_price',
                'sales_orders.total_price',
                'products.name as product_name',
                'products.sku as product_sku',
            ])
            ->cursor();
    }
}
