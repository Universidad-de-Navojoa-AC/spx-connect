<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\SunPlusAccountService;

/**
 * Interface SpxClientInterface
 *
 * @property AuthService $auth
 */
interface SpxClientInterface
{
    public function getService(string $key): mixed;

    public function auth(): AuthService;

    public function sunplusAccounts(): SunPlusAccountService;
}
