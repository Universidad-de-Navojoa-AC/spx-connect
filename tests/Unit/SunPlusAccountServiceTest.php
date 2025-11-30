<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class SunPlusAccountServiceTest extends TestCase
{
    protected SunPlusAccountService $accountService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::setToken('test-token');
        $this->accountService = new SunPlusAccountService();
    }

    public function testGetAllReturnsAccounts(): void
    {
        $expectedAccounts = [
            ['code' => '1001', 'name' => 'Caja'],
            ['code' => '1002', 'name' => 'Bancos'],
            ['code' => '2001', 'name' => 'Proveedores'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list' => Http::response([
                'response' => $expectedAccounts,
            ], 200),
        ]);

        $result = $this->accountService->getAll();

        $this->assertEquals($expectedAccounts, $result);
    }

    public function testGetAllCallsCorrectEndpoint(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->accountService->getAll();

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.sunplusxtra.mx/api/spxtra/account-list';
        });
    }

    public function testGetAllReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->accountService->getAll();

        $this->assertEquals([], $result);
    }

    public function testFindByCodeReturnsMatchingAccounts(): void
    {
        $expectedAccounts = [
            ['code' => '1001', 'name' => 'Caja'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => $expectedAccounts,
            ], 200),
        ]);

        $result = $this->accountService->findByCode('1001');

        $this->assertEquals($expectedAccounts, $result);
    }

    public function testFindByCodeSendsCorrectParameters(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->accountService->findByCode('2001');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'part=2001')
                && str_contains($request->url(), 'code=1');
        });
    }

    public function testFindByCodeReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'error' => 'Account not found',
            ], 404),
        ]);

        $result = $this->accountService->findByCode('9999');

        $this->assertEquals([], $result);
    }

    public function testSearchReturnsMatchingAccounts(): void
    {
        $expectedAccounts = [
            ['code' => '1001', 'name' => 'Caja General'],
            ['code' => '1002', 'name' => 'Caja Chica'],
        ];

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => $expectedAccounts,
            ], 200),
        ]);

        $result = $this->accountService->search('Caja');

        $this->assertEquals($expectedAccounts, $result);
    }

    public function testSearchSendsCorrectParameters(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->accountService->search('Bancos');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'part=Bancos')
                && str_contains($request->url(), 'code=0');
        });
    }

    public function testSearchReturnsEmptyArrayOnError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        $result = $this->accountService->search('NonExistent');

        $this->assertEquals([], $result);
    }

    public function testGetAllReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->accountService->getAll();

        $this->assertEquals([], $result);
    }

    public function testFindByCodeReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->accountService->findByCode('1001');

        $this->assertEquals([], $result);
    }

    public function testSearchReturnsEmptyArrayOnConnectionError(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $result = $this->accountService->search('Test');

        $this->assertEquals([], $result);
    }

    public function testAllMethodsIncludeAuthToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->accountService->getAll();
        $this->accountService->findByCode('1001');
        $this->accountService->search('test');

        Http::assertSentCount(3);
        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-token');
        });
    }

    public function testSetUserIdChangesContext(): void
    {
        TokenManager::setToken('user-token', userId: 'user-abc');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/account-list*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $this->accountService->setUserId('user-abc');
        $this->accountService->getAll();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer user-token');
        });
    }
}
