<?php namespace Barryvdh\Form\Extension\Validation;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

class ValidationListener implements EventSubscriberInterface
{
    protected $validator;

    public function __construct(ValidationFactory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
                FormEvents::POST_SUBMIT => 'validateRules',
            );
    }

    public function validateRules(FormEvent $event)
    {
        $form = $event->getForm();
        $config = $form->getConfig();

        if ( ! $form->isRoot() && $config->hasOption('rules') ) {
            $data = [
                $form->getName() => $form->getData(),
            ];
            $rules = [
                $form->getName() => $config->getOption('rules'),
            ];

            $validator = $this->validator->make($data, $rules);
            if ($validator->fails()) {
                foreach ($validator->messages()->all() as $message) {
                    $form->addError(new FormError($message));
                }
            }
        }
    }
}
