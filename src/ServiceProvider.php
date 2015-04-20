<?php namespace Barryvdh\Form;

use Symfony\Component\Form\Forms;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

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

        $this->app->bind('form.factory' ,function ($app) {
            $twig = $app['twig'];
            $theme = $app['config']->get('form.theme', 'bootstrap_3_layout');
            
            $formEngine = new TwigRendererEngine(array('form::'.theme));
            $formEngine->setEnvironment($twig);
            $twig->addExtension(new FormExtension(new TwigRenderer($formEngine)));

            return Forms::createFormFactoryBuilder()
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
		return array('form.factory');
	}

}
