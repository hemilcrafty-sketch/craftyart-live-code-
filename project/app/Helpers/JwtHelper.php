<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use stdClass;

class JwtHelper
{
    private static function secret(): string
    {
        return '677^#fdf%fdf!ewe';
    }

    public static function generate(array $payload, int $days = 30): string
    {
        $now = Carbon::now()->timestamp;

        $token = array_merge($payload, [
            'iat' => $now,
            'exp' => Carbon::now()->addDays($days)->timestamp,
        ]);

        return JWT::encode($token, self::secret(), 'HS256');
    }

    public static function generateByHours(array $payload, int $hours = 30): string
    {
        $now = Carbon::now()->timestamp;

        $token = array_merge($payload, [
            'iat' => $now,
            'exp' => Carbon::now()->addHours($hours)->timestamp,
        ]);

        return JWT::encode($token, self::secret(), 'HS256');
    }

    public static function decode(string $token): stdClass
    {
        return JWT::decode($token, new Key(self::secret(), 'HS256'));
    }
}
