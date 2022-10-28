<?php

namespace App\Form\User;

use App\Entity\User;
use App\Enum\UserPPStatus;
use App\Enum\UserRole;
use App\Repository\FacilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;


class NewUserType extends AbstractType
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
//            ->add('userFacilities', ChoiceType::class, [
//                'label' => 'user.facility',
//                'required' => false,
//                'multiple' => true,
//                'choices' => $options['facilities'],
//                'choice_label' => 'name',
//                'choice_translation_domain' => false,
//                'placeholder' => $options['userFacilities'],
//                'mapped' => false
//            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.role',
                'required' => true,
                'multiple' => true,
                'choices' => UserRole::getRolesForUserRegistrationTranslated(),
                'placeholder' => false
            ])
            ->add('email', EmailType::class, [
                'label' => 'common.email',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'common.form.save'
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this,'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event){

        $form = $event->getForm();
        $data = $event->getData();

        dump($form, $data);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'facilities' => ArrayCollection::class,
            'userFacilities' => ArrayCollection::class
        ]);
    }
}