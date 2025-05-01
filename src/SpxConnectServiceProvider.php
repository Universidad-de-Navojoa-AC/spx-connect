<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

class SpxConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SpxClientInterface::class, function ($app) {
            return new SpxClient([
                'auth' => $app->make(AuthService::class),
                'sunplusAccounts' => $app->make(SunPlusAccountService::class),
                'sunplusDimension' => $app->make(SunPlusDimensionService::class),
                'products' => $app->make(ProductService::class),
                'educationLevels' => $app->make(EducationLevelService::class),
            ]);
        });

        $this->app->alias(SpxClientInterface::class, 'spxconnect');
    }

    public function boot(): void
    {
        //
    }
}
