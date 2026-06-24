<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Rules\HasAvailableStock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class SalesOrderRequest extends FormRequest
{
    protected const MINIMUM_QUANTITY = 1;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function salesOrderRules(?SalesOrder $salesOrder = null): array
    {
        return [
            'product_id' => ['required', Rule::exists(Product::class, 'id')],
            'quantity' => [
                'required',
                'integer',
                'min:'.self::MINIMUM_QUANTITY,
                new HasAvailableStock($this->integer('product_id'), $salesOrder),
            ],
            'order_date' => ['required', 'date'],
        ];
    }
}
