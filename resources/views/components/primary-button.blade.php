<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:from-indigo-500 hover:to-violet-500 active:from-indigo-700 active:to-violet-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-md shadow-indigo-200/50 hover:shadow-lg hover:shadow-indigo-200/60 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-150 ease-in-out']) }}>
    {{ $slot }}
</button>
