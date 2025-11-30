<?php

namespace Unav\SpxConnect\Tests\Feature;

use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Facades\SpxConnect;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\JournalService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;
use Unav\SpxConnect\SpxClient;
use Unav\SpxConnect\SpxConnectServiceProvider;
use Unav\SpxConnect\Tests\TestCase;

class SpxConnectServiceProviderTest extends TestCase
{
    public function testServiceProviderRegistersSpxClientInterface(): void
    {
        $this->assertTrue($this->app->bound(SpxClientInterface::class));
    }

    public function testServiceProviderRegistersSpxConnectAlias(): void
    {
        $this->assertTrue($this->app->bound('spxconnect'));
    }

    public function testSpxClientInterfaceResolvesToSpxClient(): void
    {
        $resolved = $this->app->make(SpxClientInterface::class);

        $this->assertInstanceOf(SpxClient::class, $resolved);
    }

    public function testAliasResolvesToSpxClient(): void
    {
        $resolved = $this->app->make('spxconnect');

        $this->assertInstanceOf(SpxClient::class, $resolved);
    }

    public function testFacadeWorks(): void
    {
        $this->assertInstanceOf(AuthService::class, SpxConnect::auth());
    }

    public function testFacadeCacheWorks(): void
    {
        $this->assertInstanceOf(CacheManager::class, SpxConnect::cache());
    }

    public function testFacadeSunplusAccountsWorks(): void
    {
        $this->assertInstanceOf(SunPlusAccountService::class, SpxConnect::sunplusAccounts());
    }

    public function testFacadeSunplusDimensionWorks(): void
    {
        $this->assertInstanceOf(SunPlusDimensionService::class, SpxConnect::sunplusDimension());
    }

    public function testFacadeProductsWorks(): void
    {
        $this->assertInstanceOf(ProductService::class, SpxConnect::products());
    }

    public function testFacadeClientsWorks(): void
    {
        $this->assertInstanceOf(ClientService::class, SpxConnect::clients());
    }

    public function testFacadeEducationLevelsWorks(): void
    {
        $this->assertInstanceOf(EducationLevelService::class, SpxConnect::educationLevels());
    }

    public function testFacadeJournalWorks(): void
    {
        $this->assertInstanceOf(JournalService::class, SpxConnect::journal());
    }

    public function testFacadeCfdiWorks(): void
    {
        $this->assertInstanceOf(CfdiService::class, SpxConnect::cfdi());
    }

    public function testServiceProviderIsLoaded(): void
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(SpxConnectServiceProvider::class, $loadedProviders);
    }
}
