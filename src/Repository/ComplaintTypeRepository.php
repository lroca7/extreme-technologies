<?php

namespace App\Repository;

use App\Entity\ComplaintType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ComplaintType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComplaintType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComplaintType[]    findAll()
 * @method ComplaintType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComplaintTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComplaintType::class);
    }

    // /**
    //  * @return ComplaintType[] Returns an array of ComplaintType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ComplaintType
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
