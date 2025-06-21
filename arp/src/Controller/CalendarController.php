<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\BookedRdv;
use App\Form\BuyRdvType;
use App\Repository\BookedRdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'calendar', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Remove the user authentication requirement for booking
        // Only admins need to be authenticated for admin functions
        
        $rdv = $em->getRepository(Rdv::class)->findOneBy([]);
        if (!$rdv) {
            $this->addFlash('error', 'Aucun type de rendez-vous disponible');
            return $this->redirectToRoute('app_home');
        }

        $rdvEntity = new BookedRdv();
        $form = $this->createForm(BuyRdvType::class, $rdvEntity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Since form fields are 'mapped' => false, get data from form directly
            $clientSurname = $form->get('clientSurname')->getData();
            $beginAt = $form->get('beginAt')->getData();
            
            // Validate we have the data
            if (!$clientSurname || !$beginAt) {
                $this->addFlash('error', 'Données manquantes');
                return $this->redirectToRoute('calendar');
            }

            // DON'T PERSIST YET - just prepare the data for payment
            $rdvData = [
                'rdv_id' => $rdv->getId(),
                'client_surname' => $clientSurname,
                'begin_at' => $beginAt->format('Y-m-d H:i:s'),
                'price' => $rdv->getPrice(),
                'duration' => $rdv->getDuration()
            ];

            // Store in session for summary page
            $request->getSession()->set('pending_rdv', $rdvData);

            // Check if it's an AJAX request
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'redirect' => $this->generateUrl('booking_summary')
                ]);
            }

            // For normal requests, redirect to summary page
            return $this->redirectToRoute('booking_summary');
        }

        return $this->render('calendar/index.html.twig', [
            'rdv' => $rdv,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/booking/summary', name: 'booking_summary')]
    public function summary(Request $request, EntityManagerInterface $em): Response
    {
        $pendingRdv = $request->getSession()->get('pending_rdv');
        if (!$pendingRdv) {
            $this->addFlash('error', 'Aucune réservation en cours');
            return $this->redirectToRoute('calendar');
        }

        // Get the Rdv object for additional information if needed
        $rdv = $em->getRepository(Rdv::class)->find($pendingRdv['rdv_id']);
        if (!$rdv) {
            $this->addFlash('error', 'Type de rendez-vous introuvable');
            return $this->redirectToRoute('calendar');
        }

        return $this->render('calendar/summary.html.twig', [
            'pendingRdv' => $pendingRdv,
            'rdv' => $rdv
        ]);
    }

    #[Route('/calendar/check-conflicts', name: 'calendar_check_conflicts', methods: ['POST'])]
    public function checkConflicts(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        try {
            $beginAt = new \DateTimeImmutable($data['beginAt']);
            $duration = (int) $data['duration'];
            
            $conflicts = $this->checkTimeConflicts($beginAt, $duration, $entityManager);
            
            return new JsonResponse([
                'hasConflicts' => !empty($conflicts),
                'conflictCount' => count($conflicts)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'hasConflicts' => false,
                'error' => 'Erreur lors de la vérification'
            ]);
        }
    }

    #[Route('/load-events', name: 'fc_load_events')]
    public function loadEvents(BookedRdvRepository $bookedRdvRepository): JsonResponse
    {
        $allBookedRdv = $bookedRdvRepository->findAll();
        $events = [];
        
        foreach ($allBookedRdv as $bookedRdv) {
            $rdv = $bookedRdv->getRdv();
            $beginAt = $bookedRdv->getBeginAt();
            
            $durationInMinutes = $rdv->getDuration();
            $endAt = (clone $beginAt)->modify("+{$durationInMinutes} minutes");
            
            $events[] = [
                'id' => $bookedRdv->getId(),
                'title' => $rdv->getName() . ' - ' . substr($bookedRdv->getClientSurname(), 0, 1) . '***',
                'start' => $beginAt->format('Y-m-d\TH:i:s'),
                'end' => $endAt->format('Y-m-d\TH:i:s'),
                'backgroundColor' => $bookedRdv->isPaid() ? '#28a745' : '#dc3545',
                'borderColor' => $bookedRdv->isPaid() ? '#28a745' : '#dc3545',
                'textColor' => '#ffffff'
            ];
        }

        return new JsonResponse($events);
    }

    private function checkTimeConflicts(\DateTimeImmutable $beginAt, int $duration, EntityManagerInterface $em): array
    {
        return $em->getRepository(BookedRdv::class)->findConflictingBookings($beginAt, $duration);
    }
}