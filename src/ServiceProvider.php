<?php namespace Barryvdh\Form;

use Symfony\Component\Form\Forms;
use Barryvdh\Form\Twig\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Barryvdh\Form\Extension\EloquentExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\ResolvedFormTypeFactory;
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

        $this->app->bind('form.types', function () {
            return array();
        });

        $this->app->bind('form.type.extensions', function () {
            return array();
        });

        $this->app->bind('form.type.guessers', function () {
            return array();
        });

        $this->app->bind('form.extensions', function ($app) {
            return array(
                $app->make('Barryvdh\Form\Extension\SessionExtension'),
                new HttpFoundationExtension(),
                new EloquentExtension(),
            );
        });

        $this->app->bind('form.factory', function($app) {
            return Forms::createFormFactoryBuilder()
                ->addExtensions($app['form.extensions'])
                ->addTypes($app['form.types'])
                ->addTypeExtensions($app['form.type.extensions'])
                ->addTypeGuessers($app['form.type.guessers'])
                ->setResolvedTypeFactory($app['form.resolved_type_factory'])
                ->getFormFactory();
        });
        $this->app->alias('form.factory', 'Symfony\Component\Form\FormFactoryInterface');

        $this->app->bind('form.resolved_type_factory', function () {
            return new ResolvedFormTypeFactory();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array(
            'Symfony\Component\Form\FormFactoryInterface',
            'form.factory',
            'twig.form.engine',
            'twig.form.renderer',
            'form.resolved_type_factory',
            'form.types',
            'form.type.extensions',
            'form.type.guessers',
            'form.extensions',
        );
    }
}
