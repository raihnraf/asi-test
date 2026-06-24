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
                    $salesOrder->product_name_snapshot,
                    $salesOrder->product_sku_snapshot,
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
            ->latest('order_date')
            ->latest('id')
            ->cursor();
    }
}
