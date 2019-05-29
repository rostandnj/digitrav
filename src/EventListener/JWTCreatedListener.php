<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 15/3/19
 * Time: 5:16 PM
 */
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;


class JWTCreatedListener
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
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {

        $user = $event->getUser();

        $payload = $event->getData();
        $payload["id"]=$this->container->get("hash_service")->encrypt($user->getId());
        $payload["email"]=$user->getEmail();
        $payload["gender"]=$user->getGender();
        $payload["name"]=$user->getName();
        $payload["picture"]=$user->toArray()["picture"];
        $payload["profile_name"]=$user->getProfileName();
        //$payload["is_active"]=$user->getIsActive();
        //$payload["is_close"]=$user->getIsClose();
        //$payload["is_valid"]=$user->getIsValid();


        $event->setData($payload);

        $header        = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);



    }
}