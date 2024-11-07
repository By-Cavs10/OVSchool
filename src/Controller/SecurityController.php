<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
#[Route(path: '/', name: 'app')]
class SecurityController extends AbstractController
{
    #[Route(path: 'connexion', name: '_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('main_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('', name: '_home')]
    public function logoutSuccess(EntityManagerInterface $entityManager): Response
    {
        $user=$entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        $this->addFlash('success', 'Au revoir '.$user->getPseudo().', vous vous êtes déconnecté-e avec succès.');
        return $this->redirectToRoute('main_home');
    }

    #[Route('/login_success', name: 'login_success')]
    public function loginSuccess(EntityManagerInterface $entityManager): Response
    {
        $user=$entityManager->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);

        // Ajouter le message flash de bienvenue
        $this->addFlash('success', 'Bienvenue '.$user->getPseudo().', vous vous êtes connecté-e avec succès !');

        // Rediriger vers la page d'accueil ou autre
        return $this->redirectToRoute('main_home');
    }

    #[Route(path: 'deconnexion', name: '_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
