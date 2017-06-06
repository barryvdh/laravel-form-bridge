<?php namespace Barryvdh\Form\Extension\Session;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Session\SessionManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class SessionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSet',
        );
    }

    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $rootName = $form->getRoot()->getName();

        if (! $form->isRoot() && $parent = $form->getParent()) {
            $name = $this->getDottedName($form);
            $fullName = $this->getFullName($rootName, $name);

            // Add input from the previous submit
            if ($form->getName() !== '_token' && $value = old($fullName)) {
                // Transform back to good data
                $value = $this->transformValue($event, $value);

                // Store on the form
                if ($this->validData($form, $value)) {
                    $event->setData($value);
                }
            }

            if ($errors = session('errors')) {
                /** @var \Illuminate\Support\ViewErrorBag $errors */
                if ($errors->has($name)) {
                    $form->addError(new FormError(implode(' ', $errors->get($name))));
                }
            }
        }
    }

    protected function getDottedName(FormInterface $form)
    {
        $name = [$form->getName()];

        while ($form = $form->getParent()) {
            if ($form->getName() !== null && !$form->isRoot()) {
                array_unshift($name, $form->getName());
            }
        }

        return implode('.', $name);
    }

    protected function getFullName($rootName, $dottedName)
    {
        if ($rootName === '') {
            return $dottedName;
        }

        return $rootName . '.' . $dottedName;
    }

    /**
     * @param FormEvent $event
     * @param mixed $value
     * @return mixed
     */
    protected function transformValue(FormEvent $event, $value)
    {
        // Get all view transformers for this event
        $transformers = $event->getForm()->getConfig()->getViewTransformers();

        // Reverse them all..
        foreach ($transformers as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        return $value;
    }

    /**
     * Make sure the data should be set.
     *
     * @param FormInterface $form
     * @param mixed         $value
     * @return bool
     */
    protected function validData(FormInterface $form, $value)
    {
        // Make sure the form is not a collection type.
        $notACollectionType = ! ($form->getConfig()->getType()->getInnerType() instanceof CollectionType);

        // Value is not an array.
        $notAnArray = ! is_array($value);

        // Data class is not set.
        $dataClassIsNull = is_null($form->getConfig()->getDataClass());

        // If all these cases are true we set the data.
        return $notACollectionType && $notAnArray && $dataClassIsNull;
    }
}
