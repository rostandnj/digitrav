<?php

namespace App\Repository;

use App\Entity\Statut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Statut|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statut|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statut[]    findAll()
 * @method Statut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Statut::class);
    }


    public function countNotif($userid)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.user',"u")
            ->where('u.id = :uid')
            ->andWhere('s.statut = :sta')
            ->andWhere('s.isActive = :act')
            ->setParameter('uid', $userid)
            ->setParameter('sta', 0)
            ->setParameter('act', 1)
            ->select("count(s.id) as nb")
            ->getQuery()
            ->getResult()
        ;
    }

    public function countAllNotif($userid)
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.user',"u")
            ->where('u.id = :uid')
            ->andWhere('s.isActive = :act')
            ->setParameter('uid', $userid)
            ->setParameter('act', 1)
            ->select("count(s.id) as nb")
            ->getQuery()
            ->getResult()
            ;
    }



}
