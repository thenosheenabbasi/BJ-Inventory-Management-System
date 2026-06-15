<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function symbol(): string
    {
        return config('currency.symbol', 'AED');
    }

    public static function code(): string
    {
        return config('currency.code', 'AED');
    }

    public static function format(string|int|float $amount): string
    {
        $number = floatval($amount);
        $formatted = floor($number) === $number
            ? number_format($number, 0, '.', ',')
            : number_format($number, 2, '.', ',');

        return sprintf(config('currency.format', '%s %s'), self::symbol(), $formatted);
    }
}
