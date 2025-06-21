<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SolvableController extends AbstractController
{
    #[Route('/solvable', name: 'app_solvable')]
    public function index(): Response
    {
        return $this->render('solvable/index.html.twig', [
            'controller_name' => 'SolvableController',
        ]);
    }
}
