<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EspionController extends AbstractController
{
    #[Route('/espion', name: 'app_espion')]
    public function index(): Response
    {
        return $this->render('espion/index.html.twig', [
            'controller_name' => 'EspionController',
        ]);
    }
}
