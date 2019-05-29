<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 14/3/19
 * Time: 5:35 PM
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\Request;

class RequestService
{
    private $request;

    public function __construct()
    {

    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;

    }

    public function setRequest(Request $r)
    {
        $this->request = $r;
        return $this;
    }

    public function getData()
    {

        return json_decode($this->getRequest()->getContent(),true);
    }

    public function getQuery(string $key)
    {
        return $this->getRequest()->query->get($key);
    }


}