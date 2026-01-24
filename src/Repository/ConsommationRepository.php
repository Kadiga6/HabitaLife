<?php

namespace App\Repository;

use App\Entity\Consommation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consommation>
 */
class ConsommationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consommation::class);
    }

    //    /**
    //     * @return Consommation[] Returns an array of Consommation objects
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
     * Récupère les totaux de consommation par type pour un logement donné
     * @return array ['electricite' => float, 'eau' => float, 'gaz' => float]
     */
    public function getTotauxByLogement($logement): array
    {
        $qb = $this->createQueryBuilder('c');
        $results = $qb
            ->select('c.type, SUM(c.valeur) as total')
            ->andWhere('c.logement = :logement')
            ->setParameter('logement', $logement)
            ->groupBy('c.type')
            ->getQuery()
            ->getResult();

        // Transformer le résultat en tableau associatif
        $totaux = [
            'electricite' => 0,
            'eau' => 0,
            'gaz' => 0,
        ];

        foreach ($results as $row) {
            $type = strtolower($row['type']);
            if (isset($totaux[$type])) {
                $totaux[$type] = (float) $row['total'];
            }
        }

        return $totaux;
    }
}
