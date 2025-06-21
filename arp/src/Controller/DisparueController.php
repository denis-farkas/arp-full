<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DisparueController extends AbstractController
{
    #[Route('/disparue', name: 'app_disparue')]
    public function index(): Response
    {
        return $this->render('disparue/index.html.twig', [
            'controller_name' => 'DisparueController',
        ]);
    }
}
