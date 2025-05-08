<?php

namespace Unav\SpxConnect\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface CacheManagerInterface
{
    public function get(string $key, ?Authenticatable $user = null): mixed;

    public function put(string $key, $value, ?Authenticatable $user = null, int $ttl = 3600): void;

    public function forget(string $key, ?Authenticatable $user = null): void;
}