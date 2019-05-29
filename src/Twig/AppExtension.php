<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 23/4/19
 * Time: 11:37 AM
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $c)
    {
        $this->container = $c;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('timeago', [$this, 'timeagoFunction']),
        ];
    }

    public function timeagoFunction($datetime)
    {
        $time = time() - strtotime($datetime);

        $units = array (
            31536000 => $this->container->get('translator')->trans('year'),
            2592000 => $this->container->get('translator')->trans('month'),
            604800 => $this->container->get('translator')->trans('week'),
            86400 => $this->container->get('translator')->trans('day'),
            3600 => $this->container->get('translator')->trans('hour'),
            60 => $this->container->get('translator')->trans('minute'),
            1 => $this->container->get('translator')->trans('second')
        );

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);

           if($this->container->get('translator')->trans('year')=='year')
           {
               return ($val == 'second')? $this->container->get('translator')->trans('a_few_seconds_ago') :
                   (($numberOfUnits>1) ? $numberOfUnits : 'a')
                   .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
           }
           else
           {
               return ($val == 'second')? $this->container->get('translator')->trans('a_few_seconds_ago') :"il y'a ".
                   (($numberOfUnits>1) ? $numberOfUnits : '1')
                   .' '.$val.(($numberOfUnits>1) ? 's' : '');
           }


        }
    }
}