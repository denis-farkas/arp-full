<?php
// src/Controller/HeaderController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class HeaderController extends AbstractController
{
    public function index(): Response
    {
        // Définition des routes pour le menu
        $menuItems = [
            ['label' => 'ACCUEIL', 'link' => $this->generateUrl('app_home')],
            ['label' => 'LE CABINET', 'link' => $this->generateUrl('app_cabinet')],
            [
                'label' => 'PARTICULIERS',
                'isDropdown' => true,
                'dropdownItems' => [
                    [
                        ['label' => 'Divorce conflictuel', 'link' => $this->generateUrl('app_divorce')],
                        ['label' => 'Garde d\'enfant', 'link' => $this->generateUrl('app_garde')],
                        ['label' => 'Pension alimentaire', 'link' => $this->generateUrl('app_pension')],
                    ],
                    [
                        ['label' => 'Violations du droit du travail', 'link' => $this->generateUrl('app_travail')],
                        ['label' => 'Enquête de moralité', 'link' => $this->generateUrl('app_moral')],
                        ['label' => 'Enquête de solvabilité', 'link' => $this->generateUrl('app_solvable')],
                    ],
                    [
                        ['label' => 'Personnes disparues ou introuvables', 'link' => $this->generateUrl('app_disparue')]
                    ],
                ],
            ],
            [
                'label' => 'ENTREPRISES',
                'isDropdown' => true,
                'dropdownItems' => [
                    [
                        ['label' => 'Analyse des antécédents', 'link' => $this->generateUrl('app_antecedent')],
                        ['label' => 'Suspicion d\'arrêt maladie abusif', 'link' => $this->generateUrl('app_maladie')],
                        ['label' => 'Détection de concurrence déloyale', 'link' => $this->generateUrl('app_concurrence')],
                    ],
                    [
                        ['label' => 'Suspicion du personnel', 'link' => $this->generateUrl('app_personnel')],
                        ['label' => 'Détection des vols internes', 'link' => $this->generateUrl('app_vols')],
                        ['label' => 'Opérations de client mystère', 'link' => $this->generateUrl('app_mystere')],
                    ],
                    [
                        ['label' => 'Protection contre l\'espionnage industriel', 'link' => $this->generateUrl('app_espion')],
                        ['label' => 'Conseils en sécurité', 'link' => $this->generateUrl('app_securite')],
                    ],
                ],
            ],
            ['label' => 'TARIFS', 'link' => $this->generateUrl('app_tarif')],
            ['label' => 'CONTACTS', 'link' => $this->generateUrl('app_contact')],
            ['label' => 'ARTICLES', 'link' => $this->generateUrl('app_article')],
        ];

        return $this->render('base/header.html.twig', [
            'menuItems' => $menuItems
        ]);
    }
}