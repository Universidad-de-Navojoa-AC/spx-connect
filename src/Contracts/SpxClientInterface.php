<?php

namespace Unav\SpxConnect\Contracts;

use Unav\SpxConnect\Services\AuthService;
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
    public function getService(string $key): mixed;

    public function auth(): AuthService;

    public function cache(): CacheManagerInterface;

    public function sunplusAccounts(): SunPlusAccountService;

    public function sunplusDimension(): SunPlusDimensionService;

    public function products(): ProductService;

    public function educationLevels(): EducationLevelService;
}
