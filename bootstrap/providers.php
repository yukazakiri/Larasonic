<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JetstreamServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\AuthServiceProvider;
return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
    JetstreamServiceProvider::class,
    AuthServiceProvider::class,
];
