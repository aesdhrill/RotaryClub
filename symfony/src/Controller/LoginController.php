<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', name: 'login_')]
class LoginController extends AbstractController
{
    #[Route('', name: 'login')]
    public function login(): Response {

        return $this->render('login.html.twig');
    }
}