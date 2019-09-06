<?php namespace Barryvdh\Form;

use Barryvdh\Form\Extension\FormDefaultsTypeExtension;
use Barryvdh\Form\Extension\Http\HttpExtension;
use Barryvdh\Form\Extension\Validation\ValidationTypeExtension;
use Barryvdh\Form\Facade\FormRenderer as FormRendererFacade;
use Illuminate\Support\Facades\Blade;
use Barryvdh\Form\Extension\SessionExtension;
use Illuminate\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\Forms;
use Barryvdh\Form\Extension\EloquentExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Form\TwigRendererEngineInterface;
use Barryvdh\Form\Extension\FormValidatorExtension;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Symfony\Bridge\Twig\Extension\FormExtension;

class ServiceProvider extends BaseServiceProvider
{
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


        $twig = $this->getTwigEnvironment();
        $loader = $twig->getLoader();

        // If the loader is not already a chain, make it one
        if (! $loader instanceof \Twig_Loader_Chain) {
            $loader = new \Twig_Loader_Chain([$loader]);
            $twig->setLoader($loader);
        }

        $loader->addLoader(new \Twig_Loader_Filesystem($this->getTemplateDirectories()));

        $twig->addRuntimeLoader(new \Twig_FactoryRuntimeLoader(array(
            \Symfony\Component\Form\FormRenderer::class => function () {
                return $this->app->make(\Symfony\Component\Form\FormRenderer::class);
            }
        )));

        // Add the extension
        $twig->addExtension(new FormExtension());

        // trans filter is used in the forms
        $twig->addFilter(new \Twig_SimpleFilter('trans', function ($id = null, $replace = [], $locale = null) {
            if (empty($id)) {
                return '';
            }
            return app('translator')->get($id, $replace, $locale);
        }));
        
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
            return new TwigRendererEngine($theme, $this->getTwigEnvironment());
        });

        $this->app->singleton(\Symfony\Component\Form\FormRenderer::class, function ($app) {
            $renderer = $app->make(TwigRendererEngine::class);
            return new \Symfony\Component\Form\FormRenderer($renderer);
        });

        $this->app->alias(\Symfony\Component\Form\FormRenderer::class, FormRendererInterface::class);

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
                new SessionExtension(),
                new HttpExtension(),
                new EloquentExtension(),
                new FormValidatorExtension(),
            );
        });

        $this->app->bind('form.resolved_type_factory', function () {
            return new ResolvedFormTypeFactory();
        });

        $this->app->singleton(FormFactory::class, function ($app) {
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
            return sprintf(
                '<?php echo \\%s::form(%s); ?>',
                FormRendererFacade::class,
                trim($expression, '()')
            );
        });

        foreach (['start', 'end', 'widget', 'errors', 'label', 'row', 'rest'] as $method) {
            $callable = function ($expression) use ($method) {
                return sprintf(
                    '<?php echo \\%s::%s(%s); ?>',
                    FormRendererFacade::class,
                    $method,
                    trim($expression, '()')
                );
            };
            Blade::directive('form_' . $method, $callable);
            Blade::directive('form' . ucfirst($method), $callable);
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
            \Symfony\Component\Form\FormRenderer::class,
            \Symfony\Component\Form\FormRendererInterface::class,
            FormRendererInterface::class,
            FormFactoryInterface::class,
            'form.factory',
            'form.extensions',
        );
    }

    /**
     * Get directories to lookup for form themes
     *
     * @return string[]
     */
    protected function getTemplateDirectories()
    {
        $reflected = new \ReflectionClass(FormExtension::class);
        $path = dirname($reflected->getFileName()) . '/../Resources/views/Form';
        $dirs = (array)$this->app['config']->get('form.template_directories', []);
        $dirs = array_merge([$path], $dirs);
        return $dirs;
    }


    /**
     * Get or create a new TwigEnvironment
     *
     * @return \Twig_Environment
     */
    protected function getTwigEnvironment()
    {
        if (! $this->app->bound(\Twig_Environment::class)) {
            $this->app->singleton(\Twig_Environment::class, function () {
                return new \Twig_Environment(new \Twig_Loader_Chain([]), [
                    'cache' => storage_path('framework/views/twig'),
                ]);
            });
        }

        /** @var \Twig_Environment $twig */
        return $this->app->make(\Twig_Environment::class);
    }
}
