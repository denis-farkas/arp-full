<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VolsController extends AbstractController
{
    #[Route('/vols', name: 'app_vols')]
    public function index(): Response
    {
        return $this->render('vols/index.html.twig', [
            'controller_name' => 'VolsController',
        ]);
    }
}
