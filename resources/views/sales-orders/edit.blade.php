<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Sales Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('sales-orders.update', $salesOrder) }}" class="space-y-6 p-6">
                    @csrf
                    @method('PATCH')

                    @include('sales-orders.partials.form', ['salesOrder' => $salesOrder])

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('sales-orders.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            {{ __('Back to Sales Orders') }}
                        </a>

                        <x-primary-button>{{ __('Update Sales Order') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
