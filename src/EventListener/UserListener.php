<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 15/4/19
 * Time: 4:01 PM
 */

namespace App\EventListener;


use App\Entity\Company;
use App\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;

class UserListener
{

    private $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container =$c;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // only act on some "Product" entity
        if (!$entity instanceof User) {
            return;
        }

        if($entity->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
        {
            //$entity->getCompany();

        }

        if(is_null($entity->getPicture()))
        {
            $pic = new File();

            if($entity->getGender()==true)
            {
                $pic->setName("man.png");
                $pic->setPath("man.png");
                $pic->setSize(12578);
                $pic->setExtension(".png");
                $pic->setType("image/png");
            }
            else
            {
                $pic->setName("woman.png");
                $pic->setPath("woman.png");
                $pic->setSize(12578);
                $pic->setExtension(".png");
                $pic->setType("image/png");
            }


            $entity->setPicture($pic);

        }

        $entity->setUid($this->container->get("hash_service")->encrypt($entity->getId()));

        $entity->setUid($this->container->get("hash_service")->encrypt($entity->getId()));




        // ... do something with the Product

    }
}