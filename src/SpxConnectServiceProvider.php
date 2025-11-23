<?php

namespace Unav\SpxConnect;

use Illuminate\Support\ServiceProvider;
use Unav\SpxConnect\Contracts\AuthServiceInterface;
use Unav\SpxConnect\Contracts\CacheManagerInterface;
use Unav\SpxConnect\Contracts\CfdiServiceInterface;
use Unav\SpxConnect\Contracts\ClientServiceInterface;
use Unav\SpxConnect\Contracts\EducationLevelServiceInterface;
use Unav\SpxConnect\Contracts\JournalServiceInterface;
use Unav\SpxConnect\Contracts\ProductServiceInterface;
use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Contracts\SunPlusAccountServiceInterface;
use Unav\SpxConnect\Contracts\SunPlusDimensionServiceInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\JournalService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

class SpxConnectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register service interfaces
        $this->app->singleton(AuthServiceInterface::class, AuthService::class);
        $this->app->singleton(CacheManagerInterface::class, CacheManager::class);
        $this->app->singleton(CfdiServiceInterface::class, CfdiService::class);
        $this->app->singleton(ClientServiceInterface::class, ClientService::class);
        $this->app->singleton(EducationLevelServiceInterface::class, EducationLevelService::class);
        $this->app->singleton(JournalServiceInterface::class, JournalService::class);
        $this->app->singleton(ProductServiceInterface::class, ProductService::class);
        $this->app->singleton(SunPlusAccountServiceInterface::class, SunPlusAccountService::class);
        $this->app->singleton(SunPlusDimensionServiceInterface::class, SunPlusDimensionService::class);

        // Note: TokenManager is a static utility class and doesn't require container registration
        
        // Register main client
        $this->app->singleton(SpxClientInterface::class, SpxClient::class);
        $this->app->alias(SpxClientInterface::class, 'spxconnect');
    }

    public function boot(): void
    {
        //
    }
}
