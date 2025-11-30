<?php

namespace Unav\SpxConnect\Facades;

use Illuminate\Support\Facades\Facade;
use Unav\SpxConnect\Contracts\AuthServiceInterface;
use Unav\SpxConnect\Contracts\CacheManagerInterface;
use Unav\SpxConnect\Contracts\CfdiServiceInterface;
use Unav\SpxConnect\Contracts\ClientServiceInterface;
use Unav\SpxConnect\Contracts\EducationLevelServiceInterface;
use Unav\SpxConnect\Contracts\JournalServiceInterface;
use Unav\SpxConnect\Contracts\ProductServiceInterface;
use Unav\SpxConnect\Contracts\SunPlusAccountServiceInterface;
use Unav\SpxConnect\Contracts\SunPlusDimensionServiceInterface;

/**
 * Facade for SpxClient
 *
 * @method static AuthServiceInterface auth()
 * @method static CacheManagerInterface cache()
 * @method static SunPlusAccountServiceInterface sunplusAccounts()
 * @method static SunPlusDimensionServiceInterface sunplusDimension()
 * @method static ProductServiceInterface products()
 * @method static ClientServiceInterface clients()
 * @method static EducationLevelServiceInterface educationLevels()
 * @method static JournalServiceInterface journal()
 * @method static CfdiServiceInterface cfdi()
 */
class SpxConnect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'spxconnect';
    }
}
