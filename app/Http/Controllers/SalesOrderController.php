<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $salesOrders = $this->filteredSalesOrdersQuery($search)
            ->latest('order_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('sales-orders.index', [
            'salesOrders' => $salesOrders,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('search', ''));
        $salesOrders = $this->filteredSalesOrdersQuery($search)
            ->latest('order_date')
            ->latest('id')
            ->get();

        return response()->streamDownload(function () use ($salesOrders): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Order Date', 'Product', 'SKU', 'Quantity', 'Unit Price', 'Total Price']);

            foreach ($salesOrders as $salesOrder) {
                fputcsv($handle, [
                    $salesOrder->order_date->format('Y-m-d'),
                    $salesOrder->product->name,
                    $salesOrder->product->sku,
                    $salesOrder->quantity,
                    number_format((float) $salesOrder->unit_price, 2, '.', ''),
                    number_format((float) $salesOrder->total_price, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 'sales-orders.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create-sales-orders');

        return view('sales-orders.create', [
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($validated['product_id']);

            if ($product->stock < (int) $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'The quantity may not exceed available stock.',
                ]);
            }

            $unitPrice = (float) $product->price;

            $product->decrement('stock', (int) $validated['quantity']);

            SalesOrder::create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * (int) $validated['quantity'], 2),
                'order_date' => $validated['order_date'],
            ]);
        });

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order created successfully.');
    }

    public function edit(SalesOrder $salesOrder): View
    {
        Gate::authorize('manage-sales-orders');

        return view('sales-orders.edit', [
            'salesOrder' => $salesOrder,
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $salesOrder): void {
            $salesOrder = SalesOrder::query()
                ->lockForUpdate()
                ->findOrFail($salesOrder->id);

            $originalProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($salesOrder->product_id);

            $originalProduct->increment('stock', $salesOrder->quantity);

            $product = $originalProduct;

            if ((int) $validated['product_id'] !== $salesOrder->product_id) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['product_id']);
            }

            if ($product->stock < (int) $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'The quantity may not exceed available stock.',
                ]);
            }

            $unitPrice = (int) $validated['product_id'] === $salesOrder->product_id
                ? (float) $salesOrder->unit_price
                : (float) $product->price;

            $product->decrement('stock', (int) $validated['quantity']);

            $salesOrder->update([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * (int) $validated['quantity'], 2),
                'order_date' => $validated['order_date'],
            ]);
        });

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        Gate::authorize('manage-sales-orders');

        DB::transaction(function () use ($salesOrder): void {
            $salesOrder = SalesOrder::query()
                ->lockForUpdate()
                ->findOrFail($salesOrder->id);

            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($salesOrder->product_id);

            $product->increment('stock', $salesOrder->quantity);
            $salesOrder->delete();
        });

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order deleted successfully.');
    }

    protected function filteredSalesOrdersQuery(string $search): Builder
    {
        return SalesOrder::query()
            ->with('product')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('order_date', 'like', "%{$search}%")
                        ->orWhereHas('product', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            });
    }
}
