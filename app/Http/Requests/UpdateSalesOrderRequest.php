<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\SalesOrder;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-sales-orders') ?? false;
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

                    /** @var SalesOrder|null $salesOrder */
                    $salesOrder = $this->route('salesOrder');
                    $availableStock = $product->stock;

                    if ($salesOrder && $salesOrder->product_id === $product->id) {
                        $availableStock += $salesOrder->quantity;
                    }

                    if ((int) $value > $availableStock) {
                        $fail('The quantity may not exceed available stock.');
                    }
                },
            ],
            'order_date' => ['required', 'date'],
        ];
    }
}
