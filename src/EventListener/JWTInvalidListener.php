<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 19/3/19
 * Time: 11:35 AM
 */

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class JWTInvalidListener
{
    /**
     * @param JWTNotFoundEvent $event
     */
    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $data = [
            'code'  => '403',
            'message'=>['message'=>'Missing token',"code"=>'token_not_found','status'  => '403 Forbidden']
        ];

        $response = new JsonResponse($data, 403);

        $event->setResponse($response);
    }

}