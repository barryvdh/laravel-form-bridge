<?php namespace Barryvdh\Form\Extension\Validation;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

class ValidationListener implements EventSubscriberInterface
{
    protected $validator;

    protected $data;

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
                FormEvents::PRE_SUBMIT => 'gatherData',
                FormEvents::POST_SUBMIT => 'validateRules',
            );
    }

    /**
     * Get the original data, before submitting
     *
     * @param FormEvent $event
     */
    public function gatherData(FormEvent $event)
    {
        $this->data = $event->getData();
    }

    /**
     * Find all rules, apply them to the original data and add errors.
     *
     * @param FormEvent $event
     */
    public function validateRules(FormEvent $event)
    {
        if ($event->getForm()->isRoot()) {
            $root = $event->getForm();
            $rules = $this->findRules($root);
            $validator = $this->validator->make($this->data ?: [], $rules);

            if ($validator->fails()) {
                // Add all messages to the original name
                foreach ($validator->getMessageBag()->toArray() as $name => $messages) {
                    foreach ($messages as $message) {
                        $form = $this->getByDotted($root, $name);
                        $form->addError(new FormError($message));
                    }
                }
            }
        }
    }

    /**
     * Recursively find all rules.
     *
     * @param FormInterface $parent
     * @param array $rules
     * @return array
     */
    protected function findRules(FormInterface $parent, $rules = [], $parentName = null)
    {
        foreach ($parent->all() as $form) {
            $config = $form->getConfig();
            $name = $form->getName();
            $innerType = $form->getConfig()->getType()->getInnerType();

            if ($config->hasOption('rules')) {
                if ($parentName !== null) {
                    $name = $parentName . '.' . $name;
                } elseif (! $parent->isRoot()) {
                    $name = $parent->getName() . '.' . $name;
                }

                $rules[$name] = $this->addTypeRules($innerType, $config->getOption('rules'));
            }

            if ($innerType instanceof CollectionType) {
                $children = $form->all();
                if (isset($children[0])) {
                    $rules = $this->findRules($children[0], $rules, $name . '.*');
                }
            }
        }

        return $rules;
    }

    /**
     * Recursively get the form using the dotted name.
     *
     * @param FormInterface $form
     * @param $name
     * @return FormInterface
     */
    protected function getByDotted(FormInterface $form, $name)
    {
        $parts = explode('.', $name);

        while ($name = array_shift($parts)) {
            $form = $form->get($name);
        }

        return $form;
    }

    /**
     * Add default rules based on the type
     *
     * @param FormTypeInterface $type
     * @param array $rules
     * @return array
     */
    protected function addTypeRules(FormTypeInterface $type, array $rules)
    {
        if (($type instanceof NumberType || $type instanceof IntegerType)
            && !in_array('numeric', $rules)
        ) {
            $rules[] = 'numeric';
        }

        if (($type instanceof EmailType)
            && !in_array('email', $rules)
        ) {
            $rules[] = 'email';
        }

        if (($type instanceof TextType || $type instanceof TextareaType)
            && !in_array('string', $rules)
        ) {
            $rules[] = 'string';
        }

        return $rules;
    }
}
