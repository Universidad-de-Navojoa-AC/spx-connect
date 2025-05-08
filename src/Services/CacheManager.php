<?php

namespace Unav\SpxConnect\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Auth\Authenticatable;
use Unav\SpxConnect\Contracts\CacheManagerInterface;

class CacheManager implements CacheManagerInterface
{
    protected string $prefix = 'spxconnect_';

    public function get(string $key, ?Authenticatable $user = null): mixed
    {
        if ($user === null) {
            return Cache::get($key);
        }

        return Cache::get($this->buildKey($key, $user));
    }

    public function put(string $key, $value, ?Authenticatable $user = null, int $ttl = 3600): void
    {
        if ($user === null) {
            Cache::put($key, $value, $ttl);
            return;
        }

        Cache::put($this->buildKey($key, $user), $value, $ttl);
    }

    public function forget(string $key, ?Authenticatable $user = null): void
    {
        if ($user === null) {
            Cache::forget($key);
            return;
        }

        Cache::forget($this->buildKey($key, $user));
    }

    protected function buildKey(string $key, ?Authenticatable $user = null): string
    {
        if ($user === null) {
            return $this->prefix . $key;
        }

        return $this->prefix . $user->getAuthIdentifier() . '_' . $key;
    }
}