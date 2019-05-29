<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 19/3/19
 * Time: 11:24 AM
 */

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
//use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTFailureEventInterface;

class JWTExpiredListener
{
    /**
     * @param JWTExpiredEvent $event
     */
    public function onJWTExpired(JWTExpiredEvent $event)
    {
        /** @var JWTAuthenticationFailureResponse */
        $response = $event->getResponse();

        $response->setMessage(["message"=>'Your token is expired, please renew it.',"code"=>"expired_token","status"=>"403 forbidden"]);
    }

}