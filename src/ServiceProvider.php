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

        // Add the Form templates to the Twig Chain Loader
        $reflected = new \ReflectionClass('Symfony\Bridge\Twig\Extension\FormExtension');
        $path = dirname($reflected->getFileName()).'/../Resources/views/Form';
        $this->app['twig.loader']->addLoader(new \Twig_Loader_Filesystem($path));

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
            $theme = (array) $app['config']->get('form.theme', 'bootstrap_3_layout.html.twig');
            return new TwigRendererEngine($theme);
        });

        $this->app->bind('twig.form.renderer', function ($app) {
            return new TwigRenderer($app['twig.form.engine']);
        });

        $this->app->bind('form.factory', function($app) {
            $csrfExtension = $app->make('Barryvdh\Form\Extension\SessionExtension');

            return Forms::createFormFactoryBuilder()
                ->addExtension($csrfExtension)
                ->addExtension(new HttpFoundationExtension())
                ->getFormFactory();
        });
        $this->app->alias('form.factory', 'Symfony\Component\Form\FormFactoryInterface');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('Symfony\Component\Form\FormFactoryInterface', 'form.factory', 'twig.form.engine', 'twig.form.renderer');
    }
}
