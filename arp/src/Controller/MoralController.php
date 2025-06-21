<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MoralController extends AbstractController
{
    #[Route('/moral', name: 'app_moral')]
    public function index(): Response
    {
        return $this->render('moral/index.html.twig', [
            'controller_name' => 'MoralController',
        ]);
    }
}
