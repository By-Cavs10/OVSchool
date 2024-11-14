<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             $this->addFlash('warning', 'Vous êtes déjà connecté-e.');
             return $this->redirectToRoute('main_home');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/deconnexion-succes', name: 'app_logout_success')]
    public function logoutSuccess(): Response
    {
        $this->addFlash('success', 'Vous vous êtes déconnecté-e avec succès.');

        return $this->redirectToRoute('main_home');
    }

    #[Route('/connexion-succes', name: 'app_login_success')]
    public function loginSuccess(): Response
    {

        $user=$this->getUser();

        // Ajouter le message flash de bienvenue
        $this->addFlash('success', 'Bienvenue '.$user->getPseudo().', vous vous êtes connecté-e avec succès !');

        // Rediriger vers la page d'accueil ou autre
        return $this->redirectToRoute('main_home');
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
