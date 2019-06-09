<?php

namespace App\Repository;

use App\Entity\ToolQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ToolQuote|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolQuote|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolQuote[]    findAll()
 * @method ToolQuote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolQuoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ToolQuote::class);
    }

    // /**
    //  * @return ToolQuote[] Returns an array of ToolQuote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ToolQuote
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
