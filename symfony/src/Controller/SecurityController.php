<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Enum\TokenType;
use App\Enum\UserStatus;
use App\Form\Security\RegistrationType;
use App\Form\Security\ResetPasswordType;
use App\Form\Security\ForgotPasswordType;
use App\Manager\TokenManager;
use App\Manager\UserManager;
use App\Repository\UserRepository;
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

    #[Route(path: '/signup/', name: 'security_signup')]
    public function signup(
        Request $request,
        UserManager $userManager
    ): Response
    {
        $form = $this->createForm(RegistrationType::class);

        return $this->render('security/signup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/set_password/{value}', name: 'security_set_password')]
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

    #[Route(path: '/forgot_password', name: 'security_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, TokenManager $tokenManager): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            /** @var User $user */
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user && $user->getStatus() !== UserStatus::BLOCKED) {
                $token = new Token();
                $token->setType(TokenType::FORGOT_PASSWORD);
                $token->setValidTo((new \DateTime())->add(new \DateInterval('P1D')));
                $token->setUser($user);

                $tokenManager->save($token);

                $this->mailer->sendResetPassword($user, $token);
            }

            $this->addFlash('info', $this->translator->trans('security.flashes.password_reset_link'));
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}