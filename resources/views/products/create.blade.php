<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('products.store') }}" class="space-y-6 p-6">
                    @csrf

                    @include('products.partials.form', ['product' => null, 'autofocus' => true])

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transform hover:-translate-y-0.5 active:translate-y-0">
                            {{ __('Back to Products') }}
                        </a>

                        <x-primary-button>{{ __('Save Product') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
