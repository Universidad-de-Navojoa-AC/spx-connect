<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\SunPlusAccountService;

class SpxConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SpxClientInterface::class, function ($app) {
            return new SpxClient([
                'auth' => $app->make(AuthService::class),
                'sunplusAccounts' => $app->make(SunPlusAccountService::class),
            ]);
        });
    }

    public function boot(): void
    {
        //
    }
}
