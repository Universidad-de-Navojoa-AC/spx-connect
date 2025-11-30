<?php

namespace Unav\SpxConnect\Tests\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class ApiPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        TokenManager::setToken('performance-test-token');
    }

    public function testTokenRetrievalPerformance(): void
    {
        $userId = 'perf-user';
        TokenManager::setToken('test-token', userId: $userId);

        $startTime = microtime(true);

        for ($i = 0; $i < 1000; $i++) {
            TokenManager::getToken($userId);
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 1000 token retrievals should complete in less than 500ms
        $this->assertLessThan(500, $duration, "Token retrieval took {$duration}ms for 1000 operations");
    }

    public function testCacheManagerPerformance(): void
    {
        $cacheManager = new CacheManager();

        $startTime = microtime(true);

        for ($i = 0; $i < 500; $i++) {
            $cacheManager->put("key_{$i}", "value_{$i}");
            $cacheManager->get("key_{$i}");
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 500 put/get operations should complete in less than 300ms
        $this->assertLessThan(300, $duration, "CacheManager operations took {$duration}ms for 500 operations");
    }

    public function testClientServiceSearchPerformanceWithMockedHttp(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [['id' => 1, 'name' => 'Client']],
            ], 200),
        ]);

        $clientService = new ClientService();

        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $clientService->search("query_{$i}");
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 100 mocked HTTP requests should complete quickly
        $this->assertLessThan(2000, $duration, "ClientService search took {$duration}ms for 100 operations");
    }

    public function testProductServiceSearchPerformanceWithMockedHttp(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [['id' => 1, 'name' => 'Product', 'price' => 100]],
            ], 200),
        ]);

        $productService = new ProductService();

        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            $productService->search("product_{$i}");
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 100 mocked HTTP requests should complete quickly
        $this->assertLessThan(2000, $duration, "ProductService search took {$duration}ms for 100 operations");
    }

    public function testAccountServicePerformanceWithMockedHttp(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [
                    ['code' => '1001', 'name' => 'Account 1'],
                    ['code' => '1002', 'name' => 'Account 2'],
                ],
            ], 200),
        ]);

        $accountService = new SunPlusAccountService();

        $startTime = microtime(true);

        for ($i = 0; $i < 50; $i++) {
            $accountService->getAll();
            $accountService->findByCode('1001');
            $accountService->search('Account');
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 150 mocked HTTP requests should complete in reasonable time
        $this->assertLessThan(3000, $duration, "SunPlusAccountService operations took {$duration}ms for 150 operations");
    }

    public function testMultipleTokensPerformance(): void
    {
        $startTime = microtime(true);

        // Create 100 different user tokens
        for ($i = 0; $i < 100; $i++) {
            TokenManager::setToken("token_{$i}", userId: "user_{$i}");
        }

        // Retrieve all tokens
        for ($i = 0; $i < 100; $i++) {
            TokenManager::getToken("user_{$i}");
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 200 operations (100 set + 100 get) should complete quickly
        $this->assertLessThan(500, $duration, "Multiple token operations took {$duration}ms for 200 operations");
    }

    public function testCredentialStoragePerformance(): void
    {
        $startTime = microtime(true);

        for ($i = 0; $i < 100; $i++) {
            TokenManager::setCredentials(
                "user_{$i}",
                "pass_{$i}",
                "email_{$i}@test.com",
                userId: "cred_user_{$i}"
            );
            TokenManager::getCredentials("cred_user_{$i}");
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 200 credential operations should complete quickly
        $this->assertLessThan(1000, $duration, "Credential operations took {$duration}ms for 200 operations");
    }

    public function testLoginPerformanceWithMockedHttp(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $authService = new AuthService();

        $startTime = microtime(true);

        for ($i = 0; $i < 50; $i++) {
            $authService->setUserId("perf_user_{$i}");
            $authService->login('user', 'pass', 'email@test.com');
        }

        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000;

        // 50 login operations should complete in reasonable time
        $this->assertLessThan(2000, $duration, "Login operations took {$duration}ms for 50 operations");
    }

    public function testMemoryUsageDuringHighLoad(): void
    {
        $initialMemory = memory_get_usage(true);

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => array_fill(0, 100, ['id' => 1, 'data' => str_repeat('x', 100)]),
            ], 200),
        ]);

        $productService = new ProductService();

        for ($i = 0; $i < 100; $i++) {
            $productService->search("query_{$i}");
        }

        $peakMemory = memory_get_peak_usage(true);
        $memoryIncrease = ($peakMemory - $initialMemory) / 1024 / 1024; // In MB

        // Memory increase should be reasonable (less than 50MB for this test)
        $this->assertLessThan(50, $memoryIncrease, "Memory increased by {$memoryIncrease}MB");
    }
}
