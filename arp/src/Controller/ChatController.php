<?php 
// src/Controller/ChatController.php
namespace App\Controller;

use Lcobucci\JWT\Configuration;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\Message;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ChatController extends AbstractController
{

// Dans la méthode chatSession de ChatController.php
#[Route('/chat/session', name: 'chat_session', methods: ['GET'])]
    public function chatSession(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        MailerInterface $mailer
    ): Response {
        try {
            // 1. Récupération de la room exclusive à ce visiteur
            $roomId = $session->get('chat_room_id');
            $isNewRoom = false;
            
            if ($roomId) {
                $room = $entityManager->getRepository(Room::class)->find($roomId);
                if (!$room) {
                    // La room n'existe plus, on en crée une nouvelle
                    $room = new Room();
                    $room->setName('Discussion invité ' . uniqid());
                    $entityManager->persist($room);
                    $entityManager->flush();
                    $session->set('chat_room_id', $room->getId());
                    $roomId = $room->getId();
                    $isNewRoom = true;
                }
            } else {
                // Nouvelle session, création d'une room exclusive
                $room = new Room();
                $room->setName('Discussion invité ' . uniqid());
                $entityManager->persist($room);
                $entityManager->flush();
                $session->set('chat_room_id', $room->getId());
                $roomId = $room->getId();
                $isNewRoom = true;
            }

            // 2. Récupération du nom d'utilisateur depuis la session
            $userName = $session->get('chat_username', '');
            $hasUsername = !empty($userName);

            // 3. Tenter d'envoyer un email mais continuer même en cas d'erreur
            try {
                $emailSubject = $isNewRoom ? 'Nouvelle conversation ouverte' : 'Conversation reprise';
                $emailMessage = $isNewRoom 
                    ? "Un visiteur a ouvert une nouvelle conversation (Room #$roomId)"
                    : "Un visiteur a repris une conversation existante (Room #$roomId)";
                
                if ($userName) {
                    $emailMessage .= " - Nom: $userName";
                }
                
                $email = (new Email())
                    ->from('no-reply@example.com')
                    ->to('admin@example.com')
                    ->subject($emailSubject)
                    ->text($emailMessage);
                
                $mailer->send($email);
            } catch (\Exception $e) {
                // Ignorer les erreurs d'email et continuer
                error_log('Erreur envoi email: ' . $e->getMessage());
            }

            // 4. Configuration JWT pour Mercure - avec gestion d'erreur
            try {
                $jwtSecret = $_ENV['MERCURE_JWT_SECRET'] ?? '!ChangeThisMercureHubJWTSecretKey!';
                $config = Configuration::forSymmetricSigner(
                    new Sha256(),
                    InMemory::plainText($jwtSecret)
                );
                $token = $config->builder()
                    ->withClaim('mercure', ['subscribe' => ["/chat/{$room->getId()}"]])
                    ->getToken($config->signer(), $config->signingKey());
                $jwt = $token->toString();
            } catch (\Exception $e) {
                // En cas d'erreur JWT, utiliser une chaîne vide
                error_log('Erreur JWT: ' . $e->getMessage());
                $jwt = '';
            }

            // 5. Récupération des messages de cette room
            $messages = $entityManager->getRepository(Message::class)
                ->findBy(['roomId' => $room->getId()], ['timestamp' => 'ASC']);
            $mercurePublicUrl = $_ENV['MERCURE_PUBLIC_URL'] ?? 'http://127.0.0.1:3000/.well-known/mercure';

            // 6. Rendu du template
            $response = $this->render('chat/index.html.twig', [
                'messages' => $messages,
                'roomId' => $room->getId(),
                'mercure_public_url' => $mercurePublicUrl,
                'userName' => $userName,
                'hasUsername' => $hasUsername
            ]);
            
            // Ajouter le cookie seulement si JWT est valide
            if ($jwt) {
                $response->headers->setCookie(
                    Cookie::create('mercureAuthorization', $jwt)
                        ->withHttpOnly(true)
                        ->withPath('/.well-known/mercure')
                );
            }
            
            return $response;
        } catch (\Exception $e) {
            // Log l'erreur pour debugging
            error_log('Erreur dans chat_session: ' . $e->getMessage());
            
            // Retourner une réponse d'erreur personnalisée
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Une erreur est survenue'], 500);
            }
            
            // Pour requêtes normales, page d'erreur simple
            return new Response(
                '<html><body><h1>Erreur de chargement du chat</h1><p>Veuillez réessayer ultérieurement.</p></body></html>',
                500
            );
        }
    }
    #[Route('/chat/{roomId<\d+>}', name: 'chat_private', methods: ['GET'])]
    public function privateChat(
        int $roomId,
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {
        // Seul l'admin peut accéder à n'importe quelle room
        if (!$this->isGranted('ROLE_ADMIN')) {
            $sessionRoomId = $session->get('chat_room_id');
            if ($sessionRoomId !== $roomId) {
                throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette conversation.');
            }
        }

        $userName = $this->isGranted('ROLE_ADMIN') 
            ? $session->get('chat_username', 'Admin') 
            : $session->get('chat_username', '');
        
        $hasUsername = !empty($userName);

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($_ENV['MERCURE_JWT_SECRET'])
        );
        $token = $config->builder()
            ->withClaim('mercure', ['subscribe' => ["/chat/{$roomId}"]])
            ->getToken($config->signer(), $config->signingKey());
        $jwt = $token->toString();

        $messages = $entityManager->getRepository(Message::class)
            ->findBy(['roomId' => $roomId], ['timestamp' => 'ASC']);
        $mercurePublicUrl = $_ENV['MERCURE_PUBLIC_URL'] ?? $_SERVER['MERCURE_PUBLIC_URL'] ?? '';

        $response = $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'roomId' => $roomId,
            'mercure_public_url' => $mercurePublicUrl,
            'userName' => $userName,
            'hasUsername' => $hasUsername
        ]);
        
        $response->headers->setCookie(
            Cookie::create('mercureAuthorization', $jwt)
                ->withHttpOnly(true)
                ->withPath('/.well-known/mercure')
        );
        
        return $response;
    }

    #[Route('/chat/send/{roomId}', name: 'chat_send', methods: ['POST'])]
    public function send(
        int $roomId,
        Request $request,
        EntityManagerInterface $entityManager,
        HubInterface $hub,
        SessionInterface $session
    ): Response {
        // Vérifier que l'utilisateur a accès à cette room
        if (!$this->isGranted('ROLE_ADMIN')) {
            $sessionRoomId = $session->get('chat_room_id');
            if ($sessionRoomId !== $roomId) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['error' => 'Accès non autorisé'], 403);
                }
                throw $this->createAccessDeniedException('Vous ne pouvez pas accéder à cette conversation.');
            }
        }

        $content = $request->request->get('message');
        $sender = $request->request->get('username');

        // Stocker définitivement le nom d'utilisateur s'il est défini
        if ($sender) {
            $session->set('chat_username', $sender);
        }

        if (!$content || !$sender) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Missing data'], 400);
            }
            return $this->redirectToRoute($this->isGranted('ROLE_ADMIN') ? 'chat_private' : 'chat_session', ['roomId' => $roomId]);
        }

        $message = new Message();
        $message->setContent($content);
        $message->setSender($sender);
        $message->setTimestamp(new \DateTime());
        $message->setRoomId($roomId);

        $entityManager->persist($message);
        $entityManager->flush();

        $update = new Update(
            "/chat/{$roomId}",
            json_encode([
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sender' => $message->getSender(),
                'timestamp' => $message->getTimestamp()->format('Y-m-d H:i:s')
            ]),
            true
        );
        $hub->publish($update);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }
        
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('chat_private', ['roomId' => $roomId]);
        } else {
            return $this->redirectToRoute('chat_session');
        }
    }

    #[Route('/admin/chat-rooms', name: 'admin_chat_rooms', methods: ['GET'])]
    public function listRooms(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        $rooms = $entityManager->getRepository(Room::class)->findAll();
        return $this->render('admin/chat_rooms.html.twig', [
            'rooms' => $rooms,
        ]);
    }
}