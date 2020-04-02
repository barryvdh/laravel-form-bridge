<?php namespace Barryvdh\Form\Extension\Validation;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValidationTypeExtension extends AbstractTypeExtension
{
    /** @var ValidationFactory  */
    protected $validator;

    public function __construct(ValidationFactory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('rules'));
        $resolver->setDefault('rules', array());

        $hasNullable = version_compare(app()->version(), '5.3.0', '>=');

        // Split rule into array
        $rulesNormalizer = function (Options $options, $constraints) use ($resolver, $hasNullable) {

            if (is_string($constraints)) {
                $rules = explode('|', $constraints);
            } elseif (is_object($constraints)) {
                $rules = [$constraints];
            } else {
                $rules = $constraints;
            }

            // If the required option is set for the Field, add it to the rules
            if ($options['required'] && !in_array('required', $rules)) {
                $rules[] = 'required';
            }

            if ($hasNullable) {
                if (!in_array('required', $rules) && !in_array('nullable', $rules)) {
                    $rules[] = 'nullable';
                }
            }

            return $rules;
        };

        $resolver->setNormalizer('rules', $rulesNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ValidationListener($this->validator));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (! $form->isRoot() && isset($options['rules'])) {
            $rules = $options['rules'];

            $innerType = $form->getConfig()->getType()->getInnerType();
            if (($innerType instanceof NumberType || $innerType instanceof IntegerType)
                && !in_array('numeric', $rules)
            ) {
                $rules[] = 'numeric';
            }

            $ruleParser = new RulesParser($form, $view, $rules);
            $attr = $ruleParser->getAttributes();

            // Set form attributes based on rules
            $view->vars['required'] = in_array('required', $attr);
            $view->vars['attr'] += $attr;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
