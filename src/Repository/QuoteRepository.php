<?php

namespace App\Repository;

use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Quote::class);
    }


    public function findByJob($id,$limit,$offset)
    {
        return $this->createQueryBuilder('q')
            ->leftJoin('q.intervention','int')
            ->where('int.id = :id')
            ->andWhere('q.statut IN (:tab)')
            ->setParameter('id', $id)
            ->setParameter('tab', [Quote::ACCEPTED,Quote::SENDBYTECHNICIAN,Quote::DONE,Quote::PAID,Quote::VALIDATED,Quote::ACCEPTEDBYTECHNICIAN])
            ->orderBy("q.date","DESC")
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?Quote
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
