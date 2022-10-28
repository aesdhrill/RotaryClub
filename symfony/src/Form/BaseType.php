<?php

namespace App\Form;

    //use App\Entity\Patient\Trial\TrialVisitInterface;
//use App\Form\Type\ChoiceOrNoneType;
//use App\Form\Type\NumberOrNoneType;
//use App\Form\Type\TextOrNoneType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\Persistence\Proxy;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseType extends AbstractType
{
    protected array $requiredConditions = [];

    /*
     * Symfony 6.0+ deprecated Session component and so
     * FlashBagInterface was replaced with the RequestStack
     */
    public function __construct(
        protected TranslatorInterface $translator,
        protected RequestStack $requestStack,
        protected Security $security,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if (!$form->getConfig()->getOption('async')) {
            if (!$form->getParent() && $form->isSubmitted() && !$form->isValid()) {
//                TODO: check whether this change works
                $this->requestStack->getSession()->getFlashBag()->add('error',
                    $this->translator->trans('form.common.filled_incorrectly', domain: 'validators'));
            }
        }
        # TODO: maybe also success message here?
    }

    protected function addRequiredIfValidation(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetDataRequiredIf']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmitRequiredIf']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitRequiredIf']);
    }

    public function onPreSetDataRequiredIf(FormEvent $event): void
    {
        $form = $event->getForm();

        foreach ($form->all() as $child) {
            $childName = $child->getName();
            $childOptions = $child->getConfig()->getOptions();
            $childType = $child->getConfig()->getType()->getInnerType();

            if (array_key_exists('data-required-if', $childOptions['attr'])) {
                $requiredIfs = $childOptions['attr']['data-required-if'];
                $requiredIfsOriginal = [];

                # Add blue asterisk
                # TODO: maybe also detect keys with !!s
                if (array_key_exists('finished', $childOptions['attr']['data-required-if'])) {
                    $childOptions['label_attr']['class'] = ($childOptions['label_attr']['class'] ?? '') . ' required-finished';
                }

                foreach ($requiredIfs as $key => $value) {
                    # TODO: fix this huge hack with https://symfony.com/blog/new-in-symfony-6-1-customizable-collection-prototypes ??
                    # TODO: Implement this fix as we're working on 6.1
                    # if form is a prototype, get name from somewhere else
                    if ($form->getName() === '__name__') {
                        $formId = $form->getConfig()->getOption('attr')['prototype_name'];
                    } else {
                        $formId = $this->getFormId($form);
                    }

                    if (is_array($value)) {
                        $value = array_values($value);
                    }

                    $requiredIfsOriginal[$key] = $value;
                    unset($requiredIfs[$key]);

                    while (str_starts_with($key, '!')) {
                        $formId = preg_replace('/\|[a-zA-Z0-9_-]+$/', '', $formId);
                        $key = substr($key, 1);
                    }

                    $formId = preg_replace('/\|/', '_', $formId);

                    $requiredIfs[$formId . '_' . $key] = $value;
                }

                $childOptions['attr']['data-required-if'] = json_encode($requiredIfs, JSON_THROW_ON_ERROR);
                $childOptions['attr']['data-required-if-original'] = json_encode($requiredIfsOriginal, JSON_THROW_ON_ERROR);

                # Remove placeholder from ChoiceType
                if (is_a($childType, ChoiceType::class) || $childType->getParent() === ChoiceType::class) {
                    if ($childOptions['placeholder'] === 'None') {
                        $childOptions['placeholder'] = false;
                    }
                }

                $form->remove($childName);
                $form->add($childName, $child->getConfig()->getType()->getInnerType()::class, $childOptions);
            }
        }
    }

    public function onPreSubmitRequiredIf(FormEvent $event): void
    {
        $form = $event->getForm();

        foreach ($form->all() as $child) {
            $childAttrs = $child->getConfig()->getOption('attr');

            if (array_key_exists('data-required-if', $childAttrs)) {
                $requiredIfs = json_decode($childAttrs['data-required-if'], true);
                $requiredIfsOriginal = json_decode($childAttrs['data-required-if-original'], true);

                $this->requiredConditionsOriginal[$child->getName()] = $requiredIfsOriginal;

                foreach ($requiredIfs as $key => $value) {
                    $ids = explode('_', $key);
                    $requiredIfs[end($ids)] = $value;
                    unset($requiredIfs[$key]);
                }

                $this->requiredConditions[$child->getName()] = $requiredIfs;
            }
        }
    }

    public function onSubmitRequiredIf(FormEvent $event): void
    {
        $form = $event->getForm();

        foreach ($this->requiredConditions as $fieldName => $requiredCondition) {
            $requiredConditionOriginal = $this->requiredConditionsOriginal[$fieldName];
            $required = true;

            foreach ($requiredConditionOriginal as $otherFieldName => $value) {
                $checkedForm = clone $form;

                while (str_starts_with($otherFieldName, '!')) {
                    $checkedForm = $checkedForm->getParent();
                    $otherFieldName = substr($otherFieldName, 1);
                }

                if (count(array_intersect((array)$checkedForm->get($otherFieldName)->getData(), (array)$value)) === 0) {
                    $required = false;
                }
            }

//            TODO: restore if we decide to bring these types from LAMA
//            $excludedTypes = [ChoiceOrNoneType::class, TextOrNoneType::class, NumberOrNoneType::class];
            $excludedTypes = [];


            // Don't validate *OrNoneTypes
            $fieldConfig = $form->get($fieldName)->getConfig();
            $fieldType = $fieldConfig->getType()->getInnerType()::class;

            if ($required && !in_array($fieldType, $excludedTypes, true)) {
                if ($fieldType === ChoiceType::class && $fieldConfig->getOption('multiple') && empty($form->get($fieldName)->getData())) {
                    $form->get($fieldName)->addError(new FormError($this->translator->trans('common.field.required', domain: 'validators')));
                } elseif (is_null($form->get($fieldName)->getData())) {
                    $form->get($fieldName)->addError(new FormError($this->translator->trans('common.field.required', domain: 'validators')));
                }
            }
        }
    }

    public static function modifyOption(FormInterface $form, string $childName, string $optionName, $newValue): void
    {
        $child = $form->get($childName);
        $childOptions = $child->getConfig()->getOptions();

        $childOptions[$optionName] = $newValue;

        $form->remove($childName);
        $form->add($childName, $child->getConfig()->getType()->getInnerType()::class, $childOptions);
    }

    private function getFormId(FormInterface $form): string
    {
        if ($parent = $form->getParent()) {
            $id = $this->getFormId($parent) . '|' . $form->getName();
        } else {
            $id = $form->getName();
        }

        return $id;
    }

    # For this to work, children entities must have the attribute below:
    # #[Assert\Valid]
    protected function getValidationGroupsForTrial(FormInterface $form, string $field): array
    {
        /** @var TrialVisitInterface $data */
        $data = $form->get($field)->getData();

        if ($data instanceof Collection) {
            $data = $data->first();
        }

        $visitValidationGroups = $this->getValidationGroupsRecursive(
            $data instanceof Proxy
                ? get_parent_class($data)
                : get_class($data)
        );

        if (method_exists($data, 'isFinished') && $data->isFinished()) {
            return array_merge($visitValidationGroups, ['visit_finished']);
        }

        return $visitValidationGroups;
    }

    protected function getValidationGroupsRecursive($objectOrClass): array
    {
        $groups = [];
        $properties = (new \ReflectionClass($objectOrClass))->getProperties();

        foreach ($properties as $property) {
            if ($attributes = $property->getAttributes()) {
                $include = false;
                $propertyGroups = [];

                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === Valid::class) {
                        $include = !array_key_exists('groups', $attribute->getArguments());
                    }

                    if (in_array($attribute->getName(), [OneToMany::class, OneToOne::class], true)) { # TODO: more??
                        $targetEntity = $attribute->getArguments()['targetEntity'];

                        # TODO: check if works with children?
                        $propertyGroups = $this->getValidationGroupsRecursive($targetEntity);
                    }
                }

                if ($include) {
                    $groups = array_merge($groups, $propertyGroups);
                }
            }
        }

        return array_merge($groups, [(new \ReflectionClass($objectOrClass))->getShortName()]);
    }
}
