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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


use App\Entity\Statut;
use App\Entity\Notification;
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
            return ["message"=>"notification_not_found","code"=>401,"statut"=>true,"data"=>[]];
        }
        else
        {
            $url="";

            if($res->getStatut()==false)
            {

                $res->setStatut(true);

                $this->em->persist($res);

                $this->em->flush();

                $nb = $this->container->get("session")->get("nofif");
                if($nb>0) $this->container->get("session")->set("nofif",$nb-1);
            }

            $entity=$res->getNotification();

            switch ($res->getNotification()->getCode())
            {
                case Notification::QUOTATION_INVITATION_ACCEPTED:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;

                case Notification::QUOTATION_INVITATION:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case Notification::QUOTATION_NEW:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case Notification::EVALUATION_ACCEPTED:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case Notification::QUOTATION_ENDED:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL); break;
                case Notification::QUOTATION_ACCEPTED:
                    $url=$this->container->get("router")->generate("web_show_job",["slug"=>$entity->getQuote()->getIntervention()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case Notification::ACCOUNT_VALIDATED:
                    $cu = $this->container->get("security.token_storage")->getToken()->getUser();
                    $url=$this->container->get("router")->generate("web_profile",["name"=>$cu->getProfileName(),"id"=>$cu->getUid()], UrlGeneratorInterface::ABSOLUTE_URL);

                    break;

                case Notification::NOTE_RECEIVED:
                    $cu = $this->container->get("security.token_storage")->getToken()->getUser();
                    $url=$this->container->get("router")->generate("web_profile",["name"=>$cu->getProfileName(),"id"=>$cu->getUid()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;

                case Notification::ACCOUNT_LOCKED:
                    $cu = $this->container->get("security.token_storage")->getToken()->getUser();
                    $url=$this->container->get("router")->generate("web_profile",["name"=>$cu->getProfileName(),"id"=>$cu->getUid()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case Notification::PAYMENT_RECEIVED:
                    $cu = $this->container->get("security.token_storage")->getToken()->getUser();
                    $url=$this->container->get("router")->generate("web_profile",["name"=>$cu->getProfileName(),"id"=>$cu->getUid()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                default:
                    break;


            }

            return ["message"=>"","code"=>201,"statut"=>false,"data"=>["statut"=>$res,"url"=>$url]];
        }


    }

    public function countAllNotification(int $userid)
    {
        $res = $this->em->getRepository(Statut::class)->countAllNotif($userid);

        return $res[0]["nb"];

    }


}