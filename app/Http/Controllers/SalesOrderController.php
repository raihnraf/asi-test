<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\SalesOrderCsvExporter;
use App\Services\SalesOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderController extends Controller
{
    private const PAGINATION_PER_PAGE = 10;

    public function __construct(
        private readonly SalesOrderService $salesOrderService,
        private readonly SalesOrderCsvExporter $salesOrderCsvExporter,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $salesOrders = SalesOrder::query()
            ->withProduct()
            ->search($this->searchTerm($request))
            ->latestFirst()
            ->paginate(self::PAGINATION_PER_PAGE)
            ->withQueryString();

        return view('sales-orders.index', [
            'salesOrders' => $salesOrders,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', SalesOrder::class);

        return $this->salesOrderCsvExporter->download($this->searchTerm($request));
    }

    public function create(): View
    {
        $this->authorize('create', SalesOrder::class);

        return view('sales-orders.create', [
            'products' => Product::query()->alphabetical()->get(),
        ]);
    }

    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        try {
            $this->salesOrderService->create($request->validated());
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order created successfully.');
    }

    public function edit(SalesOrder $salesOrder): View
    {
        $this->authorize('update', $salesOrder);

        return view('sales-orders.edit', [
            'salesOrder' => $salesOrder,
            'products' => Product::query()->alphabetical()->get(),
        ]);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        try {
            $this->salesOrderService->update($salesOrder, $request->validated());
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('delete', $salesOrder);

        $this->salesOrderService->delete($salesOrder);

        return redirect()
            ->route('sales-orders.index')
            ->with('status', 'Sales order deleted successfully.');
    }

    private function searchTerm(Request $request): string
    {
        return trim((string) $request->query('search', ''));
    }
}
