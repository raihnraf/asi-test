<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrder extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'order_date',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'order_date' => 'date',
        ];
    }

    public function scopeWithProduct(Builder $query): Builder
    {
        return $query->with('product');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search): void {
            $query->where('order_date', 'like', "%{$search}%")
                ->orWhereHas('product', function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
        });
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->latest('order_date')->latest('id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
