<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('product_name_snapshot')->nullable()->after('product_id');
            $table->string('product_sku_snapshot')->nullable()->after('product_name_snapshot');
        });

        DB::table('sales_orders')
            ->join('products', 'products.id', '=', 'sales_orders.product_id')
            ->select('sales_orders.id', 'products.name', 'products.sku')
            ->orderBy('sales_orders.id')
            ->each(function (object $salesOrder): void {
                DB::table('sales_orders')
                    ->where('id', $salesOrder->id)
                    ->update([
                        'product_name_snapshot' => $salesOrder->name,
                        'product_sku_snapshot' => $salesOrder->sku,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['product_name_snapshot', 'product_sku_snapshot']);
        });
    }
};
