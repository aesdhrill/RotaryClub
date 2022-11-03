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

        $expiryDateForm = $this->createForm(ExpiryDateType::class, $user);
        $expiryDateForm->handleRequest($request);

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

        if ($expiryDateForm->isSubmitted() && $expiryDateForm->isValid()) {
            $userManager->save($expiryDateForm->getData());

            return $this->redirectToRoute('admin_users_details', [
                'id' => $user->getId(),
            ]);
        }

//        $logEntryHistory = $logEntryRepository->findAllForUser($user);

        return $this->render('system/admin/users/details.html.twig', [
            'user' => $user,
            'rolesForm' => $rolesForm->createView(),
            'statusForm' => $statusForm->createView(),
            'expiryDateForm' => $expiryDateForm->createView(),
//            'logEntryHistory' => $logEntryHistory,
        ]);
    }

    #[Route(path: '/new', name: 'new')]
    public function new(
        Request $request, UserRepository $userRepository, UserManager $userManager,
    ): Response {
        $form = $this->createForm(NewUserType::class, null, [
            'facilities' => new ArrayCollection($facilityRepository->findAll()),
            'userFacilities' => $this->getUser()->getUserFacilities()->map(function($userFacility) {
                return $userFacility->getFacility();
            })
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userFacilities = new ArrayCollection($this->getUser()->getUserFacilities()->map(function($userFacility) { return $userFacility->getFacility(); })->toArray());

            /** @var User $newUser */
            $newUser = $form->getData();
            $userEmail = $newUser->getEmail();
            $user = $userRepository->findOneBy(['email' => $userEmail]);

            if (!$user){
                $newUser->setPassword('WRONG_PASSWORD');
                $newUser->setValidTo(new \DateTime('+1 month'));
                $userManager->save($newUser);

                /** @var Facility $facility */
                foreach ($userFacilities as $facility) {
                    $userFacility = new UserFacility();
                    $userFacility->setUser($newUser);
                    $userFacility->setFacility($facility);
                    $userFacilityManager->save($userFacility);
                }

                $this->addFlash('success', $this->translator->trans('user.flash.user_added'));
                return $this->redirectToRoute('admin_users_new');
            }

            $this->addFlash('warning', $this->translator->trans('user.flash.user_exists'));
        }

        return $this->render('system/admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/dt', name: 'dt', condition: 'request.isXmlHttpRequest()')]
    public function dt(Request $request, UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findForDt($request, $this->getUser()));
    }
}
