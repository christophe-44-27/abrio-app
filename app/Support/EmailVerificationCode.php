<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Short-lived 6-digit codes used to verify a user's email address.
 * Only a hash of the code is cached, and attempts are capped.
 */
class EmailVerificationCode
{
    public const TTL_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    public static function issue(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put(self::key($user), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        return $code;
    }

    public static function check(User $user, string $code): bool
    {
        $entry = Cache::get(self::key($user));

        if (! $entry || $entry['attempts'] >= self::MAX_ATTEMPTS) {
            return false;
        }

        if (! Hash::check($code, $entry['hash'])) {
            $entry['attempts']++;
            Cache::put(self::key($user), $entry, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        Cache::forget(self::key($user));

        return true;
    }

    private static function key(User $user): string
    {
        return "email-verification-code:{$user->getKey()}";
    }
}
