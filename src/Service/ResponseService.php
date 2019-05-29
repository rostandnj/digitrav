<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 14/3/19
 * Time: 5:35 PM
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;


class ResponseService
{
    private $response;

    public function __construct()
    {
        $this->response =  new Response();
        $this->response->headers->set('Content-Type', 'application/json');

    }

    public function setStatut(int $code)
    {
        switch ($code)
        {
            case Response::HTTP_CREATED:
                $this->response->setStatusCode(Response::HTTP_CREATED);
                break;
            case Response::HTTP_FORBIDDEN:
                $this->response->setStatusCode(Response::HTTP_FORBIDDEN);
                break;
            case Response::HTTP_NOT_FOUND:
                $this->response->setStatusCode(Response::HTTP_NOT_FOUND);
                break;
            case Response::HTTP_UNAUTHORIZED:
                $this->response->setStatusCode(Response::HTTP_UNAUTHORIZED);
                break;
        }

        return $this;
    }

    public function setContent(array $data)
    {
        $this->response->setContent(json_encode($data));
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }


}