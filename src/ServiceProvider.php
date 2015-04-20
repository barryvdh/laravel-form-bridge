<?php namespace Barryvdh\Form;

use Symfony\Component\Form\Forms;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    public function boot()
    {
        $configPath = __DIR__ . '/../config/form.php';
        $this->publishes([$configPath => config_path('form.php')], 'config');

        $viewPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($viewPath, 'form');
        $this->publishes([
            $viewPath => base_path('resources/views/vendor/form'),
        ], 'views');

        $this->app['twig']->addExtension(new FormExtension($this->app['twig.form.renderer']));
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $configPath = __DIR__ . '/../config/form.php';
        $this->mergeConfigFrom($configPath, 'form');

        $this->app->bind('twig.form.engine', function ($app) {
            $theme = $app['config']->get('form.theme', 'bootstrap_3_layout');
            return new TwigRendererEngine(array('form::'.$theme));
        });

        $this->app->bind('twig.form.renderer', function ($app) {
            return new TwigRenderer($app['twig.form.engine']);
        });


        $this->app->bind('form.factory' ,function ($app) {
            $csrfExtension = $app->make('Barryvdh\Form\Extension\SessionExtension');

            return Forms::createFormFactoryBuilder()
                ->addExtension($csrfExtension)
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        });
	}


	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('form.factory', 'twig.form.engine', 'twig.form.renderer');
	}

}
