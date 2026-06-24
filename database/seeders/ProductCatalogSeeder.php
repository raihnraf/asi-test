<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * @var list<array{name:string, sku:string, price:string, stock:int}>
     */
    private const PRODUCTS = [
        ['name' => 'Gaming Keyboard Mechanical', 'sku' => 'KEY-10001', 'price' => '425000.00', 'stock' => 30],
        ['name' => 'Wireless Office Mouse', 'sku' => 'MSE-10002', 'price' => '185000.00', 'stock' => 45],
        ['name' => '27 Inch IPS Monitor', 'sku' => 'MON-10003', 'price' => '2899000.00', 'stock' => 18],
        ['name' => 'USB-C Docking Station', 'sku' => 'DOC-10004', 'price' => '975000.00', 'stock' => 20],
        ['name' => 'Laptop Stand Aluminum', 'sku' => 'LST-10005', 'price' => '215000.00', 'stock' => 40],
        ['name' => 'Noise Cancelling Headset', 'sku' => 'HDP-10006', 'price' => '1350000.00', 'stock' => 16],
        ['name' => '1080p USB Webcam', 'sku' => 'WCM-10007', 'price' => '525000.00', 'stock' => 28],
        ['name' => 'Portable SSD 1TB', 'sku' => 'SSD-10008', 'price' => '1499000.00', 'stock' => 22],
        ['name' => 'Ergonomic Office Chair', 'sku' => 'CHR-10009', 'price' => '2450000.00', 'stock' => 12],
        ['name' => 'Standing Desk 120cm', 'sku' => 'DSK-10010', 'price' => '3199000.00', 'stock' => 10],
        ['name' => 'Laser Printer Mono', 'sku' => 'PRT-10011', 'price' => '2199000.00', 'stock' => 14],
        ['name' => 'A4 Paper 80gsm Box', 'sku' => 'PPR-10012', 'price' => '315000.00', 'stock' => 55],
        ['name' => 'Router Dual Band AX1800', 'sku' => 'RTR-10013', 'price' => '899000.00', 'stock' => 19],
        ['name' => 'Projector Full HD', 'sku' => 'PRJ-10014', 'price' => '4599000.00', 'stock' => 8],
        ['name' => 'Conference Speakerphone', 'sku' => 'SPK-10015', 'price' => '1650000.00', 'stock' => 15],
        ['name' => 'Barcode Scanner USB', 'sku' => 'BCR-10016', 'price' => '675000.00', 'stock' => 24],
        ['name' => 'Receipt Printer Thermal', 'sku' => 'RCP-10017', 'price' => '1299000.00', 'stock' => 18],
        ['name' => 'External Hard Drive 2TB', 'sku' => 'HDD-10018', 'price' => '1049000.00', 'stock' => 21],
        ['name' => 'Wireless Presenter Clicker', 'sku' => 'PRS-10019', 'price' => '245000.00', 'stock' => 34],
        ['name' => 'Mini PC Core i5', 'sku' => 'MPC-10020', 'price' => '5899000.00', 'stock' => 9],
    ];

    public function run(): void
    {
        foreach (self::PRODUCTS as $attributes) {
            $product = Product::query()->firstOrNew(['sku' => $attributes['sku']]);

            $product->name = $attributes['name'];
            $product->price = $attributes['price'];

            if (! $product->exists) {
                $product->stock = $attributes['stock'];
            }

            $product->save();
        }
    }
}
