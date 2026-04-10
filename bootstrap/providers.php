<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\MailSettingsServiceProvider;

return [
    AppServiceProvider::class,
    MailSettingsServiceProvider::class,
    FortifyServiceProvider::class,
];
