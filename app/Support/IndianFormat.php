<?php

namespace App\Support;

class IndianFormat
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen',
        'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    /** 311140 => "3,11,140.00" */
    public static function money($number, int $decimals = 2): string
    {
        $number   = (float) $number;
        $negative = $number < 0;
        $parts    = explode('.', number_format(abs($number), $decimals, '.', ''));
        $int      = $parts[0];
        $dec      = $parts[1] ?? '';

        if (strlen($int) > 3) {
            $last3 = substr($int, -3);
            $rest  = substr($int, 0, -3);
            $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $int   = $rest . ',' . $last3;
        }

        return ($negative ? '-' : '') . $int . ($decimals > 0 ? '.' . $dec : '');
    }

    /** 311140 => "Rs. Three Lakh Eleven Thousand One Hundred Forty Only" */
    public static function rupeesInWords($amount): string
    {
        $amount = round((float) $amount, 2);
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $words = 'Rs. ' . ($rupees === 0 ? 'Zero' : self::words($rupees));

        if ($paise > 0) {
            $words .= ' and ' . self::words($paise) . ' Paise';
        }

        return $words . ' Only';
    }

    private static function words(int $n): string
    {
        $out = [];

        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand'] as $value => $label) {
            if ($n >= $value) {
                $out[] = self::words(intdiv($n, $value)) . ' ' . $label;
                $n %= $value;
            }
        }

        if ($n >= 100) {
            $out[] = self::ONES[intdiv($n, 100)] . ' Hundred';
            $n %= 100;
        }

        if ($n >= 20) {
            $out[] = trim(self::TENS[intdiv($n, 10)] . ' ' . self::ONES[$n % 10]);
        } elseif ($n > 0) {
            $out[] = self::ONES[$n];
        }

        return implode(' ', $out);
    }
}