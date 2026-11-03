<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Bugsnag\BugsnagLaravel\BugsnagServiceProvider;

return [
    BugsnagServiceProvider::class,
    AppServiceProvider::class,
    FortifyServiceProvider::class,
];
