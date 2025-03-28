<?php

namespace Unav\SpxConnect\Contracts;

interface SpxClientInterface
{
    public function authenticate(string $username, string $password, string $email): bool;
}
