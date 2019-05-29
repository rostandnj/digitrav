<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 16/4/19
 * Time: 10:24 AM
 */

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\Domain;
use App\Entity\MaterialQuote;
use App\Entity\User;

class MaterialQuoteService
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

        $req=["name_fr","name_en","domain"];

        foreach ($req as $el)
        {
            if(!array_key_exists($el,$data)) return ["message"=>$el." field is required","statut"=>false,
                "code"=>401,"data"=>null];
        }

        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["domain"]));

        if(is_null($domain)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $m= new MaterialQuote();
        $m->setNameFr($data["name_fr"]);
        $m->setNameEn($data["name_en"]);
        $m->setName($data["name_en"]);
        $m->setDomain($domain);

        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"material_quote_added","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function edit(string $role, array $data)
    {


        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        if(!array_key_exists("id",$data)) return ["message"=>"id field is required","statut"=>false,
            "code"=>401,"data"=>null];


        $m = $this->em->getRepository(MaterialQuote::class)->findOneBy(array("id"=>$data["id"]));

        if(is_null($m)) return ["message"=>"material_quote_not_found","statut"=>false,"code"=>404,"data"=>null];

        if(array_key_exists("name_en",$data))
        {
            $m->setName($data["name_en"]);
            $m->setNameEn($data["name_en"]);
        }

        if(array_key_exists("name_fr",$data))
        {
            $m->setNameFr($data["name_fr"]);
        }



        $this->em->persist($m);
        $this->em->flush();

        return ["message"=>"material_quote_edited","statut"=>true,
            "code"=>201,"data"=>$m];
    }

    public function showList(int $limit,int $offset)
    {
        $res = $this->em->getRepository(MaterialQuote::class)->findBy(array("isActive"=>true),array(),$limit,$offset);

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$res];
    }

    public function showDomain(int $id)
    {
        $d = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$id));

        if(is_null($d)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq = $this->em->getRepository(MaterialQuote::class)->findBy(array("domain"=>$d,"isActive"=>true));

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$mq];
    }

    public function removeFromDomain(string $role,array $data)
    {

        if(!in_array($role,["ROLE_SADMIN","ROLE_ADMIN"])) return ["message"=>"operation_denied","statut"=>false,
            "code"=>401,"data"=>null];

        //$do = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["domain"],"isActive"=>true));

       // if(is_null($do)) return ["message"=>"domain_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq = $this->em->getRepository(MaterialQuote::class)->findOneBy(array("id"=>$data["id"],"isActive"=>true));

        if(is_null($mq)) return ["message"=>"material_quote_not_found","statut"=>false,"code"=>404,"data"=>null];

        $mq->setIsActive(false);

        $this->em->persist($mq);
        $this->em->flush();

        return ["message"=>"operation_did","statut"=>true,"code"=>201,"data"=>$mq];
    }

    public function find(int $id)
    {
        $m = $this->em->getRepository(MaterialQuote::class)->findOneBy(array("id"=>$id));

        if(is_null($m)) return ["message"=>"materila_quote_not_found","statut"=>false,"code"=>404,"data"=>null];

        return ["message"=>" ","statut"=>true,"code"=>201,"data"=>$m];
    }

}