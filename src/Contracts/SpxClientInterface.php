<?php

namespace Unav\SpxConnect\Contracts;

interface SpxClientInterface
{
    public function auth(): AuthServiceInterface;

    public function cache(): CacheManagerInterface;

    public function sunplusAccounts(): SunPlusAccountServiceInterface;

    public function sunplusDimension(): SunPlusDimensionServiceInterface;

    public function products(): ProductServiceInterface;

    public function clients(): ClientServiceInterface;

    public function educationLevels(): EducationLevelServiceInterface;

    public function journal(): JournalServiceInterface;

    public function cfdi(): CfdiServiceInterface;
}
