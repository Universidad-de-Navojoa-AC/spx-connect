<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

/**
 * Interface SpxClientInterface
 *
 * @property AuthService $auth
 */
interface SpxClientInterface
{
    public function auth(): AuthService;

    public function cache(): CacheManager;

    public function sunplusAccounts(): SunPlusAccountService;

    public function sunplusDimension(): SunPlusDimensionService;

    public function products(): ProductService;

    public function clients(): ClientService;

    public function educationLevels(): EducationLevelService;
}
