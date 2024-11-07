<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
//    #[Route('/creer-compte', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPlainPassword = $form->get('confirmPlainPassword')->getData();

            //Vérification de correspondance
            if($plainPassword !== $confirmPlainPassword){
                $this->addFlash('danger', 'Les mots de passe doivent correspondre.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
//                    ou $form->createView()
                ]);
            }

            $profilFile = $form->get('photo_profil_fichier')->getData();

            if($profilFile instanceof UploadedFile){
                $profileName = $fileUploader->uploadPhoto($profilFile, $user->getPseudo(), 'upload_directory_users');
                $user->setUrlPhoto(strtolower($profileName));
            }

            // Hasher le password avant de le rentrer en BDD
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            //Définir le rôle
            $roles=['ROLE_USER'];

            if ($user->isAdministrateur()){
                $roles[] = 'ROLE_ADMIN';
            }
            $user->setRoles($roles);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $this->addFlash('success', 'Inscription réussie : Bienvenue '.$user->getPseudo().' sur le site OVSChool!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
