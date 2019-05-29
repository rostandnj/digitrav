<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 16/4/19
 * Time: 11:26 AM
 */

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Domain;

class DomainListener
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
        if (!$entity instanceof Domain) {
            return;
        }

        $get ="getName".ucfirst($this->container->get("request_stack")->getCurrentRequest()->cookies->get("lang"));



        $entity->setName($entity->$get());



        $entityManager = $args->getObjectManager();
        // ... do something with the Product

    }

}