<?php

namespace App\Controller;


use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\User;
use App\Entity\UserDetail;
use App\Entity\Statut;
use App\Entity\Role;
use App\Entity\Quote;
use App\Entity\Payment;
use App\Entity\Notification;
use App\Entity\Note;
use App\Entity\MaterialQuote;
use App\Entity\Location;
use App\Entity\Intervention;
use App\Entity\File;
use App\Entity\Evaluation;
use App\Entity\Domain;
use App\Entity\Company;
use App\Entity\Category;
use App\Entity\Bill;
use App\Entity\City;



/**
 * Class ApiController
 * @package AppBundle\Controller
 * this class define all the methods called by the api route
 */
class ApiController
{

    private $container;
    private $em;
    private $response;

    /**
     * ApiController constructor.
     * @param $container
     * @param $em
     */
    public function __construct(ContainerInterface $container,EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->em = $em;
        $this->response = $this->container->get('response_service');
    }


    private function getCreateType($type)
    {
        $role_name ="";
        $typeuser="";

        switch ($type)
        {
            case "client":
                $role_name="ROLE_CLIENT";
                $typeuser="createClient";
                break;
            case "technician_p":
                $role_name="ROLE_TECHNICIAN_PERSON";
                $typeuser="createTechnician";
                break;
            case "technician_c":
                $role_name="ROLE_TECHNICIAN_COMPANY";
                $typeuser="createTechnician";
                break;
            case "admin":
                $role_name="ROLE_ADMIN";
                $typeuser="createAdmin";
                break;
            case "operator":
                $role_name="ROLE_OPERATOR";
                $typeuser="createAdmin";
                break;
            case "manager":
                $role_name="ROLE_MANAGER_COMPANY";
                $typeuser="createTechnician";
                break;
            default:
                break;

        }

        return ["role"=>$role_name,"type"=>$typeuser];

    }

    /**
     * @param
     * @return Response
     */
    public function test()
    {

        $userService = $this->container->get('user_manager');
        $res = $this->container->get('response_service');

        $data = ["name"=>"nnnnn","surname"=>"hhhh",
            "email"=>"njff@gg.gg","password"=>"azerty0ZE",
            "phone"=>"694864251",
            "gender"=>1,"city"=>"yaounde","quater"=>"manguier"];

        $result = $userService->createTechnician($data,"ROLE_MANAGER_COMPANY");

        if($result["statut"]==false)
        {
            $res->setStatut(401);
            $res->setContent(["message"=>$result["message"],"data"=>""]);

        }
        else
        {
            $res->setStatut(201);
            $res->setContent(["message"=>"","data"=>$result["data"]->toArray()]);

        }




        return $res->getResponse();


    }

    public function home()
    {
        $token = $this->container->get("security.token_storage")->getToken();

        $res = $this->container->get('response_service');

        //$r =$this->container->get("mail_manager")->testMail();

        //var_dump($token->getUsername());

        $res->setContent( [$token->getUser()->getId()]);
        $res->setStatut(201);


        return $res->getResponse();
    }



    /**
     * @param Request $request
     * @return Response
     * get a post data from a request for create a user
     */
    public function registerAccount(Request $request,$type)
    {

        $utype = $this->getCreateType($type);

        $res = $this->container->get('response_service');

        if($utype["role"]=="" | $utype["type"]=="")
        {
            $res->setStatut(401);
            $res->setContent(["message"=>"bad url","data"=>[]]);
            return $res->getResponse();
        }

        $req = $this->container->get('request_service');

        $req->setRequest($request);

        $data = $req->getData();

        $userService = $this->container->get('user_manager');

        $create = $utype["type"];
        $role_name = $utype["role"];


        $result = $userService->$create($data,$role_name);



        if($result["statut"]==false)
        {
            $res->setStatut(401);
            $res->setContent(["message"=>$result["message"],"data"=>""]);

        }
        else
        {
            $res->setStatut(201);
            $res->setContent(["message"=>"","data"=>$result["data"]->toArray()]);

        }




        return $res->getResponse();

    }

    /**
     * @param Request $request
     * @param string $id
     * @param string $token
     * @return Response
     * get a post data from a request for create a user
     */
    public function validateAccount(Request $request,$id,$token)
    {
        $id = $this->container->get("hash_service")->decrypt($id);

        $res = $this->container->get('user_manager')->active($id,$token);
        $this->response->setContent(["message"=>$res["message"]]);

        if($res["statut"]==false)
        {
            $this->response->setStatut(401);
        }
        else
        {
            $this->response->setStatut(201);

        }

        return $this->response->getResponse();




    }

    public function updateProfile(Request $request,$type)
    {
        $currentUser = $this->container->get("security.token_storage")->getToken();
        $userService = $this->container->get('user_manager');

       // $reqType =["picture","cni_field","cni_file","criminal_record","company"];

        $data = $this->container->get("request_service")->setRequest($request)->getData();


        $res = $userService->updateProfile($currentUser->getUser(),$type,$data);

        $this->response->setStatut($res["code"]);

        if($res["statut"]==true) $res["data"]=$res["data"]->toArray();
        $this->response->setContent($res);

        return $this->response->getResponse();

    }

    public function testRegistration()
    {


        $r =$this->container->get("mail_manager")->welcome(1,"njomo rostand","rostandnj@gmail.com","147d8e5d4fg5264");


        $res = $this->container->get('response_service');
        $res->setContent( [$r]);
        $res->setStatut(201);


        return $res->getResponse();
    }

    public function simpleProfile(Request $request,$id)
    {
        //$currentUser = $this->container->get("security.token_storage")->getToken();
        $userService = $this->container->get('user_manager');

        $res = $userService->getSimpleProfile($id);

        $this->response->setStatut($res["code"]);
        $this->response->setContent($res);

        return $this->response->getResponse();
    }

    public function sendProfileForValidation(Request $request)
    {
        $currentUser = $this->container->get("security.token_storage")->getToken();
        $userService = $this->container->get('user_manager');

        $res = $userService->sendAccountForValidation($currentUser->getUser());

        $this->response->setStatut($res["code"]);
        $this->response->setContent($res);

        return $this->response->getResponse();

    }

    public function lockAccount(Request $request,$id)
    {
        $userService = $this->container->get('user_manager');
        $currentUser = $this->container->get("security.token_storage")->getToken();

        $res = $userService->lockAccount($currentUser->getUser(),$id);

        $this->response->setStatut($res["code"]);
        $this->response->setContent($res);

        return $this->response->getResponse();

    }

    public function validateTechnician(Request $request,$id)
    {
        $userService = $this->container->get('user_manager');
        $currentUser = $this->container->get("security.token_storage")->getToken();

        $data = $this->container->get("request_service")->setRequest($request)->getData();

        $res = $userService->validateTechnician($currentUser->getUser(),$id,$data);

        $this->response->setStatut($res["code"]);
        $this->response->setContent($res);

        return $this->response->getResponse();

    }

    public function getAllCompany(Request $request)
    {
        $companies =$this->em->getRepository(Company::class)->findAll();

        $res = $this->container->get('response_service');

        $data=[];

        foreach ($companies as $el)
        {
            $data[]=$el->toArray();
        }

        $res->setContent($data);
        $res->setStatut(201);


        return $res->getResponse();
    }
    public function getAllDomain(Request $request)
    {
        $companies =$this->em->getRepository(Domain::class)->findAll();

        $res = $this->container->get('response_service');

        $data=[];

        foreach ($companies as $el)
        {
            $data[]=$el->toArray();
        }

        $res->setContent($data);
        $res->setStatut(201);


        return $res->getResponse();
    }
    public function getAllCity(Request $request)
    {
        $companies =$this->em->getRepository(City::class)->findAll();

        $res = $this->container->get('response_service');

        $data=[];

        foreach ($companies as $el)
        {
            $data[]=$el->toArray();
        }

        $res->setContent($data);
        $res->setStatut(201);


        return $res->getResponse();
    }
}
