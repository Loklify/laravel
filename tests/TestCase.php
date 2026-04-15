<?php

namespace Loklify\Laravel\Tests;

use Loklify\Laravel\LoklifyServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LoklifyServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('loklify.url', 'https://api.loklify.test');
        $app['config']->set('loklify.project_id', 'test-project-uuid');
        $app['config']->set('loklify.token', 'test-token');
        $app['config']->set('loklify.cache_ttl', 0);
    }
}
