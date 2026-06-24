<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class SalesOrderConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_competing_orders_do_not_oversell_stock(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('This concurrency test requires MySQL row locking.');
        }

        $product = Product::factory()->create([
            'price' => '10.00',
            'stock' => 1,
        ]);

        $firstResultFile = tempnam(sys_get_temp_dir(), 'sales-order-');
        $secondResultFile = tempnam(sys_get_temp_dir(), 'sales-order-');
        $firstProcess = null;
        $secondProcess = null;

        DB::beginTransaction();
        Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

        try {
            $firstProcess = $this->startOrderCreationProcess($product->id, $firstResultFile);
            $secondProcess = $this->startOrderCreationProcess($product->id, $secondResultFile);

            $this->waitForState($firstResultFile, 'started');
            $this->waitForState($secondResultFile, 'started');
            usleep(200000);

            $this->assertDatabaseCount('sales_orders', 0);

            DB::commit();

            $firstProcess->wait();
            $secondProcess->wait();

            $this->assertSame(0, $firstProcess->getExitCode(), $firstProcess->getErrorOutput());
            $this->assertSame(0, $secondProcess->getExitCode(), $secondProcess->getErrorOutput());

            $results = [
                trim((string) file_get_contents($firstResultFile)),
                trim((string) file_get_contents($secondResultFile)),
            ];

            sort($results);

            $this->assertSame(['insufficient_stock', 'success'], $results);
            $this->assertDatabaseCount('sales_orders', 1);
            $this->assertSame(0, $product->fresh()->stock);
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if ($firstProcess instanceof Process && $firstProcess->isRunning()) {
                $firstProcess->stop(1);
            }

            if ($secondProcess instanceof Process && $secondProcess->isRunning()) {
                $secondProcess->stop(1);
            }

            @unlink($firstResultFile ?: '');
            @unlink($secondResultFile ?: '');
        }
    }

    private function startOrderCreationProcess(int $productId, string $resultFile): Process
    {
        $script = <<<'PHP'
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

file_put_contents($argv[2], 'started');

try {
    app(App\Services\SalesOrderService::class)->create([
        'product_id' => (int) $argv[1],
        'quantity' => 1,
        'order_date' => '2026-06-24',
    ]);

    file_put_contents($argv[2], 'success');
    exit(0);
} catch (App\Exceptions\InsufficientStockException) {
    file_put_contents($argv[2], 'insufficient_stock');
    exit(0);
} catch (Throwable $exception) {
    file_put_contents($argv[2], 'error:' . $exception::class);
    fwrite(STDERR, $exception->getMessage());
    exit(1);
}
PHP;

        $process = new Process([PHP_BINARY, '-r', $script, (string) $productId, $resultFile], base_path());
        $process->setTimeout(15);
        $process->start();

        return $process;
    }

    private function waitForState(string $resultFile, string $expectedState): void
    {
        $deadline = microtime(true) + 5;

        while (microtime(true) < $deadline) {
            $state = trim((string) file_get_contents($resultFile));

            if ($state === $expectedState) {
                return;
            }

            usleep(10000);
        }

        $this->fail(sprintf('Timed out waiting for state [%s] in [%s].', $expectedState, $resultFile));
    }
}
