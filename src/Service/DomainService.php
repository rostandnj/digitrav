<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 13/3/19
 * Time: 2:02 PM
 */
namespace App\Service;




use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\Domain;
use App\Entity\Company;
use App\Entity\User;


class DomainService
{
    private $em;
    private $container;


    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;

    }

   public function create(string $role, array $data)
   {


       if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
           "code"=>401,"data"=>null];

       $req=["name_fr","name_en"];

       foreach ($req as $el)
       {
           if(!array_key_exists($el,$data)) return ["message"=>$el." field is required","statut"=>false,
               "code"=>401,"data"=>null];
       }

       $domain = new Domain();
       $domain->setName($data["name_en"]);
       $domain->setNameEn($data["name_en"]);
       $domain->setNameFr($data["name_fr"]);

       $this->em->persist($domain);
       $this->em->flush();

       return ["message"=>"domain_added","statut"=>true,
           "code"=>201,"data"=>$domain];
   }

    public function edit(string $role, array $data)
    {


        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        if(!array_key_exists("id",$data)) return ["message"=>"id field is required","statut"=>false,
                "code"=>401,"data"=>null];


        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["id"]));

        if(is_null($domain)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        if(array_key_exists("name_en",$data))
        {
            $domain->setName($data["name_en"]);
            $domain->setNameEn($data["name_en"]);
        }

        if(array_key_exists("name_fr",$data))
        {
            $domain->setNameFr($data["name_fr"]);
        }



        $this->em->persist($domain);
        $this->em->flush();

        return ["message"=>"domain_edited","statut"=>true,
            "code"=>201,"data"=>$domain];
    }

   public function showList(int $limit,int $offset)
   {
       $res = $this->em->getRepository(Domain::class)->findBy(array("isActive"=>true),array(),$limit,$offset);

       return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$res];
   }

    public function listTechnicians(int $comp, int $limit,int $offset)
    {
        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("isActive"=>true,"id"=>$comp));

        if(is_null($domain)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];


        $companies = $this->em->getRepository(Company::class)->findCompanyByDomain($domain->getId(),$limit,$offset);

        $users = $this->em->getRepository(User::class)->findUserByDomain($domain->getId(),$limit,$offset);

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>["companies"=>$companies,"users"=>$users,"domain"=>$domain]];
    }

    public function find(int $id)
    {
        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$id));

        if(is_null($domain)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$domain];
    }

    public function showCategories(int $d,int $limit,int $offset)
    {
        $res = $this->em->getRepository(Category::class)->findBy(array("isActive"=>true,"domain"=>$d),array(),$limit,$offset);

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$res];
    }
}