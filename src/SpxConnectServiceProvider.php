<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\SpxClientInterface;

class SpxConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpxClientInterface::class, SpxClient::class);
        $this->app->alias(SpxClientInterface::class, 'spxconnect');
    }

    public function boot(): void
    {
        //
    }
}
