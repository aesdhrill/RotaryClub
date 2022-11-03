<?php

namespace App\Form\Security;

use App\Form\BaseType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResendActivationEmailType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'label' => 'form.signup.activate.email',
                'mapped' => false,
                'data' => $options['email']
            ])
            ->add('recaptcha', EWZRecaptchaV3Type::class, [
                'constraints' => [
                    new IsTrueV3(),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'form.signup.activate.submit'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'email' => null,
        ]);
    }
}