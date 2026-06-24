<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales Orders') }}
            </h2>

            <div class="flex flex-wrap items-center justify-end gap-3">
                @can('export', \App\Models\SalesOrder::class)
                    <a
                        href="{{ route('sales-orders.export', request()->only('search')) }}"
                        class="inline-flex items-center rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-md shadow-emerald-200/50 hover:from-emerald-500 hover:to-teal-500 hover:shadow-lg hover:shadow-emerald-200/60 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition duration-150 ease-in-out cursor-pointer"
                    >
                        {{ __('Export CSV') }}
                    </a>
                @endcan

                @can('create', \App\Models\SalesOrder::class)
                    <a
                        href="{{ route('sales-orders.create') }}"
                        class="inline-flex items-center rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-md shadow-indigo-200/50 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-200/60 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                    >
                        {{ __('Create Sales Order') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($salesOrders->isEmpty())
                        <div class="rounded-lg border border-dashed border-gray-300 px-6 py-12 text-center">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No sales orders yet') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Create a sales order after adding products to demonstrate transaction CRUD with master data relationships.') }}
                            </p>
                        </div>
                    @else
                        <form method="GET" action="{{ route('sales-orders.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex-1">
                                <x-text-input
                                    name="search"
                                    type="text"
                                    class="block w-full"
                                    :value="request('search')"
                                    placeholder="{{ __('Search by product, SKU, or order date') }}"
                                />
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button>{{ __('Search') }}</x-primary-button>

                                @if (request()->filled('search'))
                                    <a href="{{ route('sales-orders.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        {{ __('Clear') }}
                                    </a>
                                @endif
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Order Date') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Product') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('SKU') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Quantity') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Unit Price') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Total Price') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($salesOrders as $salesOrder)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $salesOrder->order_date->format('Y-m-d') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $salesOrder->product_name_snapshot }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $salesOrder->product_sku_snapshot }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ $salesOrder->quantity }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ \App\Support\Price::format($salesOrder->unit_price) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ \App\Support\Price::format($salesOrder->total_price) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @can('update', $salesOrder)
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <a
                                                            href="{{ route('sales-orders.edit', $salesOrder) }}"
                                                            class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100/80 active:bg-indigo-200/80 transform hover:-translate-y-0.5 active:translate-y-0 shadow-sm shadow-indigo-100/50 transition duration-150 ease-in-out"
                                                        >
                                                            {{ __('Edit') }}
                                                        </a>

                                                        <button
                                                            type="button"
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-sales-order-deletion-{{ $salesOrder->id }}')"
                                                            class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200 hover:bg-rose-100/80 active:bg-rose-200/80 transform hover:-translate-y-0.5 active:translate-y-0 shadow-sm shadow-rose-100/50 transition duration-150 ease-in-out"
                                                        >
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </div>

                                                    <x-modal name="confirm-sales-order-deletion-{{ $salesOrder->id }}" focusable>
                                                        <form method="POST" action="{{ route('sales-orders.destroy', $salesOrder) }}" class="p-6">
                                                            @csrf
                                                            @method('delete')

                                                            <h2 class="text-lg font-medium text-gray-900">
                                                                {{ __('Delete sales order?') }}
                                                            </h2>

                                                            <p class="mt-2 text-sm text-gray-600">
                                                                {{ __('Are you sure you want to delete the sales order for :name on :date? This action cannot be undone.', ['name' => $salesOrder->product_name_snapshot, 'date' => $salesOrder->order_date->format('Y-m-d')]) }}
                                                            </p>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                                    {{ __('Keep Sales Order') }}
                                                                </x-secondary-button>

                                                                <x-danger-button>
                                                                    {{ __('Delete Sales Order') }}
                                                                </x-danger-button>
                                                            </div>
                                                        </form>
                                                    </x-modal>
                                                @else
                                                    <span class="text-sm text-gray-400">{{ __('View only') }}</span>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $salesOrders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
