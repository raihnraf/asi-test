<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products') }}
            </h2>

            @can('manage-products')
                <a
                    href="{{ route('products.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    {{ __('Create Product') }}
                </a>
            @endcan
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

                    @if ($products->isEmpty())
                        <div class="rounded-lg border border-dashed border-gray-300 px-6 py-12 text-center">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('No products yet') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                {{ __('Add your first product to start managing inventory data for this test project.') }}
                            </p>
                        </div>
                    @else
                        <form method="GET" action="{{ route('products.index') }}" class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <div class="flex-1">
                                <x-text-input
                                    name="search"
                                    type="text"
                                    class="block w-full"
                                    :value="request('search')"
                                    placeholder="{{ __('Search by product name or SKU') }}"
                                />
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button>{{ __('Search') }}</x-primary-button>

                                @if (request()->filled('search'))
                                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        {{ __('Clear') }}
                                    </a>
                                @endif
                            </div>
                        </form>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Name') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('SKU') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Price') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Stock') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($products as $product)
                                        <tr>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ $product->name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600">{{ $product->sku }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ number_format((float) $product->price, 2, '.', '') }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-600">{{ $product->stock }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                @can('manage-products')
                                                    <div class="flex flex-wrap items-center gap-3">
                                                        <a href="{{ route('products.edit', $product) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                                            {{ __('Edit Product') }}
                                                        </a>

                                                        <x-danger-button
                                                            x-data=""
                                                            x-on:click.prevent="$dispatch('open-modal', 'confirm-product-deletion-{{ $product->id }}')"
                                                        >{{ __('Delete Product') }}</x-danger-button>
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

                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
