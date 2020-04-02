<?php namespace Barryvdh\Form\Extension\Session;

use Illuminate\Session\SessionManager;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractTypeExtension;

class CsrfTypeExtension extends AbstractTypeExtension
{

    /**
     * Adds a CSRF field to the root form view.
     *
     * @param FormView      $view    The form view
     * @param FormInterface $form    The form
     * @param array         $options The options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();
            $csrfForm = $factory->createNamed('_token', HiddenType::class, csrf_token(), array(
                'mapped' => false,
            ));
            $view->children['_token'] = $csrfForm->createView(null);
        }
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
