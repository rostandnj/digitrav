<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 25/4/19
 * Time: 3:05 PM
 */

namespace App\EventListener;

use App\Entity\Company;
use App\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\User;

class CompanyListener
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
        if (!$entity instanceof Company) {
            return;
        }


        if(is_null($entity->getLogo()))
        {
            $pic = new File();


                $pic->setName("brand.png");
                $pic->setPath("brand.png");
                $pic->setSize(12578);
                $pic->setExtension(".png");
                $pic->setType("image/png");
                $pic->setDate(new \DateTime());


            $entity->setLogo($pic);

        }




        // ... do something with the Product

    }

}