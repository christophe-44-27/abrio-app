<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $email
 * @property string|null $ip_address
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['email', 'ip_address', 'reason'])]
class Ban extends Model
{
    /**
     * Whether the email or the IP address is covered by a ban.
     */
    public static function matches(?string $email, ?string $ip): bool
    {
        if (! $email && ! $ip) {
            return false;
        }

        return static::query()
            ->where(function ($query) use ($email, $ip) {
                if ($email) {
                    $query->orWhere('email', strtolower($email));
                }

                if ($ip) {
                    $query->orWhere('ip_address', $ip);
                }
            })
            ->exists();
    }

    protected function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }
}
