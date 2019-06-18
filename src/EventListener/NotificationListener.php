<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 12/6/19
 * Time: 7:09 PM
 */

namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Notification;

class NotificationListener
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
        if (!$entity instanceof Notification) {
            return;
        }





        //$entityManager = $args->getObjectManager();


    }

}