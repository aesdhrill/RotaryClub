<?php

namespace App\Form\Account;

use App\Entity\Address;
use App\Enum\Voivodeship;
use App\Form\BaseType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('streetName',TextType::class, [
                'label' => 'user.address.street',
                'required' => false,
            ])
            ->add('streetNumber',TextType::class, [
                'label' => 'user.address.street_number',
            ])
            ->add('apartmentNumber',TextType::class, [
                'label' => 'user.address.flat_number',
                'required' => false,
            ])
            ->add('city',TextType::class, [
                'label' => 'user.address.city',
            ])
            ->add('postalCode',TextType::class, [
                'label' => 'user.address.postal_code',
            ])
            ->add('voivodeship', ChoiceType::class,[
                'label' => 'user.address.voivodeship',
                'choices' => Voivodeship::getValuesTranslated(),
                'attr' => [
                    'class' => 'select2'
                ]
            ])
            ->add('country', TextType::class,[
                'label' => 'user.address.country'
            ])
            ->add('save', SubmitType::class,[
                'label' => 'common.form.save',
                'attr' => [
                    'class' => 'btn btn-primary text-center w-100 p-1'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}