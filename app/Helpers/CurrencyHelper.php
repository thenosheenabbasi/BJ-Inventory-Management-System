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
        $formatted = number_format(floor($number), 0, '.', ',');

        return sprintf(config('currency.format', '%s %s'), self::symbol(), $formatted);
    }
}
