<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MaladieController extends AbstractController
{
    #[Route('/maladie', name: 'app_maladie')]
    public function index(): Response
    {
        return $this->render('maladie/index.html.twig', [
            'controller_name' => 'MaladieController',
        ]);
    }
}
