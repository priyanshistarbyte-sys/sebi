<?php

if (! function_exists('format_money')) {
    /**
     * Format a number based on the selected currency.
     *
     * @param float|int $amount
     * @param string|null $symbol  One of: '₹', '$', '€', '£' (nullable -> default '$')
     * @param int $decimals
     * @param bool $withSymbol
     */
    function format_money($amount, ?string $symbol = null, int $decimals = 2, bool $withSymbol = true): string
    {
        $symbol = $symbol ?: '₹';
        $formatted = format_number_by_currency($amount, $symbol, $decimals);
        return $withSymbol ? ($symbol . ' ' . $formatted) : $formatted;
    }
}

if (! function_exists('format_number_by_currency')) {
    function format_number_by_currency($amount, string $symbol, int $decimals = 2): string
    {
        // negative-safe
        $neg = $amount < 0;
        $amount = abs((float)$amount);

        // € often uses comma decimals and dot separators
        if ($symbol === '₹') {
            $s = format_indian_number($amount, $decimals);     // 9,00,000.00
        } else { // $, £, € default
            $s = number_format($amount, $decimals, '.', ',');  // 1,234,567.89
        }

        return $neg ? ('-' . $s) : $s;
    }
}

if (! function_exists('format_indian_number')) {
    /**
     * Indian numbering format (lakhs/crores). 1234567.89 -> 12,34,567.89
     */
    function format_indian_number(float $num, int $decimals = 2): string
    {
        $parts = explode('.', number_format($num, $decimals, '.', '')); // "1234567.89"
        $int   = $parts[0];
        $dec   = $parts[1] ?? null;

        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest  = substr($int, 0, -3);
            // insert commas every 2 digits in the rest
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $int   = $rest . ',' . $last3;
        }

        return $decimals > 0 ? ($int . '.' . $dec) : $int;
    }
}
