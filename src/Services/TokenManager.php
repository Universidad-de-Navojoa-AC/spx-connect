<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class TokenManager
{
    protected static string $tokenKeyPrefix = 'spx_access_token_';
    protected static string $credentialPrefix = 'spx_credentials_';

    public static function setToken(string $token, ?int $ttl = 3600, ?int $userId = null): void
    {
        $encrypted = Crypt::encryptString($token);
        $key = self::tokenKey($userId);

        Cache::put($key, $encrypted, $ttl);
    }

    public static function getToken(?int $userId = null): ?string
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

    public static function hasToken(?int $userId = null): bool
    {
        return self::getToken($userId) !== null;
    }

    public static function clearToken(?int $userId = null): void
    {
        Cache::forget(self::tokenKey($userId));
    }

    public static function setCredentials(string $username, string $password, string $email, ?int $ttl = 3600, ?int $userId = null): void
    {
        $data = compact('username', 'password', 'email');
        $encrypted = Crypt::encryptString(json_encode($data));
        $key = self::tokenKey($userId);

        Cache::put($key, $encrypted, $ttl);
    }

    public static function getCredentials(?int $userId = null): ?array
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

    public static function clearCredentials(?int $userId = null): void
    {
        Cache::forget(self::credentialKey($userId));
    }

    protected static function tokenKey(?int $userId): string
    {
        $suffix = $userId !== null ? (string) $userId : 'global';
        return self::$tokenKeyPrefix . $suffix;
    }

    protected static function credentialKey(?int $userId): string
    {
        $suffix = $userId !== null ? (string) $userId : 'global';
        return self::$credentialPrefix . $suffix;
    }
}
