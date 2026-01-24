<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }

    //    /**
    //     * @return Contrat[] Returns an array of Contrat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    /**
     * Récupère le contrat actif (non expiré) de l'utilisateur
     */
    public function findActiveContractForUser($user): ?Contrat
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :user')
            ->andWhere('c.dateDebut <= :today')
            ->andWhere('c.dateFin IS NULL OR c.dateFin >= :today')
            ->setParameter('user', $user)
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
