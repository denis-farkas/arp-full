<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DivorceController extends AbstractController
{
    #[Route('/divorce', name: 'app_divorce')]
    public function index(): Response
    {
        return $this->render('divorce/index.html.twig', [
            'controller_name' => 'DivorceController',
        ]);
    }
}
