<?php

namespace Unav\SpxConnect;

use Unav\SpxConnect\Contracts\AuthServiceInterface;
use Unav\SpxConnect\Contracts\CacheManagerInterface;
use Unav\SpxConnect\Contracts\CfdiServiceInterface;
use Unav\SpxConnect\Contracts\ClientServiceInterface;
use Unav\SpxConnect\Contracts\EducationLevelServiceInterface;
use Unav\SpxConnect\Contracts\JournalServiceInterface;
use Unav\SpxConnect\Contracts\ProductServiceInterface;
use Unav\SpxConnect\Contracts\SpxClientInterface;
use Unav\SpxConnect\Contracts\SunPlusAccountServiceInterface;
use Unav\SpxConnect\Contracts\SunPlusDimensionServiceInterface;

/**
 * Main client for SPX Connect SDK
 *
 * Provides access to all SPX Connect services through a unified interface.
 * Services are injected via constructor and can be accessed through method calls.
 *
 * @see SpxClientInterface For method signatures
 */
class SpxClient implements SpxClientInterface
{
    public function __construct(
        protected AuthServiceInterface             $auth,
        protected CacheManagerInterface            $cache,
        protected SunPlusAccountServiceInterface   $sunplusAccounts,
        protected SunPlusDimensionServiceInterface $sunplusDimension,
        protected ProductServiceInterface          $products,
        protected ClientServiceInterface           $clients,
        protected EducationLevelServiceInterface   $educationLevels,
        protected JournalServiceInterface          $journal,
        protected CfdiServiceInterface             $cfdi,
    )
    {
    }

    public function auth(): AuthServiceInterface
    {
        return $this->auth;
    }

    public function cache(): CacheManagerInterface
    {
        return $this->cache;
    }

    public function sunplusAccounts(): SunPlusAccountServiceInterface
    {
        return $this->sunplusAccounts;
    }

    public function sunplusDimension(): SunPlusDimensionServiceInterface
    {
        return $this->sunplusDimension;
    }

    public function products(): ProductServiceInterface
    {
        return $this->products;
    }

    public function clients(): ClientServiceInterface
    {
        return $this->clients;
    }

    public function educationLevels(): EducationLevelServiceInterface
    {
        return $this->educationLevels;
    }

    public function journal(): JournalServiceInterface
    {
        return $this->journal;
    }

    public function cfdi(): CfdiServiceInterface
    {
        return $this->cfdi;
    }
}