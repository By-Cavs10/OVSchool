<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findBy(['etat' => true], ['dateLimiteInscription' => 'DESC']);
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties
        ]);
    }

    #[Route('/detail/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function detail(Sortie $sortie, Request $request): Response
    {
        if ($request->get('partial')) {
            return $this->render('sortie/detail_content.html.twig', [
                'sortie' => $sortie,
            ]);
        }

        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,

        ]);
    }

    #[Route('/edit', name: '_edit')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $sortie->setNom($sortie->getNom());
            $sortie->setDateHeureDebut($sortie->getDateHeureDebut());
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('sucess', 'Sortie bien créé');
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/edit.html.twig', [
            'form' => $form,

        ]);
    }

}
