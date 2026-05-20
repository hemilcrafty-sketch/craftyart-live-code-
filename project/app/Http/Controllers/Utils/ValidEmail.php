<?php

namespace App\Http\Controllers\Utils;

use Illuminate\Support\Str;

class ValidEmail
{
    public static function passes($email): string | null
    {
        $attribute = 'email';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "The {$attribute} has an invalid format.";
        }

        if (!str_contains($email, '@')) {
            return "The {$attribute} must contain a domain.";
        }

        $domain = strtolower(Str::after($email, '@'));

        if (empty($domain)) {
            return "The {$attribute} domain is missing.";
        }

        $fqdn = $domain . '.';

        if (!checkdnsrr($fqdn, 'MX') && !checkdnsrr($fqdn, 'A')) {
            return "The {$attribute} domain does not exist or accept mail.";
        }

        return null;
    }
}
