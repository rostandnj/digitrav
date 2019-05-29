<?php

namespace App\Repository;

use App\Entity\SaveJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SaveJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method SaveJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method SaveJob[]    findAll()
 * @method SaveJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaveJobRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SaveJob::class);
    }


    public function findByTechnician($id,$limit,$offset)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.technician',"tech")
            ->leftJoin('s.intervention',"int")
            ->where('tech.id = :id')
            ->andWhere('int.isActive = :val')
            ->setParameter('val', true)
            ->setParameter('id', $id)
            ->orderBy('s.date', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }


    /*
    public function findOneBySomeField($value): ?SaveJob
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
