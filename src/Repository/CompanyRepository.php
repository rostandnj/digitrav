<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function findCompanyByDomain($id,$limit,$offset)
    {
        return $this->createQueryBuilder('c')
            ->leftJoin("c.domains","d")
            ->where("d.id = :do")
            ->andWhere('c.isActive = :ac')
            ->andWhere('c.isValid = :ac')
            ->orderBy('c.note','DESC')
            ->setParameter('do', $id)
            ->setParameter('ac', true)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function countCompany()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(c.id) as nb FROM App\Entity\Company c");
        $objects = $query->getResult();

        return $objects;
    }
}
