@php
    $initials = collect(preg_split('/\s+/', trim(config('app.name', 'ASI Test'))) ?: [])
        ->filter()
        ->take(2)
        ->map(fn (string $segment) => strtoupper(substr($segment, 0, 1)))
        ->implode('');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-900 text-sm font-bold tracking-wide text-white shadow-sm">
        {{ $initials !== '' ? $initials : 'AT' }}
    </span>

    <span class="text-sm font-semibold tracking-wide text-gray-900">
        {{ config('app.name', 'ASI Test') }}
    </span>
</span>
