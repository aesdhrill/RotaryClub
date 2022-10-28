<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '', name: 'dashboard_', priority: 11)]
#[IsGranted('ROLE_USER')]
class DashboardController extends BaseController
{

    #[Route(path: '', name: 'index_redirect')]
    public function indexRedirect(): RedirectResponse
    {
        return  $this->redirectToRoute('dashboard_index');
    }


    #[Route(path: '/dashboard', name: 'index')]
    public function index(Request $request): Response
    {
        return $this->render('system/dashboard.html.twig');
    }
}