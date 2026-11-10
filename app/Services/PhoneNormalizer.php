<?php
declare(strict_types=1);
namespace App\Services;

class PhoneNormalizer
{
    private const COUNTRIES = ['TN', 'FR', 'MA', 'DZ'];

    /**
     * Normalise un numero au format international E.164.
     * Gere Tunisie (+216, 8 chiffres), France (+33), Maroc (+212), Algerie (+213).
     */
    public static function normalize(?string $phone, string $country = 'TN'): string
    {
        if ($phone === null) return '';
        $p = trim($phone);
        if ($p === '') return '';
        $hasPlus = str_starts_with($p, '+');
        $digits = preg_replace('/\D+/', '', $p) ?? '';
        if ($digits === '') return '';

        if ($hasPlus && strlen($digits) >= 8) return '+' . $digits;

        $country = in_array($country, self::COUNTRIES, true) ? $country : 'TN';
        switch ($country) {
            case 'TN':
                if (strlen($digits) === 8) return '+216' . $digits;
                break;
            case 'FR':
                if (strlen($digits) === 10 && $digits[0] === '0') return '+33' . substr($digits, 1);
                if (strlen($digits) === 9) return '+33' . $digits;
                break;
            case 'MA':
                if (strlen($digits) === 10 && $digits[0] === '0') return '+212' . substr($digits, 1);
                break;
            case 'DZ':
                if (strlen($digits) === 10 && $digits[0] === '0') return '+213' . substr($digits, 1);
                if (strlen($digits) === 9) return '+213' . $digits;
                break;
        }
        return ''; // non normalisable avec certitude
    }

    public static function telLink(string $e164): string
    {
        return 'tel:+' . ltrim($e164, '+');
    }

    public static function waLink(string $e164): string
    {
        return 'https://wa.me/' . ltrim($e164, '+');
    }
}
