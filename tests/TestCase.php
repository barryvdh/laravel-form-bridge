<?php
namespace Barryvdh\Form\Tests;

use Barryvdh\Form\ServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

abstract class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware('Illuminate\Session\Middleware\StartSession');

        $app['config']->set('app.debug', true);
        $app['view']->addNamespace('forms', __DIR__ .'/views');
    }
}
