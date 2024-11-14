<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'main')]
class MainController extends AbstractController
{
    #[Route('/', name: '_home')]
    public function accueil(): Response
    {
        return $this->render('main/index.html.twig');
    }
}
