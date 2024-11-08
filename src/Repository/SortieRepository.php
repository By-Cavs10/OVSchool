<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function userSubscribe (Sortie $sortie, User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $checkSql = 'SELECT COUNT(*) FROM sortie_user WHERE user_id = :userId AND sortie_id = :idSortie';
        $count = $conn->executeQuery($checkSql, [
            'userId' => $user->getId(),
            'idSortie' => $sortie->getId(),
        ])->fetchOne();

        if ($count == 0) {
            $insertSql = 'INSERT INTO sortie_user (user_id, sortie_id) VALUES (:userId, :idSortie)';
            $conn->executeQuery($insertSql, [
                'userId' => $user->getId(),
                'idSortie' => $sortie->getId(),
            ]);
            return [
                'label' => 'success',
                'message' => 'Inscription terminée - '.$user->getPseudo().' est bien inscrit-e à la sortie "'.$sortie->getNom().'".',
            ];
        } else {
            return [
                'label' => 'warning',
                'message' => 'Réinscription impossible - '.$user->getPseudo().' est déjà inscrit-e à la sortie "'.$sortie->getNom().'".',
            ];
        }
    }

    public function userUnsubscribe (Sortie $sortie, User $user): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $checkSql = 'SELECT COUNT(*) FROM sortie_user WHERE user_id = :userId AND sortie_id = :idSortie';
        $count = $conn->executeQuery($checkSql, [
            'userId' => $user->getId(),
            'idSortie' => $sortie->getId(),
        ])->fetchOne();

        if ($count === 1) {
            $insertSql = 'DELETE FROM sortie_user WHERE user_id = :userId AND sortie_id = :idSortie;';
            $conn->executeQuery($insertSql, [
                'userId' => $user->getId(),
                'idSortie' => $sortie->getId(),
            ]);
            return [
                'label' => 'success',
                'message' => 'Désinscription terminée - '.$user->getPseudo().' est bien désinscrit-e de la sortie "'.$sortie->getNom().'".',
            ];
        } else {
            return [
                'label' => 'warning',
                'message' => 'Désinscription impossible - '.$user->getPseudo().' n\'est pas dans la liste des participant-e-s de la sortie "'.$sortie->getNom().'".',
            ];
        }
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sortie
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
