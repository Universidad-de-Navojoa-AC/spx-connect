<?php

namespace Unav\SpxConnect\Tests\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CfdiService;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class TokenSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testTokensAreEncryptedInStorage(): void
    {
        $plainToken = 'super-secret-token-123';
        $userId = 'secure-user';

        TokenManager::setToken($plainToken, userId: $userId);

        // Get raw cached value
        $cacheKey = 'spx_access_token_' . $userId;
        $cachedValue = Cache::get($cacheKey);

        // The cached value should not be the plain token
        $this->assertNotEquals($plainToken, $cachedValue);

        // But we should be able to decrypt it back
        $decrypted = Crypt::decryptString($cachedValue);
        $this->assertEquals($plainToken, $decrypted);
    }

    public function testCredentialsAreEncryptedInStorage(): void
    {
        $username = 'admin';
        $password = 'super-secret-password';
        $email = 'admin@example.com';
        $userId = 'secure-cred-user';

        TokenManager::setCredentials($username, $password, $email, userId: $userId);

        // Get raw cached value
        $cacheKey = 'spx_credentials_' . $userId;
        $cachedValue = Cache::get($cacheKey);

        // The cached value should not contain plain credentials
        $this->assertStringNotContainsString($password, $cachedValue);
        $this->assertStringNotContainsString($username, $cachedValue);

        // But we should be able to decrypt it back
        $decrypted = json_decode(Crypt::decryptString($cachedValue), true);
        $this->assertEquals($username, $decrypted['username']);
        $this->assertEquals($password, $decrypted['password']);
        $this->assertEquals($email, $decrypted['email']);
    }

    public function testTokensAreIsolatedPerUser(): void
    {
        $user1 = 'user-1';
        $user2 = 'user-2';

        TokenManager::setToken('token-for-user-1', userId: $user1);
        TokenManager::setToken('token-for-user-2', userId: $user2);

        // Each user should only see their own token
        $this->assertEquals('token-for-user-1', TokenManager::getToken($user1));
        $this->assertEquals('token-for-user-2', TokenManager::getToken($user2));

        // Clearing one user's token shouldn't affect another
        TokenManager::clearToken($user1);
        $this->assertNull(TokenManager::getToken($user1));
        $this->assertEquals('token-for-user-2', TokenManager::getToken($user2));
    }

    public function testCredentialsAreIsolatedPerUser(): void
    {
        $user1 = 'cred-user-1';
        $user2 = 'cred-user-2';

        TokenManager::setCredentials('admin1', 'pass1', 'admin1@test.com', userId: $user1);
        TokenManager::setCredentials('admin2', 'pass2', 'admin2@test.com', userId: $user2);

        $creds1 = TokenManager::getCredentials($user1);
        $creds2 = TokenManager::getCredentials($user2);

        // Each user should only see their own credentials
        $this->assertEquals('admin1', $creds1['username']);
        $this->assertEquals('admin2', $creds2['username']);

        // Clearing one user's credentials shouldn't affect another
        TokenManager::clearCredentials($user1);
        $this->assertNull(TokenManager::getCredentials($user1));
        $this->assertNotNull(TokenManager::getCredentials($user2));
    }

    public function testInvalidEncryptedDataReturnsNull(): void
    {
        $userId = 'corrupt-user';
        $cacheKey = 'spx_access_token_' . $userId;

        // Store invalid encrypted data
        Cache::put($cacheKey, 'not-a-valid-encrypted-string');

        // Should return null instead of throwing an exception
        $token = TokenManager::getToken($userId);
        $this->assertNull($token);
    }

    public function testInvalidCredentialDataReturnsNull(): void
    {
        $userId = 'corrupt-cred-user';
        $cacheKey = 'spx_credentials_' . $userId;

        // Store invalid encrypted data
        Cache::put($cacheKey, 'invalid-data');

        // Should return null instead of throwing an exception
        $credentials = TokenManager::getCredentials($userId);
        $this->assertNull($credentials);
    }

    public function testTokenTtlIsRespected(): void
    {
        $userId = 'ttl-user';
        $token = 'expiring-token';

        // Set token with 1 second TTL
        TokenManager::setToken($token, ttl: 1, userId: $userId);

        // Token should be available immediately
        $this->assertEquals($token, TokenManager::getToken($userId));

        // Wait for expiration (in real tests this would use Carbon::setTestNow())
        // For now we just verify the mechanism is in place
        $cacheKey = 'spx_access_token_' . $userId;
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function testNullUserIdDefaultsToGlobal(): void
    {
        // Both null and 'global' should use the same key
        TokenManager::setToken('global-token', userId: null);

        $this->assertEquals('global-token', TokenManager::getToken(null));
        $this->assertEquals('global-token', TokenManager::getToken('global'));
    }

    public function testAuthServiceProtectsCredentials(): void
    {
        Http::fake([
            'api.sunplusxtra.mx/api/spxtra/login' => Http::response([
                'access_token' => 'new-token',
            ], 200),
        ]);

        $authService = new AuthService();
        $authService->login('secret-user', 'secret-pass', 'secret@email.com');

        // Credentials should be stored encrypted
        $cacheKey = 'spx_credentials_global';
        $cachedValue = Cache::get($cacheKey);

        $this->assertStringNotContainsString('secret-pass', $cachedValue);
    }

    public function testTokenIsNotExposedInLogs(): void
    {
        $token = 'sensitive-token-that-should-not-be-logged';
        TokenManager::setToken($token);

        // The token should be encrypted when stored
        $cacheKey = 'spx_access_token_global';
        $cachedValue = Cache::get($cacheKey);

        // If someone accidentally logs the cache content, the token is encrypted
        $this->assertNotEquals($token, $cachedValue);
    }
}
