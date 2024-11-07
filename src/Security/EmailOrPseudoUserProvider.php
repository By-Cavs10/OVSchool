<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class EmailOrPseudoUserProvider implements UserProviderInterface
{

    public function __construct(private EntityManagerInterface $entityManager){}

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->entityManager->getRepository(User::class)->find($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $identifier])
            ?? $userRepository->findOneBy(['pseudo' => $identifier]);

        if (!$user){
            throw new UserNotFoundException("Utilisateur inexistant");
        }

        return $user;
    }
}