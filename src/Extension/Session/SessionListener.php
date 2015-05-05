<?php namespace Barryvdh\Form\Extension\Session;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Session\SessionManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SessionListener implements EventSubscriberInterface
{
    /**
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param  SessionManager  $sessionManager
     */
    public function __construct(SessionManager $sessionManager)
    {
        $this->session = $sessionManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSet',
        );
    }

    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $name = $form->getConfig()->getName();

        if ( ! $form->isRoot() && $parent = $form->getParent()){
            $dotted = $parent->getName() ? $parent->getName() . '.' . $name : $name;
            // Add input from the previous submit
            if ($name !== '_token' && $this->session->hasOldInput($dotted)) {
                $event->setData($this->session->getOldInput($dotted));
            }

            if ($this->session->has('errors')) {
                /** @var \Illuminate\Support\ViewErrorBag $errors */
                $errors = $this->session->get('errors');
                if ($errors->has($name)) {
                    $form->addError(new FormError(implode(' ', $errors->get($name))));
                }
            }
        }
    }
}
