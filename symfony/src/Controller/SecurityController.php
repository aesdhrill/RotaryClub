<?php

namespace App\Controller;

use App\Enum\TokenType;
use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response {

        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard_index');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/pwSet/{value}', name: 'security_set_password')]
    public function setNewPassword(
        Token $token,
        Request $request,
        UserPasswordHasherInterface $passwordEncoder,
        UserManager $userManager
    ): RedirectResponse|Response {
        if (!TokenType::isValidForSettingPassword($token->getType())) {
            $this->addFlash('error', $this->translator->trans('security.flashes.wrong_token'));

            return $this->redirectToRoute('app_login');
        }

        if ($token->getValidTo() < (new \DateTime())) {
            $this->addFlash('warning', $this->translator->trans('security.flashes.invalid_token'));

            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $token->getUser();

            $newPassword = $passwordEncoder->hashPassword(
                $user, $form->get('password')->getData()
            );

            $token->setValidTo(new \DateTime());

            $user->setPassword($newPassword);

            if ($token->getType() === TokenType::ACTIVATE_ACCOUNT) {
                $user->setStatus(UserStatus::ACTIVE);
            }

            $userManager->save($user);

            $this->addFlash('success', $this->translator->trans('security.flashes.password_set'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/confirm_email.html.twig', [
            'form' => $form->createView()
        ]);
    }
}