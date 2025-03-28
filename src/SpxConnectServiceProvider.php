<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\SpxClientInterface;

class SpxConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SpxClientInterface::class, SpxClient::class);
    }

    public function boot(): void
    {
    }
}
