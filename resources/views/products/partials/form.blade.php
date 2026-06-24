@php
    $priceValue = old('price', $product ? number_format((float) $product->price, 2, '.', '') : '');
@endphp

<div>
    <x-input-label for="name" :value="__('Name')" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name ?? '')" required :autofocus="$autofocus" />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div>
    <x-input-label for="sku" :value="__('SKU')" />
    <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $product->sku ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('sku')" />
</div>

<div>
    <x-input-label for="price" :value="__('Price')" />
    <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="$priceValue" required />
    <x-input-error class="mt-2" :messages="$errors->get('price')" />
</div>

<div>
    <x-input-label for="stock" :value="__('Stock')" />
    <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('stock')" />
</div>
