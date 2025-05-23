<?php

namespace Unav\SpxConnect\Facades;

use Illuminate\Support\Facades\Facade;
use Unav\SpxConnect\Contracts\CacheManagerInterface;
use Unav\SpxConnect\Services\AuthService;
use Unav\SpxConnect\Services\EducationLevelService;
use Unav\SpxConnect\Services\ProductService;
use Unav\SpxConnect\Services\SunPlusAccountService;
use Unav\SpxConnect\Services\SunPlusDimensionService;

/**
 * Facade for SpxClient
 *
 * @method static AuthService auth()
 * @method static CacheManagerInterface cache()
 * @method static SunPlusAccountService sunplusAccounts()
 * @method static SunPlusDimensionService sunplusDimension()
 * @method static ProductService products()
 * @method static EducationLevelService educationLevels()
 */
class SpxConnect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'spxconnect';
    }
}
