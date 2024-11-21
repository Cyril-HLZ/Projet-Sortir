<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\PasswordType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['POST', 'GET'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/password', name: 'app_password', methods: ['GET', 'POST'])]
    public function password(Request $request, UserPasswordHasherInterface $passwordHasher,
                             EntityManagerInterface $entityManager): Response
    {
        // Création d'un formulaire pour changer le mot de passe
        $passwordForm = $this->createForm(PasswordType::class);
        $passwordForm->handleRequest($request);

        // Validation du formulaire
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            // Récupérer l'email saisi par l'utilisateur
            $mail = $passwordForm->get('mail')->getData();

            // Rechercher l'utilisateur dans la base de données
            $participant = $entityManager->getRepository(Participant::class)->findOneBy(['mail' => $mail]);

            if ($participant) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $participant,
                    $passwordForm->get('password')->getData()
                );

                $participant->setPassword($hashedPassword);

                // Enregistrement en base
                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('success', 'Le mot de passe a bien été modifié.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', 'Adresse email non reconnue.');
            }
        }

        // Affichage du formulaire
        return $this->render('security/password.html.twig', [
            'passwordForm' => $passwordForm->createView(),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}