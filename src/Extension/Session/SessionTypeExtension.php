<?php namespace Barryvdh\Form\Extension\Session;

use Illuminate\Session\SessionManager;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;

class SessionTypeExtension extends AbstractTypeExtension
{
    /**
     * Constructor.
     *
     * @param  SessionManager  $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->listener = new SessionListener($sessionManager);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->listener);
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
}
