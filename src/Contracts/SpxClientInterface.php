<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Services\AuthService;

/**
 * Interface SpxClientInterface
 *
 * @property AuthService $auth
 */
interface SpxClientInterface
{
    public function authenticate(string $username, string $password, string $email): bool;

    public function getService(string $key): mixed;
}
