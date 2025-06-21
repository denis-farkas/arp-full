<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PensionController extends AbstractController
{
    #[Route('/pension', name: 'app_pension')]
    public function index(): Response
    {
        return $this->render('pension/index.html.twig', [
            'controller_name' => 'PensionController',
        ]);
    }
}
