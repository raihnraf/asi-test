<div>
    <x-input-label for="product_id" :value="__('Product')" />
    <select id="product_id" name="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        <option value="">{{ __('Select a product') }}</option>
        @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected((string) old('product_id', $salesOrder->product_id ?? '') === (string) $product->id)>
                {{ $product->name }} ({{ $product->sku }}) - {{ number_format((float) $product->price, 2, '.', '') }}
            </option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('product_id')" />
</div>

<div>
    <x-input-label for="quantity" :value="__('Quantity')" />
    <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" :value="old('quantity', $salesOrder->quantity ?? '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
</div>

<div>
    <x-input-label for="order_date" :value="__('Order Date')" />
    <x-text-input id="order_date" name="order_date" type="date" class="mt-1 block w-full" :value="old('order_date', isset($salesOrder) ? $salesOrder->order_date->format('Y-m-d') : '')" required />
    <x-input-error class="mt-2" :messages="$errors->get('order_date')" />
</div>

<p class="text-sm text-gray-600">
    {{ __('Unit price and total price are calculated automatically from the selected product and quantity when you save the transaction.') }}
</p>
