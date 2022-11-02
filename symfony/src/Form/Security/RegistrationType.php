<?php

namespace App\Form\Security;

use App\Form\BaseType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', EmailType::class,[
            'label' => 'common.email'
        ])
        ->add('password', RepeatedType::class,[
            'type' => PasswordType::class,
            'invalid_message' => 'errors.password.mismatch',
            'options' => ['attr' => ['class' => 'password-field']],
            'required' => true,
            'first_options'  => ['label' => 'form.signup.password'],
            'second_options' => ['label' => 'form.signup.password_repeat'],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'validation_groups' => ['signup'],
        ]);
    }
}