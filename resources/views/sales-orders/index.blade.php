<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales Orders') }}
            </h2>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('sales-orders.export', request()->only('search')) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ __('Export CSV') }}
                </a>

                @can('create-sales-orders')
                    <a
                        href="{{ route('sales-orders.create') }}"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $salesOrder->product->name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $salesOrder->product->sku }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ $salesOrder->quantity }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $salesOrder->unit_price, 2, '.', '') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $salesOrder->total_price, 2, '.', '') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @can('manage-sales-orders')
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <a href="{{ route('sales-orders.edit', $salesOrder) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                                            {{ __('Edit Sales Order') }}
                                                        </a>

                                                        <x-danger-button
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-sales-order-deletion-{{ $salesOrder->id }}')"
                                                        >{{ __('Delete Sales Order') }}</x-danger-button>
                                                    </div>

                                                    <x-modal name="confirm-sales-order-deletion-{{ $salesOrder->id }}" focusable>
                                                        <form method="POST" action="{{ route('sales-orders.destroy', $salesOrder) }}" class="p-6">
                                                            @csrf
                                                            @method('delete')

                                                            <h2 class="text-lg font-medium text-gray-900">
                                                                {{ __('Delete sales order?') }}
                                                            </h2>

                                                            <p class="mt-2 text-sm text-gray-600">
                                                                {{ __('Are you sure you want to delete the sales order for :name on :date? This action cannot be undone.', ['name' => $salesOrder->product->name, 'date' => $salesOrder->order_date->format('Y-m-d')]) }}
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
