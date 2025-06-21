<?php

namespace App\Controller;

use App\Entity\BookedRdv;
use App\Entity\Rdv;
use App\Repository\RdvRepository;
use App\Service\PayPalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    #[Route('/payment/process', name: 'payment_process')]
    public function process(
        Request $request, 
        PayPalService $paypalService, 
        EntityManagerInterface $entityManager
    ): Response 
    {
        // Get pending RDV from session
        $pendingRdv = $request->getSession()->get('pending_rdv');
        if (!$pendingRdv) {
            $this->addFlash('error', 'Aucune réservation en cours');
            return $this->redirectToRoute('calendar');
        }
        
        // Get the RDV entity
        $rdv = $entityManager->getRepository(Rdv::class)->find($pendingRdv['rdv_id']);
        if (!$rdv) {
            $this->addFlash('error', 'Type de rendez-vous introuvable');
            return $this->redirectToRoute('calendar');
        }
        
        // Create PayPal payment
        $order = $paypalService->createPayment(
            $rdv->getName(),
            (float)$rdv->getPrice(),
            $rdv->getId(),
            $pendingRdv['client_surname']
        );
        
        if (!$order) {
            $this->addFlash('error', 'Erreur lors de la création du paiement PayPal');
            return $this->redirectToRoute('booking_summary');
        }
        
        // Store order ID in session
        $request->getSession()->set('paypal_order_id', $order->result->id);
        
        // Get the approval URL
        $approvalUrl = $paypalService->getApprovalLink($order);

        if ($approvalUrl) {
            return $this->redirect($approvalUrl);
        } else {
            $this->addFlash('error', 'Erreur lors de la redirection vers PayPal');
            return $this->redirectToRoute('booking_summary');
        }
    }
    
    #[Route('/payment/success', name: 'payment_success')]
    public function success(
        Request $request, 
        PayPalService $paypalService, 
        EntityManagerInterface $entityManager
    ): Response 
    {
        // Get PayPal parameters
        $orderId = $request->query->get('token');

        if (!$orderId) {
            $this->addFlash('error', 'Informations de paiement manquantes');
            return $this->redirectToRoute('calendar');
        }

        // Capture payment
        $result = $paypalService->capturePayment($orderId);

        if (!$result) {
            $this->addFlash('error', 'Erreur lors de la capture du paiement');
            return $this->redirectToRoute('booking_summary');
        }
        
        // Get pending RDV from session
        $pendingRdv = $request->getSession()->get('pending_rdv');
        if (!$pendingRdv) {
            $this->addFlash('error', 'Aucune réservation en cours');
            return $this->redirectToRoute('calendar');
        }
        
        // Get RDV entity
        $rdv = $entityManager->getRepository(Rdv::class)->find($pendingRdv['rdv_id']);
        
        // Create BookedRdv entity with payment info
        $bookedRdv = new BookedRdv();
        $bookedRdv->setRdv($rdv);
        $bookedRdv->setClientSurname($pendingRdv['client_surname']);
        $bookedRdv->setBeginAt(new \DateTimeImmutable($pendingRdv['begin_at']));
        $bookedRdv->setPaymentId($orderId);
        $bookedRdv->setPaid(true);
        $bookedRdv->setBookingToken(bin2hex(random_bytes(16))); // Generate unique token
        
        // Save to database
        $entityManager->persist($bookedRdv);
        $entityManager->flush();
        
        // Clean up session
        $request->getSession()->remove('pending_rdv');
        $request->getSession()->remove('paypal_payment_id');
        
        // Save booking info in localStorage via JS
        $bookingData = [
            'id' => $bookedRdv->getId(),
            'token' => $bookedRdv->getBookingToken(),
            'date' => $bookedRdv->getBeginAt()->format('Y-m-d H:i:s')
        ];
        
        // Add flash message
        $this->addFlash('success', 'Votre réservation a été confirmée avec succès!');
        
        // Render confirmation page
        return $this->render('payment/success.html.twig', [
            'bookedRdv' => $bookedRdv,
            'rdv' => $rdv,
            'bookingData' => json_encode($bookingData)
        ]);
    }
    
    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(Request $request): Response
    {
        $this->addFlash('info', 'Le paiement a été annulé');
        return $this->redirectToRoute('booking_summary');
    }
}