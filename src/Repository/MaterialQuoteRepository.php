<?php

namespace App\Repository;

use App\Entity\MaterialQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MaterialQuote|null find($id, $lockMode = null, $lockVersion = null)
 * @method MaterialQuote|null findOneBy(array $criteria, array $orderBy = null)
 * @method MaterialQuote[]    findAll()
 * @method MaterialQuote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaterialQuoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MaterialQuote::class);
    }

    // /**
    //  * @return MaterialQuote[] Returns an array of MaterialQuote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MaterialQuote
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
