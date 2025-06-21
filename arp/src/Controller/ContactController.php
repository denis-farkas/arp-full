<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }
    
    #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
    public function send(Request $request, MailerInterface $mailer): Response
    {
        // 1. Récupérer les données du formulaire
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');
        $sujet = $request->request->get('sujet');
        $message = $request->request->get('message');
        $rgpd = $request->request->has('rgpd');
        
        // 2. Validation basique
        $errors = [];
        if (!$nom) $errors[] = 'Le nom est requis';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
        if (!$sujet) $errors[] = 'Le sujet est requis';
        if (!$message) $errors[] = 'Le message est requis';
        if (!$rgpd) $errors[] = 'Vous devez accepter la politique de confidentialité';
        
        // 3. Si erreurs, retourner à la page de contact avec messages d'erreur
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
            return $this->redirectToRoute('app_contact');
        }
        
        // 4. Envoyer l'email
        try {
            $email = (new Email())
                ->from($email)
                ->to('contact@example.com') // Remplacer par l'adresse email de destination
                ->subject('Formulaire de contact: ' . $sujet)
                ->text("Message de: {$nom} ({$email})\n\n{$message}")
                ->html("
                    <h3>Nouveau message du formulaire de contact</h3>
                    <p><strong>De:</strong> {$nom}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Sujet:</strong> {$sujet}</p>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                ");
            
            $mailer->send($email);
            
            // 5. Message de confirmation
            $this->addFlash('success', 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.');
            
        } catch (\Exception $e) {
            // 6. Gestion des erreurs
            $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer ultérieurement.');
        }
        
        // 7. Redirection
        return $this->redirectToRoute('app_contact');
    }
}