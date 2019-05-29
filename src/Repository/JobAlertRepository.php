<?php

namespace App\Repository;

use App\Entity\JobAlert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method JobAlert|null find($id, $lockMode = null, $lockVersion = null)
 * @method JobAlert|null findOneBy(array $criteria, array $orderBy = null)
 * @method JobAlert[]    findAll()
 * @method JobAlert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JobAlertRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, JobAlert::class);
    }


    public function getSpecific($id)
    {

        return $this->createQueryBuilder('j')
            ->leftJoin("j.intervention","i")
            ->where('i.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

}
