<?php

namespace Unav\SpxConnect\Contracts;

interface AuthServiceInterface
{
    public function login(string $username, string $password, string $email): bool;

    public function hasValidToken(): bool;

    public function refreshAccessToken(): bool;

    public function setUserId(?string $userId = 'global'): self;
}
