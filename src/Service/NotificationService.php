<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 24/5/19
 * Time: 6:34 PM
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


use App\Entity\Statut;
use App\Entity\User;

class NotificationService
{
    private $em;
    private $container;


    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;

    }

    public function getMyNotification(User $user,int $limit,int $offset)
    {
        $data = $this->em->getRepository(Statut::class)->findBy(array("user"=>$user,"isActive"=>true),array("date"=>"DESC"),$limit,$offset);

        return ["message"=>"","code"=>201,"statut"=>false,"data"=>$data];
    }

    public function countNewNotification(int $userid)
    {
        $res = $this->em->getRepository(Statut::class)->countNotif($userid);

        return $res[0]["nb"];

    }

    public function getOne(int $id)
    {
        $res = $this->em->getRepository(Statut::class)->findOneBy(array("id"=>$id));

        if(is_null($res))
        {
            return ["message"=>"","code"=>401,"statut"=>true,"data"=>[]];
        }
        else
        {
            if($res->getStatut()==false)
            {

                $res->setStatut(true);

                $this->em->persist($res);

                $this->em->flush();

                $nb = $this->container->get("session")->get("nofif");
                if($nb>0) $this->container->get("session")->set("nofif",$nb-1);
            }
            return ["message"=>"","code"=>201,"statut"=>false,"data"=>$res];
        }


    }

    public function countAllNotification(int $userid)
    {
        $res = $this->em->getRepository(Statut::class)->countAllNotif($userid);

        return $res[0]["nb"];

    }

}