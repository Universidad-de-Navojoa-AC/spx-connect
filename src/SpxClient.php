<?php

namespace Unav\SpxConnect;

use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\CacheManager;
use Unav\SpxConnect\Services\ClientService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

/**
 * Facade para acceder a SpxClient
 *
 * @method static AuthService auth()
 * @method static CacheManager cache()
 * @method static SunPlusAccountService sunplusAccounts()
 * @method static SunPlusDimensionService sunplusDimension()
 * @method static ProductService products()
 * @method static ClientService clients()
 * @method static EducationLevelService educationLevels()
 */
class SpxClient implements SpxClientInterface
{
    public function __construct(
        protected AuthService $auth,
        protected CacheManager $cache,
        protected SunPlusAccountService $sunplusAccounts,
        protected SunPlusDimensionService $sunplusDimension,
        protected ProductService $products,
        protected ClientService $clients,
        protected EducationLevelService $educationLevels,
    ) {}

    public function auth(): AuthService
    {
        return $this->auth;
    }

    public function cache(): CacheManager
    {
        return $this->cache;
    }

    public function sunplusAccounts(): SunPlusAccountService
    {
        return $this->sunplusAccounts;
    }

    public function sunplusDimension(): SunPlusDimensionService
    {
        return $this->sunplusDimension;
    }

    public function products(): ProductService
    {
        return $this->products;
    }

    public function clients(): ClientService
    {
        return $this->clients;
    }

    public function educationLevels(): EducationLevelService
    {
        return $this->educationLevels;
    }
}