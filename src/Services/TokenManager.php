<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class TokenManager
{
    protected static string $tokenKeyPrefix = 'spx_access_token_';
    protected static string $credentialPrefix = 'spx_credentials_';

    public static function setToken(string $token, int $ttl = 3600): void
    {
        $encrypted = Crypt::encryptString($token);
        $userId = Auth::id();

        Cache::put(self::$tokenKeyPrefix . $userId, $encrypted, $ttl);
    }

    public static function getToken(): ?string
    {
        $userId = Auth::id();
        $encrypted = Cache::get(self::$tokenKeyPrefix . $userId);

        if (!$encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function hasToken(): bool
    {
        $userId = Auth::id();

        return Cache::has(self::$tokenKeyPrefix . $userId);
    }

    public static function clearToken(): void
    {
        $userId = Auth::id();

        Cache::forget(self::$tokenKeyPrefix . $userId);
    }

    public static function setCredentials(string $username, string $password, string $email, int $ttl = 3600): void
    {
        $data = compact('username', 'password', 'email');
        $encrypted = Crypt::encryptString(json_encode($data));
        $userId = Auth::id();

        Cache::put(self::$credentialPrefix . $userId, $encrypted, $ttl);
    }

    public static function getCredentials(): ?array
    {
        $userId = Auth::id();
        $encrypted = Cache::get(self::$credentialPrefix . $userId);

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

    public static function clearCredentials(): void
    {
        $userId = Auth::id();

        Cache::forget(self::$credentialPrefix . $userId);
    }
}
