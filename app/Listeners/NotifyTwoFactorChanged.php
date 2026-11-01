<?php

namespace App\Listeners;

use App\Notifications\TwoFactorChangedNotification;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

class NotifyTwoFactorChanged
{
    public function handle(TwoFactorAuthenticationConfirmed|TwoFactorAuthenticationDisabled $event): void
    {
        $event->user->notify(new TwoFactorChangedNotification(
            $event instanceof TwoFactorAuthenticationConfirmed,
            app()->getLocale(),
            request()->ip(),
        ));
    }
}
