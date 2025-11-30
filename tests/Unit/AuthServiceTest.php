<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class AuthServiceTest extends TestCase
{
    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        TokenManager::clearToken();
        TokenManager::clearCredentials();
        $this->authService = new AuthService();
    }

    public function testLoginSuccess(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'fake-token-123',
            ], 200),
        ]);

        $result = $this->authService->login('username', 'password', 'test@example.com');

        $this->assertTrue($result);
        $this->assertEquals('fake-token-123', TokenManager::getToken('global'));
    }

    public function testLoginSendsCorrectPayload(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'test-token',
            ], 200),
        ]);

        $this->authService->login('testuser', 'testpass', 'test@email.com');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.sunplusxtra.mx/api/spxtra/login'
                && $request['idusuario'] === 'testuser'
                && $request['password'] === 'testpass'
                && $request['email'] === 'test@email.com';
        });
    }

    public function testLoginFailsWithNoToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'message' => 'Unauthorized',
            ], 401),
        ]);

        $result = $this->authService->login('wrong', 'credentials', 'bad@email.com');

        $this->assertFalse($result);
        $this->assertNull(TokenManager::getToken('global'));
    }

    public function testLoginFailsWithEmptyToken(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => null,
            ], 200),
        ]);

        $result = $this->authService->login('user', 'pass', 'email@test.com');

        $this->assertFalse($result);
    }

    public function testHasValidTokenReturnsTrue(): void
    {
        TokenManager::setToken('valid-token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/company' => Http::response(['company' => 'Test'], 200),
        ]);

        $result = $this->authService->hasValidToken();

        $this->assertTrue($result);
    }

    public function testHasValidTokenReturnsFalseFor220Status(): void
    {
        TokenManager::setToken('invalid-token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/company' => Http::response(['error' => 'Invalid token'], 220),
        ]);

        $result = $this->authService->hasValidToken();

        $this->assertFalse($result);
    }

    public function testHasValidTokenReturnsFalseFor401Status(): void
    {
        TokenManager::setToken('expired-token');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/company' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $result = $this->authService->hasValidToken();

        $this->assertFalse($result);
    }

    public function testRefreshAccessTokenSuccess(): void
    {
        TokenManager::setCredentials('user', 'pass', 'email@test.com');

        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'new-token',
            ], 200),
        ]);

        $result = $this->authService->refreshAccessToken();

        $this->assertTrue($result);
        $this->assertEquals('new-token', TokenManager::getToken('global'));
    }

    public function testRefreshAccessTokenFailsWithoutCredentials(): void
    {
        $result = $this->authService->refreshAccessToken();

        $this->assertFalse($result);
    }

    public function testSetUserId(): void
    {
        $authService = $this->authService->setUserId('custom-user-id');

        $this->assertInstanceOf(AuthService::class, $authService);
    }

    public function testLoginStoresCredentials(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'token',
            ], 200),
        ]);

        $this->authService->login('storeduser', 'storedpass', 'stored@email.com');

        $credentials = TokenManager::getCredentials('global');
        $this->assertEquals('storeduser', $credentials['username']);
        $this->assertEquals('storedpass', $credentials['password']);
        $this->assertEquals('stored@email.com', $credentials['email']);
    }

    public function testLoginWithDifferentUserIds(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'user-specific-token',
            ], 200),
        ]);

        $this->authService->setUserId('user-123');
        $result = $this->authService->login('user', 'pass', 'email@test.com');

        $this->assertTrue($result);
        $this->assertEquals('user-specific-token', TokenManager::getToken('user-123'));
    }
}
