<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class RecordLoginIp
{
    public function handle(Login $event): void
    {
        $event->user->forceFill(['last_login_ip' => request()->ip()])->saveQuietly();
    }
}
