<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ConcurrenceController extends AbstractController
{
    #[Route('/concurrence', name: 'app_concurrence')]
    public function index(): Response
    {
        return $this->render('concurrence/index.html.twig', [
            'controller_name' => 'ConcurrenceController',
        ]);
    }
}
