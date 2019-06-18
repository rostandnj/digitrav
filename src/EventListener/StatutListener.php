<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 27/5/19
 * Time: 11:20 AM
 */

namespace App\EventListener;


use App\Entity\Statut;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;


class StatutListener
{


    private $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        // only act on some "Product" entity
        if (!$entity instanceof Statut) {
            return;
        }

        $code = $entity->getNotification()->getCode();

        switch ($code)
        {
            case -1:
                $entity->setMessage($this->container->get('translator')->trans('quotation_invitation_accepted'));
                break;
            case 0:
                $entity->setMessage($this->container->get('translator')->trans('quotation_invitation'));
                break;
            case 1:
                $entity->setMessage($this->container->get('translator')->trans('quotation_new'));
                break;
            case 2:
                $entity->setMessage($this->container->get('translator')->trans('quotation_accepted'));
                break;
            case 3:
                $entity->setMessage($this->container->get('translator')->trans('quotation_refused'));
                break;
            case 4:
                $entity->setMessage($this->container->get('translator')->trans('evaluation_new'));
                break;
            case 5:
                $entity->setMessage($this->container->get('translator')->trans('evaluation_accepted'));
                break;
            case 6:
                $entity->setMessage($this->container->get('translator')->trans('evaluation_refused'));
                break;
            case 7:
                $entity->setMessage($this->container->get('translator')->trans('quotation_ended'));
                break;
            case 8:
                $entity->setMessage($this->container->get('translator')->trans('note_received'));
                break;
            case 9:
                $entity->setMessage($this->container->get('translator')->trans('account_validated'));
                break;
            case 10:
                $entity->setMessage($this->container->get('translator')->trans('account_locked'));
                break;
            case 11:
                $entity->setMessage($this->container->get('translator')->trans('payment_received'));
                break;

        }

        $uid =$this->container->get("hash_service")->encrypt($entity->getId());

        $entity->setUid($uid);
        $entity->setUrl($this->container->get("router")->generate("web_notification_show",["id"=>$uid]));


    }
}