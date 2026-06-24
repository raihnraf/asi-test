<?php

namespace App\Exceptions;

use RuntimeException;

final class InsufficientStockException extends RuntimeException
{
    public const MESSAGE = 'The quantity may not exceed available stock.';

    public static function forRequestedQuantity(): self
    {
        return new self(self::MESSAGE);
    }
}
