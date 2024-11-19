<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\InscriptionFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'app_inscription')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        AppAuthenticator $authenticator,
        EntityManagerInterface $entityManager
    ): Response {
        $participant = new Participant();
        $form = $this->createForm(InscriptionFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère le mot de passe en clair depuis le formulaire
            $plainPassword = $form->get('plainPassword')->getData();

            // Hash le mot de passe
            $participant->setPassword($passwordHasher->hashPassword($participant, $plainPassword));

            // Sauvegarde l'utilisateur en base
            $entityManager->persist($participant);
            $entityManager->flush();

            // Ajoute un message flash
            $this->addFlash('success', "Your account has been created");

            // Authentifie l'utilisateur et redirige
            return $userAuthenticator->authenticateUser(
                $participant,
                $authenticator,
                $request
            );
        }

        // Affiche le formulaire
        return $this->render('inscription/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

