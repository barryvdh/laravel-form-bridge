<?php namespace Barryvdh\Form;

use Barryvdh\Form\Extension\FormDefaultsTypeExtension;
use Barryvdh\Form\Extension\Validation\ValidationTypeExtension;
use Barryvdh\Form\Facade\FormRenderer as FormRendererFacade;
use Illuminate\Support\Facades\Blade;
use Barryvdh\Form\Extension\SessionExtension;
use Illuminate\View\View;
use Symfony\Bridge\Twig\Form\TwigRendererEngineInterface;
use Symfony\Bridge\Twig\Form\TwigRendererInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\Forms;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Barryvdh\Form\Extension\EloquentExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Barryvdh\Form\Extension\FormValidatorExtension;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;

class ServiceProvider extends BaseServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        $configPath = __DIR__ . '/../config/form.php';
        $this->publishes([$configPath => config_path('form.php')], 'config');

        if ($this->app->bound(\Twig_Environment::class)) {
            /** @var \Twig_Environment $twig */
            $twig = $this->app->make(\Twig_Environment::class);
        } else {
            $twig = new \Twig_Environment(new \Twig_Loader_Chain([]));
        }

        $loader = $twig->getLoader();

        // If the loader is not already a chain, make it one
        if (! $loader instanceof \Twig_Loader_Chain) {
            $loader = new \Twig_Loader_Chain([$loader]);
            $twig->setLoader($loader);
        }

        $reflected = new \ReflectionClass(FormExtension::class);
        $path = dirname($reflected->getFileName()).'/../Resources/views/Form';
        $loader->addLoader(new \Twig_Loader_Filesystem($path));

        /** @var TwigRenderer $renderer */
        $renderer = $this->app->make(TwigRenderer::class);
        $renderer->setEnvironment($twig);


        $twig->addRuntimeLoader(new \Twig_FactoryRuntimeLoader(array(
            TwigRenderer::class => function () {
                return $this->app->make(TwigRenderer::class);
            }
        )));

        // Add the extension
        $twig->addExtension(new FormExtension());

        // trans filter is used in the forms
        $twig->addFilter(new \Twig_SimpleFilter('trans', 'trans'));
        // csrf_token needs to be replaced for Laravel
        $twig->addFunction(new \Twig_SimpleFunction('csrf_token', 'csrf_token'));

        $this->registerBladeDirectives();
        $this->registerViewComposer();
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

        $this->app->singleton(TwigRendererEngine::class, function ($app) {
            $theme = (array) $app['config']->get('form.theme', 'bootstrap_3_layout.html.twig');
            return new TwigRendererEngine($theme);
        });

        $this->app->singleton(TwigRenderer::class, function ($app) {
            $renderer = $app->make(TwigRendererEngine::class);
            return new TwigRenderer($renderer);
        });
        $this->app->alias(TwigRenderer::class, FormRendererInterface::class);

        $this->app->bind('form.type.extensions', function ($app) {
            return array(
                new FormDefaultsTypeExtension($app['config']->get('form.defaults', [])),
                new ValidationTypeExtension($app['validator']),
            );
        });
        $this->app->bind('form.type.guessers', function ($app) {
            return array();
        });


        $this->app->bind('form.extensions', function ($app) {
            return array(
                $app->make(SessionExtension::class),
                new HttpFoundationExtension(),
                new EloquentExtension(),
                new FormValidatorExtension(),
            );
        });

        $this->app->bind('form.resolved_type_factory', function () {
            return new ResolvedFormTypeFactory();
        });

        $this->app->singleton(FormFactory::class, function($app) {
            return Forms::createFormFactoryBuilder()
                ->addExtensions($app['form.extensions'])
                ->addTypeExtensions($app['form.type.extensions'])
                ->addTypeGuessers($app['form.type.guessers'])
                ->setResolvedTypeFactory($app['form.resolved_type_factory'])
                ->getFormFactory();
        });
        $this->app->alias(FormFactory::class, 'form.factory');
        $this->app->alias(FormFactory::class, FormFactoryInterface::class);
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('form', function ($expression) {
            return '<?php echo \\' . FormRendererFacade::class .'::form('.$expression.'); ?>';
        });

        foreach (['start', 'end', 'widget', 'errors', 'label', 'row', 'rest'] as $method) {
            Blade::directive('form_' . $method, function ($expression) use($method) {
                return '<?php echo \\' . FormRendererFacade::class .'::'.$method.'('.$expression.'); ?>';
            });
        }
    }

    protected function registerViewComposer()
    {
        $this->app['view']->composer('*', function ($view) {
            if ($view instanceof View) {
                foreach ($view->getData() as $key => $value) {
                    if ($value instanceof Form) {
                        $view->with($key, $value->createView());
                    }
                }
            }
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
            FormFactoryInterface::class,
            TwigRendererEngine::class,
            TwigRendererEngineInterface::class,
            TwigRenderer::class,
            TwigRendererInterface::class,
            FormRendererInterface::class,
            FormFactoryInterface::class,
            'form.factory',
            'form.extensions',
        );
    }
}
