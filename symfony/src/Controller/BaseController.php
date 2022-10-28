<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailSender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseController extends AbstractController
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected MailSender $mailer,
        protected FormFactoryInterface $formFactory,
    ) {}

    /**
     * @return User|null
     */
    protected function getUser(): ?UserInterface
    {
        return parent::getUser();
    }

    protected function createFormNamedBuilder(string $name, $data = null, array $options = []): FormBuilderInterface
    {
        return $this->container->get('form.factory')->createNamedBuilder($name, FormType::class, $data, $options);
    }

    protected function createNamedForm(string $formName, string $type, $data = null, array $options = []): FormInterface
    {
        return $this->formFactory->createNamed($formName, $type, $data, $options);
    }
}
