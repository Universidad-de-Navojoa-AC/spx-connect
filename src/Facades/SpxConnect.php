<?php

namespace Unav\SpxConnect\Facades;

use Illuminate\Support\Facades\Facade;

class SpxConnect extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Unav\SpxConnect\Contracts\SpxClientInterface::class;
    }
}
