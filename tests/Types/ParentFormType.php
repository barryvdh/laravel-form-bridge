<?php
namespace Barryvdh\Form\Tests\Types;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ParentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('children', CollectionType::class, [
                'entry_type' => UserFormType::class,
                'allow_add' => true,
            ])
            ->add('emails', CollectionType::class, [
                'entry_type' => EmailType::class,
                'allow_add' => true,
                'rules' => ['min:1'],
                'entry_options' => [
                    'rules' => ['distinct'],
                ],
            ])
        ;
    }
}
