<?php

namespace App\Support;

final class Price
{
    public static function format(int|float|string|null $amount): string
    {
        $formatted = number_format((float) $amount, 2, ',', '.');

        return str_ends_with($formatted, ',00')
            ? substr($formatted, 0, -3)
            : $formatted;
    }

    public static function parseInput(int|float|string|null $value): int|float|null
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        $normalized = preg_replace('/\s+/', '', trim($value));

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^-?\d{1,3}(?:\.\d{3})+(?:,\d+)?$/', $normalized) === 1) {
            return (float) str_replace(',', '.', str_replace('.', '', $normalized));
        }

        if (preg_match('/^-?\d+(?:,\d+)?$/', $normalized) === 1) {
            return (float) str_replace(',', '.', $normalized);
        }

        if (preg_match('/^-?\d{1,3}(?:,\d{3})+(?:\.\d+)?$/', $normalized) === 1) {
            return (float) str_replace(',', '', $normalized);
        }

        if (preg_match('/^-?\d+(?:\.\d+)?$/', $normalized) === 1) {
            return (float) $normalized;
        }

        return null;
    }
}
