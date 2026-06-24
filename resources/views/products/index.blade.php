<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ __('Products') }}
                </h2>

                <p class="text-sm text-gray-500">
                    {{ __('Manage product master data with cleaner spacing and easier-to-read pricing.') }}
                </p>
            </div>

            @can('create', \App\Models\Product::class)
                <a
                    href="{{ route('products.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-md shadow-indigo-200/50 hover:from-indigo-500 hover:to-violet-500 hover:shadow-lg hover:shadow-indigo-200/60 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                    {{ __('Create Product') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-10 sm:py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="space-y-8 p-6 text-gray-900 sm:p-8">
                    @if (session('status'))
                        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($products->isEmpty())
                        <div class="rounded-xl border border-dashed border-gray-300 px-6 py-14 text-center sm:px-10">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No products yet') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Add your first product to start managing inventory data for this test project.') }}
                            </p>
                        </div>
                    @else
                        <div class="flex flex-col gap-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                            <div>
                                <p class="text-sm font-semibold text-blue-900">
                                    {{ number_format($products->total()) }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} {{ __('listed') }}
                                </p>

                                <p class="mt-1 text-sm text-blue-700">
                                    @if (request()->filled('search'))
                                        {{ __('Showing results for ":search".', ['search' => request('search')]) }}
                                    @else
                                        {{ __('Browse the latest saved catalog entries.') }}
                                    @endif
                                </p>
                            </div>

                            <p class="text-sm text-blue-700">
                                {{ __('Prices now use 25.000 style formatting.') }}
                            </p>
                        </div>

                        <form method="GET" action="{{ route('products.index') }}" class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                                <div class="flex-1">
                                    <label for="product-search" class="mb-2 block text-sm font-medium text-gray-700">
                                        {{ __('Search products') }}
                                    </label>

                                    <x-text-input
                                        id="product-search"
                                        name="search"
                                        type="text"
                                        class="block w-full"
                                        :value="request('search')"
                                        placeholder="{{ __('Search by product name or SKU') }}"
                                    />
                                </div>

                                <div class="flex flex-wrap items-center gap-3">
                                    <x-primary-button>{{ __('Search') }}</x-primary-button>

                                    @if (request()->filled('search'))
                                        <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                            {{ __('Clear') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                            <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('SKU') }}</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Price') }}</th>
                                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Stock') }}</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($products as $product)
                                        <tr class="align-top transition hover:bg-gray-50/80">
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700">
                                                    {{ $product->sku }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium tabular-nums text-gray-700">{{ \App\Support\Price::format($product->price) }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm tabular-nums text-gray-600">{{ number_format($product->stock) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                @can('update', $product)
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <a
                                                            href="{{ route('products.edit', $product) }}"
                                                            class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 hover:bg-indigo-100/80 active:bg-indigo-200/80 transform hover:-translate-y-0.5 active:translate-y-0 shadow-sm shadow-indigo-100/50 transition duration-150 ease-in-out"
                                                        >
                                                            {{ __('Edit') }}
                                                        </a>

                                                        <button
                                                            type="button"
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-product-deletion-{{ $product->id }}')"
                                                            class="inline-flex items-center rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200 hover:bg-rose-100/80 active:bg-rose-200/80 transform hover:-translate-y-0.5 active:translate-y-0 shadow-sm shadow-rose-100/50 transition duration-150 ease-in-out"
                                                        >
                                                            {{ __('Delete') }}
                                                        </button>
                                                    </div>

                                                    <x-modal name="confirm-product-deletion-{{ $product->id }}" focusable>
                                                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="p-6">
                                                            @csrf
                                                            @method('delete')

                                                            <h2 class="text-lg font-medium text-gray-900">
                                                                {{ __('Delete product?') }}
                                                            </h2>

                                                            <p class="mt-2 text-sm text-gray-600">
                                                                {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $product->name]) }}
                                                            </p>

                                                            <div class="mt-6 flex justify-end gap-3">
                                                                <x-secondary-button x-on:click="$dispatch('close')">
                                                                    {{ __('Keep Product') }}
                                                                </x-secondary-button>

                                                                <x-danger-button>
                                                                    {{ __('Delete Product') }}
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
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
