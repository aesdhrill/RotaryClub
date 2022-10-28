<?php

namespace App\Form\Security;

use App\Form\BaseType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ChangePasswordType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'common.current_password',
                'constraints' => [
                    new UserPassword(),
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'common.new_password'],
                'second_options' => ['label' => 'common.repeat_new_password'],
                'options' => ['attr' => ['class' => 'password-field']],
                'invalid_message' => 'form.passwords_must_match',
                'constraints' => [
                    new Length(min: 8),
                    new NotCompromisedPassword(),
                    # TODO: maybe more complex requirements
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'common.form.save'
            ]);
    }
}
