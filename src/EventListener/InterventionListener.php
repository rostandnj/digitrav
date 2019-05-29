<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 26/4/19
 * Time: 6:21 PM
 */

namespace App\EventListener;

use App\Entity\Intervention;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use App\Entity\Quote;
use App\Entity\SaveJob;
use App\Entity\JobAlert;

class InterventionListener
{

    private $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container =$c;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();

        // only act on some "Product" entity
        if (!$entity instanceof Intervention) {
            return;
        }

        $entity->setCanAlert(0);
        $entity->setCanApply(0);
        $entity->setCanDelete(0);
        $entity->setCanSave(0);
        $entity->setHasApplied(0);

        $reqType = $this->container->get("request_stack")->getCurrentRequest();

        if(!$reqType->isXmlHttpRequest())
        {


            $cu = $this->container->get("security.token_storage")->getToken()->getUser();

            if(!is_null($cu))
            {

                $quote = $em->getRepository(Quote::class)->findOneBy(array("intervention"=>$entity,"technician"=>$cu));
                if(is_null($quote)) $entity->setCanApply(true);
                else{
                    if($quote->getTechnician()->getId() == $cu->getId())
                    {
                        $entity->setHasApplied(1);
                    }
                }

                $save = $em->getRepository(SaveJob::class)->findOneBy(array("intervention"=>$entity,"technician"=>$cu));

                if(is_null($save)) $entity->setCanSave(true);

                $alert = $em->getRepository(JobAlert::class)->findOneBy(array("intervention"=>$entity,"user"=>$cu));

                if(is_null($alert)) $entity->setCanAlert(true);

                if(!in_array($entity->getStatut(),[Intervention::PAID,Intervention::DONE]))
                {
                    $entity->setCanDelete(true);
                }







            }
        }







        // ... do something with the Product

    }

}