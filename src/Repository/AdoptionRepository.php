<?php

namespace App\Repository;

use App\Entity\Adoption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Adoption|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adoption|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adoption[]    findAll()
 * @method Adoption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Adoption[]    findPaged($page, $size)
 */
class AdoptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adoption::class);
    }

    // /**
    //  * @return Adoption[] Returns an array of Adoption objects
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
    /**
     * @return Adoption[] Returns an array of Adoption objects
     */
    public function findPaged($page, $size)
    {
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $size;
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Adoption
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
