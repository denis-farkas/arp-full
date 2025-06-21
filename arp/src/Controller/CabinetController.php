<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CabinetController extends AbstractController
{
    #[Route('/cabinet', name: 'app_cabinet')]
    public function index(): Response
    {
        return $this->render('cabinet/index.html.twig', [
            'controller_name' => 'CabinetController',
        ]);
    }
}
