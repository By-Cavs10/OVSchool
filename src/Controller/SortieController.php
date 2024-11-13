<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\User;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Form\UpdateType;
use App\Helper\FileUploader;
use App\Manager\SortieManager;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie')]
class SortieController extends AbstractController
{
    public function __construct(private SortieManager $sortieManager){}
    #[Route('/list', name: '_list')]
    public function list(VilleRepository $villeRepository, SortieRepository $sortieRepository): Response
    {
        $this->sortieManager->updateAllSorties();

        $villes = $villeRepository->findBy([], ['nom' => 'ASC']);
        $sorties = $sortieRepository->findAll();

        return $this->render('sortie/list.html.twig', [
            'villes' => $villes,
            'sorties' => $sorties
        ]);
    }

    #[Route('/detail/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    public function detail(Sortie $sortie, Request $request): Response
    {
        $this->sortieManager->updateAllSorties();
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

    #[Route('/{id}/inscription', name: '_subscribe', requirements: ['id' => '\d+'])]
    public function userSubscribe(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortieId = $entityManager->getRepository(Sortie::class)->find($sortie->getId())->getId();

        if (!$sortieId){
            $this->addFlash('danger', 'Inscription impossible - cette Sortie n\'existe pas en BDD.');
            return $this->redirectToRoute('sortie_list');
        }

        if ($sortie->getEtat()->getId() !== 2) {
            $flashMessage = match ($sortie->getEtat()->getId()){
              1 => 'Inscription impossible à "'.$sortie->getNom().'" - la Sortie n\'est pas publiée',
              3 => 'Inscription impossible à "'.$sortie->getNom().'" - la date et/ou l\'heure limites d\'inscription à cette Sortie sont dépassées.',
              4 => 'Inscription impossible à "'.$sortie->getNom().'" - la Sortie a déjà démarrée',
              5 => 'Inscription impossible à "'.$sortie->getNom().'" - la Sortie est terminée',
              6 => 'Inscription impossible à "'.$sortie->getNom().'" - la Sortie est terminée et archivée',
              default => 'Inscription impossible - la Sortie est dans un état inconnu',
            };

            $this->addFlash('danger', $flashMessage);
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        if (count($sortie->getParticipants()) === $sortie->getNbInscriptionsMax()){
            $this->addFlash('danger', 'Inscription impossible à "'.$sortie->getNom().'" - le nombre de participants maximum est atteint.');
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();
        $userId = null;

        if($user){
            $userId = $entityManager->getRepository(User::class)->find($user->getId())->getId();
        }

        if ($userId) {
            $flashMessage = $sortieRepository->userSubscribe($sortie, $user);
            $this->addFlash($flashMessage['label'], $flashMessage['message']);
        } else {
            $this->addFlash('danger', 'Inscription impossible - cet Utilisateur n\'existe pas en BDD.');
        }
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
    }

    #[Route('/{id}/desinscription', name: '_unsubscribe', requirements: ['id' => '\d+'])]
    public function userUnsubscribe(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortieId = $entityManager->getRepository(Sortie::class)->find($sortie->getId())->getId();

        if (!$sortieId){
            $this->addFlash('danger', 'Désinscription impossible - cette Sortie n\'existe pas en BDD.');
            return $this->redirectToRoute('sortie_list');
        }

        $etatId = $sortie->getEtat()->getId();

        if ($etatId !== 2 and $etatId !== 3) {
            $flashMessage = match ($sortie->getEtat()->getId()) {
                1 => 'Accès refusé à "' . $sortie->getNom() . '" - la Sortie n\'est pas publiée',
                4 => 'Accès refusé à "' . $sortie->getNom() . '" - la Sortie a déjà démarrée',
                5 => 'Accès refusé à "' . $sortie->getNom() . '" - la Sortie est terminée',
                6 => 'Accès refusé à "' . $sortie->getNom() . '" - la Sortie est terminée et archivée',
                default => 'Accès refusé - la Sortie est dans un état inconnu',
            };
            $this->addFlash('danger', $flashMessage);
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        $user = $this->getUser();
        $userId = null;

        if($user){
            $userId = $entityManager->getRepository(User::class)->find($user->getId())->getId();
        }

        if ($userId) {
            $flashMessage = $sortieRepository->userUnsubscribe($sortie, $user);
            $this->addFlash($flashMessage['label'], $flashMessage['message']);
        } else {
            $this->addFlash('danger', 'Désinscription impossible - cet Utilisateur n\'existe pas en BDD.');
        }
        return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
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

    #[Route('/cancel-delete/{id}', name: '_cancel_delete', requirements: ['id' => '\d+'])]
    public function cancelDelete(Sortie $sortie, EntityManagerInterface $em): JsonResponse
    {


        // Change l'état de la sortie à "Annulée"
        $etat = $em->getRepository(Etat::class)->find(6);
        $sortie->setEtat($etat);
        $em->persist($sortie);
        $em->flush();



        return new JsonResponse(['success' => true]);
    }

    #[Route('/publish/{id}', name: '_publish', requirements: ['id' => '\d+'])]
    public function publishSortie (Sortie $sortie, EntityManagerInterface $em): Response
    {
        // Change l'état de la sortie à "Ouverte"
        $etat = $em->getRepository(Etat::class)->find(2);
        $sortie->setEtat($etat);
        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('sortie_list');
    }





}


