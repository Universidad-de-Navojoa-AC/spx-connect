<?php

namespace Unav\SpxConnect\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Unav\SpxConnect\SpxConnectServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SpxConnectServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'SpxConnect' => \Unav\SpxConnect\Facades\SpxConnect::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('cache.default', 'array');
        // Generate a valid 32-byte key for AES-256-CBC
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('app.cipher', 'AES-256-CBC');
    }
}
