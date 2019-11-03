<?php namespace Barryvdh\Form\Extension\Session;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
        ];
    }

    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $rootName = $form->getRoot()->getName();
        $parent = $form->getParent();

        if ($parent
            && $form->getName() !== '_token'
            && !($parent->getConfig()->getType()->getInnerType() instanceof ChoiceType)
        ) {
            $name = $this->getDottedName($form);
            $fullName = $this->getFullName($rootName, $name);

            // Get the input from the previous submit
            $oldValue = session()->getOldInput($fullName);
            if (!is_null($oldValue)) {
                // Transform back to good data
                try {
                    $value = $this->transformValue($event, $oldValue);

                    // Store on the form
                    $event->setData($value);
                } catch (TransformationFailedException $e) {
                    // Cannot transform the data
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
     *
     * @return mixed
     */
    protected function transformValue(FormEvent $event, $value)
    {
        // Get all view transformers for this event
        $config = $event->getForm()->getConfig();

        // For Models, skip the transformation, that is done on children
        $dataClass = $config->getDataClass();
        if ($dataClass && is_array($value) && is_a($config->getDataClass(), Model::class, true)) {
            return new $dataClass;
        }

        // If array is given, check if it needs to be a Collection
        if (is_array($value) && $event->getData() instanceof Collection) {
            $value = $event->getData()->make($value);
        }

        // Reverse them all..
        foreach ($config->getViewTransformers() as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        // Map the models to correct values
        foreach ($config->getModelTransformers() as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        return $value;
    }
}
