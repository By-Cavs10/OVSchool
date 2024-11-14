<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Helper\FileUploader;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'user')]
class UserController extends AbstractController
{
    #[Route('/list', name: '_list')]
    #[IsGranted('ROLE_ADMIN')]
    public function list(EntityManagerInterface $entityManager): Response
    {

        $userRepository = $entityManager->getRepository(User::class);

        $users = $userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/{id}', name: '_detail', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function detail(int $id, EntityManagerInterface $entityManager): Response
    {
        $userRepository = $entityManager->getRepository(User::class);

        $user = $userRepository->find($id);

        if(!$user){
            throw $this ->createNotFoundException();
        }

        return $this->render('user/detail.html.twig', [
            'user'=>$user
        ]);
    }

    #[Route('/create', name: '_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if ($this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Accès refusé - impossible de créér de nouveau un compte pour un User non administrateur');
        }

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
                ]);
            }

            $profilFile = $form->get('photoProfilFichier')->getData();

            if($profilFile instanceof UploadedFile){
                $profileName = $fileUploader->uploadPhoto($profilFile, $user->getPseudo(), 'upload_directory_users');
                $user->setUrlPhoto(strtolower($profileName));
            }

            // Hasher le password avant de le rentrer en BDD
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            if (!$this->isGranted('ROLE_ADMIN')) {
                $user->setAdministrateur('false');
                $user->setActif('true');
            }

            //Définir le rôle
            $roles=['ROLE_USER'];

            if ($user->isAdministrateur()){
                $roles[] = 'ROLE_ADMIN';
            }
            $user->setRoles($roles);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $this->addFlash('success', 'Inscription du profil de "'.$user->getPseudo().'" réussie');

            return $this->redirectToRoute('main_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/{id}/update', name: '_update', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser()->getId() != $id) {
            throw $this->createAccessDeniedException('Accès refusé - impossible de modifier ce compte User');
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Cet Utilisateur n'existe pas.");
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPlainPassword = $form->get('confirmPlainPassword')->getData();

            //Vérification de correspondance
            if ($plainPassword !== $confirmPlainPassword) {
                $this->addFlash('danger', 'Les mots de passe doivent correspondre.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $profilFile = $form->get('photoProfilFichier')->getData();

            if ($profilFile instanceof UploadedFile) {
                $profileName = $fileUploader->uploadPhoto($profilFile, $user->getPseudo(), 'upload_directory_users');
                $user->setUrlPhoto(strtolower($profileName));
            }

            // Hasher le password avant de le rentrer en BDD
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            //Définir le rôle
            $roles = ['ROLE_USER'];

            if ($user->isAdministrateur()) {
                $roles[] = 'ROLE_ADMIN';
            }
            $user->setRoles($roles);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $this->addFlash('success', 'Mise à jour du profil de "'.$user->getPseudo().'" réussie');

            return $this->redirectToRoute('user_detail', ['id' => $user->getId()]);
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: '_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Cet Utilisateur n'existe pas.");
        }

        if($this->isCsrfTokenValid('delete'.$id, $request->query->get('token'))){
            if ($userRepository->doesUserIsOrganisateur($user)){
                $user->setActif(false);
                $entityManager->persist($user);
            } else {
                $entityManager->remove($user);
            }
                $entityManager->flush();
            $this->addFlash('success', 'Suppression du profil de "'.$user->getPseudo().'" réussie');
        } else {
            $this->addFlash('success', 'Vous n\'avez pas les autorisations pour supprimer "'.$user->getPseudo().'"');
        }

        return  $this->redirectToRoute('main_home');
    }
}
