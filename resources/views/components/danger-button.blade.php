<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-600 to-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-rose-500 hover:to-red-500 active:from-rose-700 active:to-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-md shadow-rose-200/50 hover:shadow-lg hover:shadow-rose-200/60 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
