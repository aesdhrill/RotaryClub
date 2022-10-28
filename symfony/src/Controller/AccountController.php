<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Account\NameSurnameType;
//use App\Form\Account\PhoneType;
use App\Form\Security\ChangePasswordType;
use App\Manager\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/account', name: 'account_')]
#[IsGranted('ROLE_USER')]
class AccountController extends BaseController
{
    #[Route(path: '/settings', name: 'settings')]
    public function settings(Request $request, UserManager $userManager, UserPasswordHasherInterface $passwordHasher): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $newPassword = $passwordHasher->hashPassword(
                $user, $passwordForm->get('password')->getData()
            );

            $user->setPassword($newPassword);
            $userManager->save($user);

            $this->addFlash('success', $this->translator->trans('account.settings.flash.password_changed_log_in'));

            return $this->redirectToRoute('app_logout');
        }


        $nameSurnameForm = $this->createForm(NameSurnameType::class, $user);
        $nameSurnameForm->handleRequest($request);

        if ($nameSurnameForm->isSubmitted() && $nameSurnameForm->isValid()) {
            $userManager->save($nameSurnameForm->getData());

            $this->addFlash('success', $this->translator->trans('account.settings.flash.data_changed'));

            return $this->redirectToRoute('account_settings');
        }

//        $phoneForm = $this->createForm(PhoneType::class, $user);
//        $phoneForm->handleRequest($request);
//
//        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
//            $userManager->save($phoneForm->getData());
//
//            $this->addFlash('success', $this->translator->trans('account.settings.flash.data_changed'));
//
//            return $this->redirectToRoute('account_settings');
//        }

        return $this->render('system/account/settings.html.twig', [
            'user' => $user,
            'passwordForm' => $passwordForm->createView(),
            'nameSurnameForm' => $nameSurnameForm->createView(),
//            'phoneForm' => $phoneForm->createView(),
        ]);
    }
}
