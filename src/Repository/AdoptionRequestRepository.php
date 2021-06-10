<?php

namespace App\Repository;

use App\Entity\AdoptionRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AdoptionRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdoptionRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdoptionRequest[]    findAll()
 * @method AdoptionRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdoptionRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdoptionRequest::class);
    }

    // /**
    //  * @return AdoptionRequest[] Returns an array of AdoptionRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AdoptionRequest
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
