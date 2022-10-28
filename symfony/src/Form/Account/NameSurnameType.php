<?php

namespace App\Form\Account;

use App\Entity\User;
use App\Form\BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NameSurnameType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'user.name',
                'required' => true
            ])
            ->add('surname', TextType::class, [
                'label' => 'user.surname',
                'required' => true
            ])
            ->add('save', SubmitType::class, [
                'label' => 'common.form.save'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}