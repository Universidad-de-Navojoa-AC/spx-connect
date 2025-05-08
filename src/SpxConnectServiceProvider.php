<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\CacheManagerInterface;
use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

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
