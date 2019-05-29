<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 24/4/19
 * Time: 4:58 PM
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;


class SecurityController extends AbstractController
{

    protected $mycontainer;

    public function __construct(ContainerInterface $c)
    {
        $this->mycontainer = $c;

        $lang = $this->mycontainer->get("request_stack")->getCurrentRequest()->cookies->get("lang");


        if(in_array($lang,["fr","en"])) {
            $this->mycontainer->get('translator')->setLocale($lang);
            $this->locale = $lang;
        }
    }

    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if(!is_null($this->getUser())) return $this->redirectToRoute("web_find_job");


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $data=[
            'email' => $lastUsername
        ];

        if(!is_null($error)) {
            $data['message']= $this->mycontainer->get('translator')->trans('login_incorrect');
            $data["error"]= $error;
        }



        return $this->render('client/login.html.twig',$data );
    }
}