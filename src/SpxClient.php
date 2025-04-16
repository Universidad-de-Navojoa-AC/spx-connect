<?php

namespace Unav\SpxConnect;

use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;

/**
 * @property AuthService $auth
 */
class SpxClient implements SpxClientInterface
{
    protected array $services = [];

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    public function getService(string $key): mixed
    {
        return $this->services[$key] ?? null;
    }

    public function __get(string $name): mixed
    {
        return $this->getService($name);
    }

    public function authenticate(string $username, string $password, string $email): bool
    {
        return $this->auth->login($username, $password, $email);
    }
}