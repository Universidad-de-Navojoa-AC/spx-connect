<?php

namespace Unav\SpxConnect;

use Unav\SpxConnect\Contracts\SpxClientInterface;

class SpxClient implements SpxClientInterface
{
    protected AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    public function authenticate(string $username, string $password, string $email): bool
    {
        return $this->auth->login($username, $password, $email);
    }
}