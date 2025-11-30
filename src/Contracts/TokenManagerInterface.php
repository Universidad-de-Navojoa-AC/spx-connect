<?php

namespace Unav\SpxConnect\Contracts;

interface TokenManagerInterface
{
    public static function setToken(string $token, ?int $ttl = 3600, ?string $userId = null): void;

    public static function getToken(?string $userId = null): ?string;

    public static function hasToken(?string $userId = null): bool;

    public static function clearToken(?string $userId = null): void;

    public static function setCredentials(string $username, string $password, string $email, ?int $ttl = 3600, ?string $userId = null): void;

    public static function getCredentials(?string $userId = null): ?array;

    public static function clearCredentials(?string $userId = null): void;
}
