<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Form\UpdateType;
use App\Helper\FileUploader;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    #[Route('/list', name: '_list')]
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAll(['etat' => true], ['dateLimiteInscription' => 'DESC']);
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
        $googleMapsApiKey = $_ENV['GOOGLE_API_KEY'];
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie,
            'google_maps_api_key' => $googleMapsApiKey,
            'latitude' => $sortie->getLieu()->getLatitude(),
            'longitude' => $sortie->getLieu()->getLongitude(),

        ]);
    }



    #[Route('/edit', name: '_edit')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $sortie = new Sortie();  // Créer une nouvelle sortie
        $form = $this->createForm(SortieType::class, $sortie);  // Créer le formulaire pour Sortie

        $form->handleRequest($request);
        $googleMapsApiKey = $_ENV['GOOGLE_API_KEY'];
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données des champs cachés du formulaire
            $latitude = $form->get('latitude')->getData();
            $longitude = $form->get('longitude')->getData();
            $nomLieu = $form->get('nomLieu')->getData();  // Nom du lieu
            $rue = $form->get('rue')->getData();
            $codePostal = $form->get('codePostal')->getData();
            $nomVille = $form->get('nomVille')->getData();  // Nom de la ville

            // Créer ou récupérer la ville
            $ville = $em->getRepository(Ville::class)->findOneBy(['nom' => $nomVille]);
            $lieu = $em->getRepository(Lieu::class)->findOneBy(['nom' => $nomLieu, 'rue' => $rue]);

            if (!$ville) {
                // Si la ville n'existe pas encore, la créer
                $ville = new Ville();
                $ville->setNom($nomVille);
                $ville->setCodePostal($codePostal);
                $em->persist($ville);  // Persister la ville
            }

                // Gérer le cas où $nomLieu est vide
            if (!$lieu) {
                // Créer l'entité Lieu
                $lieu = new Lieu();
                $lieu->setNom($nomLieu);
                $lieu->setLatitude($latitude);
                $lieu->setLongitude($longitude);
                $lieu->setRue($rue);
                $lieu->setVille($ville);

            }
            // Si une photo est uploadée, gérer l'upload
            $file = $form->get('urlPhoto')->getData();
            if ($file instanceof UploadedFile) {
                $name = $fileUploader->uploadPhoto($file, $sortie->getNom(), 'upload_directory_sorties');
                $sortie->setUrlPhoto(strtolower($name));
            }

            $inscriptionOption = $form->get('inscriptionOption')->getData();

            if ($inscriptionOption === 'now') {
                $sortie->setDateDebutInscription(null);

                // Récupérer l'objet Etat avec l'ID (2 ici par exemple)
                $etat = $em->getRepository(Etat::class)->find(2);  // 2 pour l'état "ouverte"
                if ($etat) {
                    $sortie->setEtat($etat);  // Assigner l'entité Etat à Sortie
                }

            } elseif ($inscriptionOption === 'later') {
                $dateDebutInscription = $form->get('dateDebutInscription')->getData();
                $sortie->setDateDebutInscription($dateDebutInscription);

                // Récupérer l'objet Etat avec l'ID (1 ici par exemple)
                $etat = $em->getRepository(Etat::class)->find(1);  // 1 pour l'état "créé"
                if ($etat) {
                    $sortie->setEtat($etat);  // Assigner l'entité Etat à Sortie
                }
            }

            // Associer le lieu à la sortie


            $sortie->setLieu($lieu);
            $em->persist($lieu);
            $em->persist($sortie);  // Persister la sortie
            $em->flush();

            // Message de confirmation
            $this->addFlash('success', 'Sortie bien créée');

            // Redirection après la création
            return $this->redirectToRoute('sortie_list');
        }

        // Si le formulaire n'est pas soumis ou n'est pas valide
        return $this->render('sortie/edit.html.twig', [
            'form' => $form->createView(),
                'google_maps_api_key' => $googleMapsApiKey,
        ]);
    }


    #[Route('/update/{id}', name: '_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, EntityManagerInterface $em, Sortie $sortie): Response
    {
        $form = $this->createForm(UpdateType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em->flush();

            $this->addFlash('success', 'La sortie a été modifiée avec succès !');
            return $this->redirectToRoute('sortie_list');
        }

        return $this->render('sortie/update.html.twig', [
            'form' => $form,
            'sortie' => $sortie,

        ]);
    }

    #[Route('/delete/{id}', name: '_delete', requirements: ['id' => '\d+'])]
    public function delete(Sortie $sortie, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->get('token'))) {
            $em->remove($sortie);
            $em->flush();

            $this->addFlash('success', 'La sortie a été supprimée');
        } else {
            $this->addFlash('danger', 'Pas possible de supprimer! ');
        }
        return $this->redirectToRoute('sortie_list');

    }

    #[Route('/cancel-delete/{id}', name: '_cancel_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function cancelDelete(Sortie $sortie, EntityManagerInterface $em): JsonResponse
    {


        // Change l'état de la sortie à 6
        $etat = $em->getRepository(Etat::class)->find(6);
        $sortie->setEtat($etat);
        $em->persist($sortie);
        $em->flush();



        return new JsonResponse(['success' => true]);
    }


}


