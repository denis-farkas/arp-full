<?php


namespace App\Controller;

use App\Entity\BookedRdv;
use App\Repository\BookedRdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/bookings', name: 'admin_bookings')]
    public function bookings(BookedRdvRepository $bookedRdvRepository): Response
    {
        $bookings = $bookedRdvRepository->findBy([], ['createdAt' => 'DESC']);
        
        return $this->render('admin/bookings.html.twig', [
            'bookings' => $bookings
        ]);
    }

    #[Route('/booking/{id}/toggle-payment', name: 'admin_toggle_payment', methods: ['POST'])]
    public function togglePayment(BookedRdv $bookedRdv, EntityManagerInterface $em): Response
    {
        $bookedRdv->setIsPaid(!$bookedRdv->isPaid());
        $em->flush();
        
        $this->addFlash('success', 'Statut de paiement mis à jour');
        
        return $this->redirectToRoute('admin_bookings');
    }

    #[Route('/booking/{id}/delete', name: 'admin_delete_booking', methods: ['POST'])]
    public function deleteBooking(BookedRdv $bookedRdv, EntityManagerInterface $em): Response
    {
        $em->remove($bookedRdv);
        $em->flush();
        
        $this->addFlash('success', 'Réservation supprimée');
        
        return $this->redirectToRoute('admin_bookings');
    }
}