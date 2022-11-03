<?php

namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Entity\Token;
use App\Entity\User;
use App\Enum\TokenType;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Form\User\ExpiryDateType;
use App\Form\User\NewUserType;
use App\Form\User\RolesType;
use App\Form\User\StatusType;
use App\Manager\TokenManager;
use App\Manager\UserManager;
use App\Repository\LogEntryRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/admin/users', name: 'admin_users_')]
#[IsGranted('ROLE_ADMINISTRATION')]
class UserController extends BaseController
{
    #[Route(path: '/', name: 'list')]
    public function index(): Response
    {
        return $this->render('system/admin/users/index.html.twig', []);
    }


    #[Route(path: '/details/{id<\d+>}', name: 'details')]
    #todo: implement LogEntryRepository $logEntryRepository as an argument?
    public function details(User $user, Request $request, UserManager $userManager, TokenManager $tokenManager): Response
    {
        $rolesForm = $this->createForm(RolesType::class, $user);
        $rolesForm->handleRequest($request);

        $statusForm = $this->createForm(StatusType::class, $user);
        $statusForm->handleRequest($request);

        if ($rolesForm->isSubmitted() && $rolesForm->isValid()) {
            $userManager->save($rolesForm->getData());

            return $this->redirectToRoute('admin_users_details', [
                'id' => $user->getId(),
            ]);
        }

        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            $userManager->save($statusForm->getData());
            if ($statusForm->get('status')->getData() === UserStatus::ACTIVE){
                $token = new Token();
                $token->setType(TokenType::ACTIVATE_ACCOUNT);
                $token->setValidTo((new \DateTime())->add(new \DateInterval('P7D')));
                $token->setUser($user);

                $tokenManager->save($token);

                $this->mailer->sendSignUp($user, $token);
            }

            return $this->redirectToRoute('admin_users_details', [
                'id' => $user->getId(),
            ]);
        }

//        $logEntryHistory = $logEntryRepository->findAllForUser($user);

        return $this->render('system/admin/users/details.html.twig', [
            'user' => $user,
            'rolesForm' => $rolesForm->createView(),
            'statusForm' => $statusForm->createView(),
//            'logEntryHistory' => $logEntryHistory,
        ]);
    }

    #[Route(path: '/dt', name: 'dt', condition: 'request.isXmlHttpRequest()')]
    public function dt(Request $request, UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findForDt($request, $this->getUser()));
    }
}
