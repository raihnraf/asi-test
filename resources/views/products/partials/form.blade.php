@php
    $priceValue = old('price', $product ? \App\Support\Price::format($product->price) : '');
@endphp

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name ?? '')" placeholder="e.g. Gaming Keyboard" required :autofocus="$autofocus" />
    <p class="mt-2 text-sm text-gray-500">{{ __('Example: Gaming Keyboard') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="sku" :value="__('SKU')" />
    <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $product->sku ?? '')" placeholder="e.g. KEY-10001" required />
    <p class="mt-2 text-sm text-gray-500">{{ __('Example: KEY-10001') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('sku')" />
</div>

<div>
    <x-input-label for="price" :value="__('Price')" />
    <x-text-input
        id="price"
        name="price"
        type="text"
        inputmode="decimal"
        class="mt-1 block w-full"
        :value="$priceValue"
        placeholder="e.g. 25.000"
        x-data="priceInput()"
        x-init="$el.value = format($el.value)"
        x-on:input="$el.value = format($el.value)"
        required
    />
    <p class="mt-2 text-sm text-gray-500">{{ __('Example: 25.000') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('price')" />
</div>

<div>
    <x-input-label for="stock" :value="__('Stock')" />
    <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('stock')" />
</div>
