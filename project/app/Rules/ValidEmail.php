<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class ValidEmail implements Rule
{
    protected bool $smtpCheck;
    protected string $errorMessage = 'Invalid email.';

    public function __construct(bool $smtpCheck = false)
    {
        $this->smtpCheck = $smtpCheck;
    }

    public function passes($attribute, $value): bool
    {
        // Ensure value contains "@"
        if (!str_contains($value, '@')) {
            $this->errorMessage = "The {$attribute} must contain a domain.";
            return false;
        }

        $domain = strtolower(Str::after($value, '@'));

        // Ensure domain part is not empty
        if (empty($domain)) {
            $this->errorMessage = "The {$attribute} domain is missing.";
            return false;
        }

        // Step 1: MX record check
        if (!checkdnsrr($domain, 'MX')) {
            $this->errorMessage = "The {$attribute} domain does not accept mail.";
            return false;
        }

        // Step 2: SMTP handshake (optional)
        if ($this->smtpCheck && !$this->verifySmtp($value)) {
            $this->errorMessage = "The {$attribute} address is not deliverable.";
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->errorMessage;
    }

    /**
     * Try SMTP connection to check deliverability.
     */
    protected function verifySmtp(string $email): bool
    {
        [$user, $domain] = explode('@', $email);

        // Get MX records
        if (!getmxrr($domain, $mxHosts)) {
            return false;
        }

        $mxHost = $mxHosts[0] ?? $domain;

        $connection = @fsockopen($mxHost, 25, $errno, $errstr, 5);
        if (!$connection) {
            return false;
        }

        stream_set_timeout($connection, 5);

        $this->sendCommand($connection, "HELO example.com");
        $this->sendCommand($connection, "MAIL FROM:<check@example.com>");
        $response = $this->sendCommand($connection, "RCPT TO:<{$email}>");

        fclose($connection);

        // 250 = OK
        return str_starts_with($response, '250');
    }

    protected function sendCommand($connection, string $command): string
    {
        fwrite($connection, $command . "\r\n");
        return fgets($connection, 1024) ?: '';
    }
}
