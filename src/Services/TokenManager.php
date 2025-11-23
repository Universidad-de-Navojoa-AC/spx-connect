<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Unav\SpxConnect\Contracts\TokenManagerInterface;

class TokenManager implements TokenManagerInterface
{
    protected static string $tokenKeyPrefix = 'spx_access_token_';
    protected static string $credentialPrefix = 'spx_credentials_';

    public static function setToken(string $token, ?int $ttl = 3600, ?string $userId = null): void
    {
        $encrypted = Crypt::encryptString($token);
        $key = self::tokenKey($userId);

        Cache::put($key, $encrypted, $ttl);
    }

    public static function getToken(?string $userId = null): ?string
    {
        $key = self::tokenKey($userId);
        $encrypted = Cache::get($key);

        if (!$encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function hasToken(?string $userId = null): bool
    {
        return self::getToken($userId) !== null;
    }

    public static function clearToken(?string $userId = null): void
    {
        Cache::forget(self::tokenKey($userId));
    }

    public static function setCredentials(string $username, string $password, string $email, ?int $ttl = 3600, ?string $userId = null): void
    {
        $data = compact('username', 'password', 'email');
        $encrypted = Crypt::encryptString(json_encode($data));
        $key = self::credentialKey($userId);

        Cache::put($key, $encrypted, $ttl);
    }

    public static function getCredentials(?string $userId = null): ?array
    {
        $key = self::credentialKey($userId);
        $encrypted = Cache::get($key);

        if (!$encrypted) {
            return null;
        }

        try {
            $json = Crypt::decryptString($encrypted);
            return json_decode($json, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function clearCredentials(?string $userId = null): void
    {
        Cache::forget(self::credentialKey($userId));
    }

    protected static function tokenKey(?string $userId): string
    {
        $suffix = $userId !== null ? (string) $userId : 'global';
        return self::$tokenKeyPrefix . $suffix;
    }

    protected static function credentialKey(?string $userId): string
    {
        $suffix = $userId !== null ? $userId : 'global';
        return self::$credentialPrefix . $suffix;
    }
}
