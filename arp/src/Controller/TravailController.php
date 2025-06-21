<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TravailController extends AbstractController
{
    #[Route('/travail', name: 'app_travail')]
    public function index(): Response
    {
        return $this->render('travail/index.html.twig', [
            'controller_name' => 'TravailController',
        ]);
    }
}
