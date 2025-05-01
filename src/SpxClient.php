<?php

namespace Unav\SpxConnect;

use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

/**
 * @property AuthService $auth
 * @property SunPlusAccountService $sunplusAccounts
 * @property SunPlusDimensionService $sunplusDimension
 * @property ProductService $products
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

    public function auth(): AuthService
    {
        return $this->auth;
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

    public function educationLevels(): EducationLevelService
    {
        return $this->educationLevels();
    }
}