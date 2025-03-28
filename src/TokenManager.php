<?php

namespace Unav\SpxConnect;

class TokenManager
{
    protected static ?string $accessToken = null;
    protected static ?array $credentials = null;

    public static function setToken(string $token): void
    {
        self::$accessToken = $token;
    }

    public static function getToken(): ?string
    {
        return self::$accessToken;
    }

    public static function hasToken(): bool
    {
        return self::$accessToken !== null;
    }

    public static function clearToken(): void
    {
        self::$accessToken = null;
    }

    public static function setCredentials(string $username, string $password, string $email): void
    {
        self::$credentials = compact('username', 'password', 'email');
    }

    public static function getCredentials(): ?array
    {
        return self::$credentials;
    }
}
