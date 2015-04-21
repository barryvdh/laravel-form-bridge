<?php namespace Barryvdh\Form\Extension\Type;

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
        $compound = $form->getConfig()->getCompound();

        // Add input from the previous submit
        if ( ! $compound && $name !== '_token' && $this->session->hasOldInput($name)) {
            $event->setData($this->session->getOldInput($name));
        }

        // Check if the session has any errors
        if ($compound &&  $this->session->has('errors')) {
            /** @var \Illuminate\Support\ViewErrorBag $errors */
            $errors = $this->session->get('errors');

            foreach ($errors->getMessages() as $name => $messages) {
                // When the form doesn't have the field, add a global error
                $field = $form->has($name) ? $form->get($name) : $form;
                $field->addError(new FormError(
                    implode(' ', $messages)
                ));
            }
        }
    }
}
