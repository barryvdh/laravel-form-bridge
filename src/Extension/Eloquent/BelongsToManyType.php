<?php namespace Barryvdh\Form\Extension\Eloquent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @deprecated Use a 'ChoiceType' with multiple=true, mapped=false
 */
class BelongsToManyType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->addModelTransformer(new BelongsToManyTransformer())
          ->addEventSubscriber(new BelongsToManyListener())
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
          'multiple' => true
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getName()
    {
        return 'belongs_to_many';
    }
}
