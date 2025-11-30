<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\BaseApiService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class BaseApiServiceTest extends TestCase
{
    protected BaseApiService $apiService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::clearCredentials();
        $this->apiService = new BaseApiService();
    }

    public function testRequestAddsAuthorizationToken(): void
    {
        TokenManager::setToken('test-bearer-token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/endpoint' => Http::response(['data' => 'test'], 200),
        ]);

        $this->apiService->request('get', 'endpoint');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-bearer-token');
        });
    }

    public function testGetRequestSendsGetMethod(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/test-endpoint' => Http::response(['response' => 'ok'], 200),
        ]);

        $response = $this->apiService->get('test-endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['response' => 'ok'], $response->json());
    }

    public function testRequestWithQueryParameters(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response(['response' => 'ok'], 200),
        ]);

        $this->apiService->get('search', ['query' => 'test', 'page' => 1]);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'query=test')
                && str_contains($request->url(), 'page=1');
        });
    }

    public function testRequestWithCustomHeaders(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response(['response' => 'ok'], 200),
        ]);

        $this->apiService->request('get', 'endpoint', [], ['X-Custom-Header' => 'custom-value']);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('X-Custom-Header', 'custom-value');
        });
    }

    public function testRequestRefreshesTokenOn401(): void
    {
        $userId = 'global';
        TokenManager::setToken('expired-token', userId: $userId);
        TokenManager::setCredentials('user', 'pass', 'email@test.com', userId: $userId);

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'new-refreshed-token',
            ], 200),
            'api.sunplusxtra.mx/api/spxtra/endpoint' => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['data' => 'success'], 200),
        ]);

        $response = $this->apiService->request('get', 'endpoint');

        $this->assertEquals(200, $response->status());
        $this->assertEquals(['data' => 'success'], $response->json());
    }

    public function testSetUserIdChangesTokenContext(): void
    {
        TokenManager::setToken('user-1-token', userId: 'user-1');
        TokenManager::setToken('user-2-token', userId: 'user-2');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([], 200),
        ]);

        $this->apiService->setUserId('user-1');
        $this->apiService->get('test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer user-1-token');
        });
    }

    public function testSetUserIdReturnsServiceInstance(): void
    {
        $result = $this->apiService->setUserId('some-user');

        $this->assertInstanceOf(BaseApiService::class, $result);
    }

    public function testSetUserIdWithDefaultGlobal(): void
    {
        TokenManager::setToken('global-token', userId: 'global');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([], 200),
        ]);

        // setUserId with 'global' uses the default global context
        $this->apiService->setUserId('global');
        $this->apiService->get('test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer global-token');
        });
    }

    public function testRequestHandlesNullToken(): void
    {
        // No token set
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response(['data' => 'test'], 200),
        ]);

        $response = $this->apiService->get('endpoint');

        // Should still make the request even without token
        $this->assertEquals(200, $response->status());
    }
}
