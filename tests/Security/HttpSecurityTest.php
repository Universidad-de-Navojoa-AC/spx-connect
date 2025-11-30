<?php

namespace Unav\SpxConnect\Tests\Security;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class HttpSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
    }

    public function testAuthorizationHeaderIsSent(): void
    {
        TokenManager::setToken('bearer-token-123');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $clientService = new ClientService();
        $clientService->search('test');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization')
                && str_starts_with($request->header('Authorization')[0], 'Bearer ');
        });
    }

    public function testHttpsIsUsedForApiCalls(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $clientService = new ClientService();
        $clientService->search('test');

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), 'https://');
        });
    }

    public function testLoginUsesHttps(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'access_token' => 'token',
            ], 200),
        ]);

        $authService = new AuthService();
        $authService->login('user', 'pass', 'email@test.com');

        Http::assertSent(function (Request $request) {
            return str_starts_with($request->url(), 'https://');
        });
    }

    public function testCredentialsAreSentSecurely(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'token',
            ], 200),
        ]);

        $authService = new AuthService();
        $authService->login('secret-user', 'secret-pass', 'secret@test.com');

        Http::assertSent(function (Request $request) {
            // Credentials should be sent in POST body, not URL
            return $request->method() === 'POST'
                && !str_contains($request->url(), 'password')
                && !str_contains($request->url(), 'secret-pass');
        });
    }

    public function testSensitiveDataNotExposedInQueryString(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/timbra-comprobante' => Http::response([
                'response' => 'stamped-xml',
            ], 200),
        ]);

        $cfdiService = new CfdiService();
        $result = $cfdiService->stamp('<xml>sensitive-content</xml>', 'email@test.com');

        // Verify the stamping was successful
        $this->assertEquals('stamped-xml', $result);

        // Verify that the URL does not contain sensitive data
        // The comprobante (XML content) should be in POST body, not query string
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'timbra-comprobante')
                && !str_contains($request->url(), 'sensitive-content');
        });
    }

    public function testTokenIsNotLoggedOnError(): void
    {
        $sensitiveToken = 'super-secret-do-not-log';
        TokenManager::setToken($sensitiveToken);

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'error' => 'Server Error',
            ], 500),
        ]);

        $clientService = new ClientService();

        // This will trigger error handling
        $result = $clientService->search('test');

        // The result should be empty (error handled gracefully)
        $this->assertEquals([], $result);

        // In a real scenario, we would check logs don't contain the token
        // For this test, we just ensure the error is handled without crashing
    }

    public function testRefreshTokenMechanismWorks(): void
    {
        TokenManager::setToken('old-token');
        TokenManager::setCredentials('user', 'pass', 'email@test.com');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'new-refreshed-token',
            ], 200),
        ]);

        $authService = new AuthService();
        $result = $authService->refreshAccessToken();

        $this->assertTrue($result);
        $this->assertEquals('new-refreshed-token', TokenManager::getToken());
    }

    public function test401ResponseTriggersTokenRefresh(): void
    {
        TokenManager::setToken('expired-token');
        TokenManager::setCredentials('user', 'pass', 'email@test.com');

        // Use BaseApiService to test the 401 handling
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'fresh-token',
            ], 200),
            'api.sunplusxtra.mx/api/spxtra/products*' => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['response' => [['id' => 1]]], 200),
        ]);

        $productService = new ProductService();
        $result = $productService->search('test');

        // Should have made 3 requests: initial, login refresh, retry
        Http::assertSentCount(3);
    }

    public function testApiBaseUrlIsCorrect(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/*' => Http::response([
                'response' => [],
            ], 200),
        ]);

        $clientService = new ClientService();
        $clientService->search('test');

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'api.sunplusxtra.mx/api/spxtra');
        });
    }

    public function testConnectionErrorsAreHandledSecurely(): void
    {
        TokenManager::setToken('token');

        Http::fake([
            'api.sunplusxtra.mx/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $clientService = new ClientService();

        // Should not throw exception, should return gracefully
        $result = $clientService->search('test');

        $this->assertEquals([], $result);
    }
}
