<?php

namespace App\Http\Requests;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-sales-orders') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists(Product::class, 'id')],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $product = Product::query()->find($this->integer('product_id'));

                    if (! $product) {
                        return;
                    }

                    if ((int) $value > $product->stock) {
                        $fail('The quantity may not exceed available stock.');
                    }
                },
            ],
            'order_date' => ['required', 'date'],
        ];
    }
}
