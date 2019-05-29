<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 27/5/19
 * Time: 9:38 AM
 */

namespace App\EventSubscriber;

use App\Controller\LogginInterfaceController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class LogginSubscriber implements EventSubscriberInterface
{
    private $session;

    public function __construct(SessionInterface $s,ContainerInterface $c)
    {
        $this->session = $s;
        $this->mycontainer = $c;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof LogginInterfaceController) {

            $u = $this->mycontainer->get("security.token_storage")->getToken()->getUser();

            if($u!="anon.")
            {


                $notif=$this->mycontainer->get("notification_service")->countNewNotification($u->getId());

                $this->session->set("notif",$notif);
            }



        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

}