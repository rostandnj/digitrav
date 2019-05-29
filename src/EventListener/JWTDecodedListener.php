<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 15/3/19
 * Time: 5:16 PM
 */
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTFailureException;

class JWTDecodedListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    private $container;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack,ContainerInterface $c)
    {
        $this->requestStack = $requestStack;
        $this->container = $c;
    }

    /**
     * @param JWTDecodedEvent $event
     *
     * @return void
     */
    public function onJWTDecoded(JWTDecodedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getPayload();

        /*if (!isset($payload['email']) || !isset($payload['id']) || !isset($payload['is_active']) || !isset($payload['is_close'])
            || !isset($payload['is_valid']) ) {
            $event->markAsInvalid();
        }
        else
        {
            if($payload['is_valid']==false) $event->markAsInvalid();
            if($payload['is_active']==false) $event->markAsInvalid();
            if($payload['is_close']==true) $event->markAsInvalid();
        }*/

        $u=$this->container->get("user_manager")->getUserByEmail($payload['email']);

        if(is_null($u)) $event->markAsInvalid();
        elseif ($u->getIsActive()==false) $event->markAsInvalid();
        elseif ($u->getIsClose()==true) $event->markAsInvalid();


    }
}