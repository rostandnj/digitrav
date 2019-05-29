<?php

namespace App\Repository;

use App\Entity\Intervention;
use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Intervention|null find($id, $lockMode = null, $lockVersion = null)
 * @method Intervention|null findOneBy(array $criteria, array $orderBy = null)
 * @method Intervention[]    findAll()
 * @method Intervention[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Intervention::class);
    }


    public function findNewJobs($limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->where('i.statut >= :val')
            ->andWhere("i.isActive = :act")
            ->setParameter('val', 0)
            ->setParameter('act', true)
            ->orderBy('i.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }



    public function findByTechnician($id,$limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.quotes','q')
            ->leftJoin('q.technician','t')
            ->where('t.id = :id')
            ->andWhere("i.isActive = :ac")
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy("i.statut","ASC")
            ->setParameter('id', $id)
            ->setParameter('ac', true)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTechnicianDone($id,$limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.quotes','q')
            ->leftJoin('q.technician','t')
            ->where('t.id = :id')
            ->andWhere("i.isActive = :ac")
            ->andWhere("q.statut = :qs")
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy("i.statut","ASC")
            ->setParameter('id', $id)
            ->setParameter('qs', Quote::DONE)
            ->setParameter('ac', true)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findDomainJobs($slug, $limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin("i.domain","d")
            ->where('d.slug = :slug')
            ->andWhere('i.statut >= :val')
            ->andWhere("i.isActive = :ac")
            ->setParameter('val', 0)
            ->setParameter('ac', true)
            ->setParameter('slug', $slug)
            ->orderBy('i.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findCategoryJobs($slug, $limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin("i.category","d")
            ->where('d.slug = :slug')
            ->andWhere('i.statut >= :val')
            ->setParameter('val', 0)
            ->setParameter('slug', $slug)
            ->orderBy('i.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;
    }

    public function countClientJob($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(i.id) as nb FROM App\Entity\Intervention i JOIN i.client c where c.id = ".$id." and i.isActive = 1");
        $objects = $query->getResult();

        return $objects;
    }

    public function findByKey($domain,$city,$key,$limit,$offset)
    {
        return $this->createQueryBuilder('i')
            ->leftJoin("i.domain","d")
            ->leftJoin("i.location","lo")
            ->where('d.id = :did')
            ->andWhere('i.isActive = :active')
            ->andWhere('lo.city LIKE :city')
            ->andWhere('i.title LIKE :key')
            ->setParameter('did', $domain)
            ->setParameter('active', 1)
            ->setParameter('city', "%".$city."%")
            ->setParameter('key', "%".$key."%")
            ->orderBy('i.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
            ;

    }

}