<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 27/4/19
 * Time: 8:11 AM
 */

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use App\Entity\City;

class CityService
{
    private $em;
    private $container;


    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;

    }

    public function getAll()
    {
        $data = $this->em->getRepository(City::class)->findAll();

        return $data;
    }

}