<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 17/4/19
 * Time: 5:05 PM
 */

namespace App\Service;


use App\Entity\SubCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\Domain;
use App\Entity\Category;

class CategoryService
{
    private $em;
    private $container;


    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;

    }

    public function create(string $role,array $data)
    {
        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        $req=["name_fr","name_en","domain","budget"];

        foreach ($req as $el)
        {
            if(!array_key_exists($el,$data)) return ["message"=>$el." field is required","statut"=>false,
                "code"=>401,"data"=>null];
        }

        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["domain"]));

        if(is_null($domain)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $m= new Category();
        $m->setNameFr($data["name_fr"]);
        $m->setNameEn($data["name_en"]);
        $m->setName($data["name_en"]);
        $m->setDomain($domain);
        $m->setBudget($data["budget"]);

        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"category_added","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function edit(string $role, array $data)
    {


        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        if(!array_key_exists("id",$data)) return ["message"=>"id field is required","statut"=>false,
            "code"=>401,"data"=>null];


        $m = $this->em->getRepository(Category::class)->findOneBy(array("id"=>$data["id"]));

        if(is_null($m)) return ["message"=>"category_not_found","statut"=>false,"code"=>404,"data"=>null];

        if(array_key_exists("name_en",$data))
        {
            $m->setName($data["name_en"]);
            $m->setNameEn($data["name_en"]);
        }

        if(array_key_exists("name_fr",$data))
        {
            $m->setNameFr($data["name_fr"]);
        }

        if(array_key_exists("budget",$data))
        {
            $m->setBudget($data["budget"]);
        }



        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"category_edited","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function showList(int $limit,int $offset)
    {
        $res = $this->em->getRepository(Category::class)->findBy(array("isActive"=>true),array(),$limit,$offset);

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$res];
    }

    public function showDomain(int $id)
    {
        $d = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$id));

        if(is_null($d)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq = $this->em->getRepository(Category::class)->findBy(array("domain"=>$d,"isActive"=>true));

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$mq];
    }

    public function removeFromDomain(string $role,array $data)
    {

        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        //$do = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["domain"],"isActive"=>true));

        // if(is_null($do)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq = $this->em->getRepository(Category::class)->findOneBy(array("id"=>$data["id"],"isActive"=>true));

        if(is_null($mq)) return ["message"=>"category_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq->setIsActive(false);

        $this->em->persist($mq);
        $this->em->flush();

        return ["message"=>"operation_did","statut"=>true,"code"=>201,"data"=>$mq];
    }

    public function find(int $id)
    {
        $m = $this->em->getRepository(Category::class)->findOneBy(array("id"=>$id));

        if(is_null($m)) return ["message"=>"category_not_found","statut"=>false,"code"=>404,"data"=>null];

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$m];
    }

    public function findByDomain(int $id)
    {
        $m = $this->em->getRepository(Category::class)->findBy(array("domain"=>$id));



        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$m];
    }

    public function showSubCategories(int $d,int $limit,int $offset)
    {
        $res = $this->em->getRepository(SubCategory::class)->findBy(array("isActive"=>true,"category"=>$d),array(),$limit,$offset);

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$res];
    }

    public function createSub(string $role,array $data)
    {
        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        $req=["name_fr","name_en","category","budget"];

        foreach ($req as $el)
        {
            if(!array_key_exists($el,$data)) return ["message"=>$el." field is required","statut"=>false,
                "code"=>401,"data"=>null];
        }

        $cat = $this->em->getRepository(Category::class)->findOneBy(array("id"=>$data["category"]));

        if(is_null($cat)) return ["message"=>"category_not_found","statut"=>false,"code"=>404,"data"=>null];

        $m= new SubCategory();
        $m->setNameFr($data["name_fr"]);
        $m->setNameEn($data["name_en"]);
        $m->setName($data["name_en"]);
        $m->setCategory($cat);
        $m->setBudget($data["budget"]);

        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"sub_category_added","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function editSub(string $role, array $data)
    {


        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        if(!array_key_exists("id",$data)) return ["message"=>"id field is required","statut"=>false,
            "code"=>401,"data"=>null];


        $m = $this->em->getRepository(SubCategory::class)->findOneBy(array("id"=>$data["id"]));

        if(is_null($m)) return ["message"=>"sub_category_not_found","statut"=>false,"code"=>404,"data"=>null];

        if(array_key_exists("name_en",$data))
        {
            $m->setName($data["name_en"]);
            $m->setNameEn($data["name_en"]);
        }

        if(array_key_exists("name_fr",$data))
        {
            $m->setNameFr($data["name_fr"]);
        }

        if(array_key_exists("budget",$data))
        {
            $m->setBudget($data["budget"]);
        }



        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"sub_category_edited","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function findSub(int $id)
    {
        $m = $this->em->getRepository(SubCategory::class)->findOneBy(array("id"=>$id));

        if(is_null($m)) return ["message"=>"sub_category_not_found","statut"=>false,"code"=>404,"data"=>null];

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$m];
    }

    public function removeSubCategory(string $role,array $data)
    {

        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];


        $mq = $this->em->getRepository(SubCategory::class)->findOneBy(array("id"=>$data["id"],"isActive"=>true));

        if(is_null($mq)) return ["message"=>"sub_category_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq->setIsActive(false);

        $this->em->persist($mq);
        $this->em->flush();

        return ["message"=>"operation_did","statut"=>true,"code"=>201,"data"=>$mq];
    }

    public function findSubByCategory(int $id)
    {
        $m = $this->em->getRepository(SubCategory::class)->findBy(array("category"=>$id));

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$m];
    }

}