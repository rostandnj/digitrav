<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 19/3/19
 * Time: 11:19 AM
 */

namespace App\EventListener;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;

class AuthenticationFailureListener
{
    /**
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'status'  => '401 Unauthorized',
            'message' => 'Bad credentials, please verify that your username/password are correctly set',
            'code'=>'bad_login'
        ];

        $response = new JWTAuthenticationFailureResponse($data);

        $event->setResponse($response);
    }

}