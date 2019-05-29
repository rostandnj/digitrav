<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername($usernameOrEmail)
    {

        return $this->createQueryBuilder('u')
            ->where('u.phone = :query OR u.email = :query')
            //->having("isClose = :close")
            //->andHaving("isActive = :active")
            //->andHaving("isValid = :active")
            ->setParameter('query', $usernameOrEmail)
            //->setParameter('active', 1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function loadAllUser(array $roles,int $limit,int $offset)
    {
        $roles[]="ROLE_SYSTEM";



        return $this->createQueryBuilder('u')
            ->leftJoin("u.role","role")
            ->where('role.code not in (:tab)')
            //->having("isClose = :close")
            //->andHaving("isActive = :active")
            //->andHaving("isValid = :active")
            ->setParameter('tab', $roles)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            //->setParameter('active', 1)
            ->getQuery()
            ->getResult();
    }

    public function findUserByDomain($id,$limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin("u.userDetail","d")
            ->leftJoin("d.company","com")
            ->where("com.id = :id")
            ->andWhere('com.isActive = :ac')
            ->setParameter('id', $id)
            ->setParameter('ac', true)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByDomain($id,$limit,$offset)
    {
        return $this->createQueryBuilder('u')
            ->leftJoin("u.userDetail","ud")
            ->leftJoin("ud.domains","domains")
            ->where("domains.id = :id")
            ->andWhere('u.isActive = :ac')
            ->andWhere('u.isValid = :ac')
            ->andWhere('u.isClose = :cl')
            ->orderBy("ud.note","DESC")
            ->setParameter('id', $id)
            ->setParameter('ac', true)
            ->setParameter('cl', false)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
            ;
    }

    public function countClient()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(u.id) as nb FROM App\Entity\User u  JOIN u.role r WHERE r.code = 'ROLE_CLIENT'");
        $objects = $query->getResult();

        return $objects;
    }

    public function countTechnician()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT count(u.id) as nb FROM App\Entity\User u  JOIN u.role r WHERE r.code IN ('ROLE_TECHNICIAN_PERSON','ROLE_TECHNICIAN_COMPANY')");
        $objects = $query->getResult();

        return $objects;
    }

    public function findTechnicianPerson($did,$city,$limit,$offset)
    {

        return $this->createQueryBuilder('u')
            ->leftJoin("u.role","role")
            ->leftJoin("u.userDetail","uD")
            ->leftJoin("u.location","uL")
            ->leftJoin("uD.domains","uDomain")
            ->where('role.code IN (:role)')
            //->andWhere("u.isValid = :active")
            //->andWhere("uD.isValid = :active")
            ->andWhere("uL.city LIKE :city")
            ->andWhere("uDomain.id IN (:do)")
            ->setParameter('role',["ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON"])
            //->setParameter('active', 1)
            ->setParameter('city', "%".$city."%")
            ->setParameter('do', [$did])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();


    }

    public function findTechnicianCompany($did,$city,$limit,$offset)
    {

        return $this->createQueryBuilder('u')
            ->leftJoin("u.role","role")
            ->leftJoin("u.company","comp")
            ->leftJoin("comp.location","cL")
            ->leftJoin("comp.domains","cDomain")
            ->where('role.code = :role')
            ->andWhere("u.isValid = :active")
            ->andWhere("comp.isValid = :active")
            ->andWhere("cL.city LIKE :city")
            ->andWhere("cDomain.id IN (:do)")
            ->setParameter('role',"ROLE_MANAGER_COMPANY")
            ->setParameter('active', 1)
            ->setParameter('city', "%".$city."%")
            ->setParameter('do', [$did])
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


}
