<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MystereController extends AbstractController
{
    #[Route('/mystere', name: 'app_mystere')]
    public function index(): Response
    {
        return $this->render('mystere/index.html.twig', [
            'controller_name' => 'MystereController',
        ]);
    }
}
