<?php

namespace App\Repository;

use App\Entity\Lost;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Animal;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lost|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lost|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lost[]    findAll()
 * @method Lost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lost::class);
    }

     /**
      * @return Lost[] Returns an array of Lost objects
      */
    
      public function findWithCriteria($criteria = null,  array $orderBy = null, $limit = null, $offset = null)
      {
          $query =  $this->createQueryBuilder('a');
         
          if (count($criteria) > 0) {
              if (isset($criteria['espece']) || isset($criteria['type']) || isset($criteria['taille']) || isset($criteria['sexe'])) {
                  $query->innerJoin(
                      Animal::class,
                      'b',
                      'WITH',
                      'b.id = a.animal'
                  );
                  if (isset($criteria['espece'])) {
                      $query->andWhere('b.espece = :espece')
                          ->setParameter('espece', $criteria['espece']);
                  }
                  if (isset($criteria['type'])) {
                      $query->andWhere('b.type = :type')
                          ->setParameter('type', $criteria['type']);
                  }
                  if (isset($criteria['taille'])) {
                      $query->andWhere('b.taille = :taille')
                          ->setParameter('taille', $criteria['taille']);
                  }
                  if (isset($criteria['sexe'])) {
                      $query->andWhere('b.sexe = :sexe')
                          ->setParameter('sexe', $criteria['sexe']);
                  }
              }
              if (isset($criteria['ville']) || isset($criteria['municipality'])) {
                  $query->innerJoin(
                      User::class,
                      'c',
                      'WITH',
                      'c.id = a.user'
                  );
                  $query->innerJoin(
                      Address::class,
                      'd',
                      'WITH',
                      'd.id = c.address'
                  );
                  if (isset($criteria['ville'])) {
                      $query->andWhere('d.ville = :ville')
                          ->setParameter('ville', $criteria['ville']);
                  }
                  if (isset($criteria['municipality'])) {
                      $query->andWhere('d.municipality = :municipality')
                          ->setParameter('municipality', $criteria['municipality']);
                  }
              }
              $query->select('a');
              if (isset($criteria['user_id'])) {
                  $query->andWhere('a.user = :user')
                      ->setParameter('user', $criteria['user_id']);
              }
          }
          return  $query->orderBy('a.id', 'DESC')
              ->setFirstResult($offset)
              ->setMaxResults($limit)
              ->getQuery()
              ->getResult();
      }
  

    /*
    public function findOneBySomeField($value): ?Lost
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Lost[] Returns an array of Adoption objects
     */
    public function findPaged($page, $size)
    {
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $size;
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }
}
