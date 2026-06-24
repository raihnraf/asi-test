<?php

namespace App\Http\Requests;

use App\Models\SalesOrder;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateSalesOrderRequest extends SalesOrderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var SalesOrder|null $salesOrder */
        $salesOrder = $this->route('salesOrder');

        return $this->salesOrderRules($salesOrder);
    }
}
