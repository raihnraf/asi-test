<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="overflow-hidden rounded-lg border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white shadow-sm">
                    <div class="p-6 text-gray-900">
                        <p class="text-sm font-medium uppercase tracking-wider text-emerald-700">{{ __('Total Revenue') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ \App\Support\Price::format($totalRevenue) }}</p>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Combined revenue from all recorded sales orders.') }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-blue-100 bg-gradient-to-br from-blue-50 to-white shadow-sm">
                    <div class="p-6 text-gray-900">
                        <p class="text-sm font-medium uppercase tracking-wider text-blue-700">{{ __('Total Products') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($totalProducts) }}</p>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Active product records available in the catalog.') }}</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-violet-100 bg-gradient-to-br from-violet-50 to-white shadow-sm sm:col-span-2 xl:col-span-1">
                    <div class="p-6 text-gray-900">
                        <p class="text-sm font-medium uppercase tracking-wider text-violet-700">{{ __('Total Transactions') }}</p>
                        <p class="mt-3 text-3xl font-semibold text-gray-900">{{ number_format($totalSalesOrders) }}</p>
                        <p class="mt-2 text-sm text-gray-600">{{ __('Sales orders processed across the application.') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Product Master Data') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Manage product inventory records with full create, read, update, and delete support.') }}
                        </p>

                        <div class="mt-6">
                            <a
                                href="{{ route('products.index') }}"
                                class="inline-flex items-center rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-md shadow-indigo-200/50 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-200/60 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                            >
                                {{ __('Open Products') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Sales Orders') }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Create transactions linked to products with automatic unit-price and total calculation.') }}
                        </p>

                        <div class="mt-6">
                            <a
                                href="{{ route('sales-orders.index') }}"
                                class="inline-flex items-center rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-md shadow-indigo-200/50 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-200/60 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                            >
                                {{ __('Open Sales Orders') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
