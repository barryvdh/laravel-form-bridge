<?php namespace Barryvdh\Form\Extension\Type;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Session\SessionManager;
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
        // High priority in order to supersede other listeners
        return array(FormEvents::PRE_SET_DATA => array('preBind', 128));
    }

    public function preBind(FormEvent $event)
    {
        $form = $event->getForm();
        $name = $form->getConfig()->getName();

        if ($this->session->hasOldInput($name)) {
            $event->setData($this->session->getOldInput($name));
        }

    }
}
