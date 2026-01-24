<?php

namespace App\Repository;

use App\Entity\Paiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Paiement>
 */
class PaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Paiement::class);
    }

    /**
     * Récupère tous les paiements pour les contrats spécifiés
     * @param array $contratIds Liste des IDs des contrats
     * @return Paiement[]
     */
    public function findByContratIds(array $contratIds): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.contrat IN (:contratIds)')
            ->setParameter('contratIds', $contratIds)
            ->orderBy('p.periode', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère les paiements en attente pour les contrats spécifiés
     * @param array $contratIds Liste des IDs des contrats
     * @return Paiement[]
     */
    public function findPendingPayments(array $contratIds): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.contrat IN (:contratIds)')
            ->andWhere('p.statut IN (:statuts)')
            ->setParameter('contratIds', $contratIds)
            ->setParameter('statuts', ['en_attente', 'en_retard'])
            ->orderBy('p.periode', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
