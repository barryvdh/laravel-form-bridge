<?php namespace Barryvdh\Form\Extension\Eloquent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BelongsToManyType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->addModelTransformer(new BelongsToManyTransformer())
          ->addEventSubscriber(new BelongsToManyListener())
        ;
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'belongs_to_many';
    }
}
