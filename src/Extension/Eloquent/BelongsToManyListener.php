<?php namespace Barryvdh\Form\Extension\Eloquent;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BelongsToManyListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
          FormEvents::PRE_SUBMIT => 'preSubmit',
        );
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        if ($parent = $form->getParent()) {
            $parent->remove($form->getName());
        }
    }
}
