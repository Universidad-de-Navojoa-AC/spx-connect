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
use Unav\SpxConnect\Tests\TestCase;

class SpxClientTest extends TestCase
{
    protected SpxClient $spxClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->spxClient = $this->app->make(SpxClientInterface::class);
    }

    public function testSpxClientIsRegisteredInContainer(): void
    {
        $client = $this->app->make(SpxClientInterface::class);

        $this->assertInstanceOf(SpxClient::class, $client);
    }

    public function testSpxClientIsSingleton(): void
    {
        $client1 = $this->app->make(SpxClientInterface::class);
        $client2 = $this->app->make(SpxClientInterface::class);

        $this->assertSame($client1, $client2);
    }

    public function testSpxClientAliasWorks(): void
    {
        $client = $this->app->make('spxconnect');

        $this->assertInstanceOf(SpxClient::class, $client);
    }

    public function testAuthMethodReturnsAuthService(): void
    {
        $authService = $this->spxClient->auth();

        $this->assertInstanceOf(AuthService::class, $authService);
    }

    public function testCacheMethodReturnsCacheManager(): void
    {
        $cacheManager = $this->spxClient->cache();

        $this->assertInstanceOf(CacheManager::class, $cacheManager);
    }

    public function testSunplusAccountsMethodReturnsService(): void
    {
        $accountService = $this->spxClient->sunplusAccounts();

        $this->assertInstanceOf(SunPlusAccountService::class, $accountService);
    }

    public function testSunplusDimensionMethodReturnsService(): void
    {
        $dimensionService = $this->spxClient->sunplusDimension();

        $this->assertInstanceOf(SunPlusDimensionService::class, $dimensionService);
    }

    public function testProductsMethodReturnsService(): void
    {
        $productService = $this->spxClient->products();

        $this->assertInstanceOf(ProductService::class, $productService);
    }

    public function testClientsMethodReturnsService(): void
    {
        $clientService = $this->spxClient->clients();

        $this->assertInstanceOf(ClientService::class, $clientService);
    }

    public function testEducationLevelsMethodReturnsService(): void
    {
        $educationLevelService = $this->spxClient->educationLevels();

        $this->assertInstanceOf(EducationLevelService::class, $educationLevelService);
    }

    public function testJournalMethodReturnsService(): void
    {
        $journalService = $this->spxClient->journal();

        $this->assertInstanceOf(JournalService::class, $journalService);
    }

    public function testCfdiMethodReturnsService(): void
    {
        $cfdiService = $this->spxClient->cfdi();

        $this->assertInstanceOf(CfdiService::class, $cfdiService);
    }

    public function testAllServicesAreSameInstance(): void
    {
        $auth1 = $this->spxClient->auth();
        $auth2 = $this->spxClient->auth();

        $this->assertSame($auth1, $auth2);
    }
}
