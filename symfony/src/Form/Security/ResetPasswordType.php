<?php

namespace App\Form\Security;

use App\Form\BaseType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ResetPasswordType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options'  => ['label' => 'common.password'],
                'second_options' => ['label' => 'common.repeat_password'],
                'options' => ['attr' => ['class' => 'password-field']],
                'invalid_message' => 'form.passwords_must_match',
                'constraints' => [
                    new Length(min: 8),
                    new NotCompromisedPassword(),
                ],
            ])
            ->add('recaptcha', EWZRecaptchaV3Type::class, [
                'constraints' => [
                    new IsTrueV3(),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'common.form.save'
            ]);
    }
}
