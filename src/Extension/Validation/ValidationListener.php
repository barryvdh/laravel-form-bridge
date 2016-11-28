<?php namespace Barryvdh\Form\Extension\Validation;

use Illuminate\Validation\Rules\In;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\Form\FormTypeInterface;

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

            $rules = $config->getOption('rules');

            $innerType = $form->getConfig()->getType()->getInnerType();
            $rules = $this->addTypeRules($innerType, $rules);

            $data = [
                $form->getName() => $form->getData(),
            ];
            $rules = [
                $form->getName() => $rules,
            ];

            $validator = $this->validator->make($data, $rules);
            if ($validator->fails()) {
                foreach ($validator->messages()->all() as $message) {
                    $form->addError(new FormError($message));
                }
            }
        }
    }

    protected function addTypeRules(FormTypeInterface $type, array $rules)
    {
        if (
            ($type instanceof NumberType || $type instanceof IntegerType)
            && !in_array('numeric', $rules)
        ) {
            $rules[] = 'numeric';
        }

        if (
            ($type instanceof EmailType)
            && !in_array('email', $rules)
        ) {
            $rules[] = 'email';
        }

        if (
            ($type instanceof TextType || $type instanceof TextareaType)
            && !in_array('string', $rules)
        ) {
            $rules[] = 'string';
        }

        return $rules;
    }
}
