<?php

namespace Unav\SpxConnect\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Unav\SpxConnect\Services\TokenManager;
use Unav\SpxConnect\Tests\TestCase;

class TokenManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function testSetAndGetToken(): void
    {
        $token = 'test-token-123';
        $userId = 'user-1';

        TokenManager::setToken($token, userId: $userId);

        $retrievedToken = TokenManager::getToken($userId);
        $this->assertEquals($token, $retrievedToken);
    }

    public function testSetAndGetTokenWithGlobalUser(): void
    {
        $token = 'global-token-456';

        TokenManager::setToken($token);

        $retrievedToken = TokenManager::getToken();
        $this->assertEquals($token, $retrievedToken);
    }

    public function testHasTokenReturnsTrue(): void
    {
        $userId = 'user-has-token';
        TokenManager::setToken('some-token', userId: $userId);

        $this->assertTrue(TokenManager::hasToken($userId));
    }

    public function testHasTokenReturnsFalseWhenNoToken(): void
    {
        $this->assertFalse(TokenManager::hasToken('non-existent-user'));
    }

    public function testClearToken(): void
    {
        $userId = 'user-clear';
        TokenManager::setToken('token-to-clear', userId: $userId);

        $this->assertTrue(TokenManager::hasToken($userId));

        TokenManager::clearToken($userId);

        $this->assertFalse(TokenManager::hasToken($userId));
    }

    public function testSetAndGetCredentials(): void
    {
        $userId = 'user-creds';
        $username = 'testuser';
        $password = 'testpass';
        $email = 'test@example.com';

        TokenManager::setCredentials($username, $password, $email, userId: $userId);

        $credentials = TokenManager::getCredentials($userId);

        $this->assertIsArray($credentials);
        $this->assertEquals($username, $credentials['username']);
        $this->assertEquals($password, $credentials['password']);
        $this->assertEquals($email, $credentials['email']);
    }

    public function testGetCredentialsReturnsNullWhenNotSet(): void
    {
        $this->assertNull(TokenManager::getCredentials('no-creds-user'));
    }

    public function testClearCredentials(): void
    {
        $userId = 'user-clear-creds';
        TokenManager::setCredentials('user', 'pass', 'email@test.com', userId: $userId);

        $this->assertNotNull(TokenManager::getCredentials($userId));

        TokenManager::clearCredentials($userId);

        $this->assertNull(TokenManager::getCredentials($userId));
    }

    public function testTokenExpiresWithTtl(): void
    {
        // Mock Cache to simulate TTL behavior
        Cache::shouldReceive('put')
            ->once()
            ->withArgs(function ($key, $value, $ttl) {
                return $ttl === 60;
            });
        Cache::shouldReceive('get')
            ->once()
            ->andReturn(null);

        TokenManager::setToken('expiring-token', ttl: 60, userId: 'ttl-user');
        $this->assertNull(TokenManager::getToken('ttl-user'));
    }

    public function testMultipleUsersHaveSeparateTokens(): void
    {
        $user1 = 'user-a';
        $user2 = 'user-b';
        $token1 = 'token-for-a';
        $token2 = 'token-for-b';

        TokenManager::setToken($token1, userId: $user1);
        TokenManager::setToken($token2, userId: $user2);

        $this->assertEquals($token1, TokenManager::getToken($user1));
        $this->assertEquals($token2, TokenManager::getToken($user2));
    }

    public function testTokenIsEncrypted(): void
    {
        $token = 'plain-token';
        $userId = 'encrypt-user';

        TokenManager::setToken($token, userId: $userId);

        // Retrieve the raw cached value
        $cacheKey = 'spx_access_token_' . $userId;
        $encryptedValue = Cache::get($cacheKey);

        // The stored value should be different from the plain token
        $this->assertNotEquals($token, $encryptedValue);

        // But decrypting it should give us the original token
        $decrypted = Crypt::decryptString($encryptedValue);
        $this->assertEquals($token, $decrypted);
    }

    public function testCredentialsAreEncrypted(): void
    {
        $userId = 'encrypt-creds-user';
        $username = 'encrypteduser';
        $password = 'encryptedpass';
        $email = 'encrypted@test.com';

        TokenManager::setCredentials($username, $password, $email, userId: $userId);

        // Retrieve the raw cached value
        $cacheKey = 'spx_credentials_' . $userId;
        $encryptedValue = Cache::get($cacheKey);

        // The stored value should be encrypted
        $decrypted = json_decode(Crypt::decryptString($encryptedValue), true);
        $this->assertEquals($username, $decrypted['username']);
        $this->assertEquals($password, $decrypted['password']);
        $this->assertEquals($email, $decrypted['email']);
    }
}
