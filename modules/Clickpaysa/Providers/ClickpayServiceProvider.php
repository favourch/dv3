<?php

namespace Modules\Clickpaysa\Providers;

use Illuminate\Support\ServiceProvider;


class ClickpayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}   