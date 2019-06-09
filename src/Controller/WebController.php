<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 25/3/19
 * Time: 11:42 AM
 */

namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Twig\AppExtension;




class WebController extends AbstractController implements LogginInterfaceController
{

    private $mycontainer;
    private $token;
    private $locale;
    private $tokenUser;
    private $session;
    private $extension;

    private function getRandom(int $n)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $numbers='0123456789';
        $maj='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        if($n>=8)
        {
            for ($i = 0; $i < 2; $i++)
            {
                $index = rand(0, strlen($numbers) - 1);
                $randomString .= $numbers[$index];

            }

            for ($i = 2; $i < 6; $i++)
            {
                $index = rand(0, strlen($characters) - 1);
                $randomString .= $characters[$index];

            }

            for ($i = 6; $i < 8; $i++)
            {
                $index = rand(0, strlen($maj) - 1);
                $randomString .= $maj[$index];

            }
        }
        else{
            for ($i = 0; $i < $n; $i++) {

                if($i <=3)
                {
                    $index = rand(0, strlen($numbers) - 1);
                    $randomString .= $numbers[$index];
                }
                else if($i==$n)
                {
                    $index = rand(0, strlen($maj) - 1);
                    $randomString .= $maj[$index];
                }
                else
                {
                    $index = rand(0, strlen($characters) - 1);
                    $randomString .= $characters[$index];
                }

            }
        }



        return $randomString;
    }

    /**
     * WebController constructor.
     */
    public function __construct(ContainerInterface $c,SessionInterface $s)
    {

        $this->extension = new AppExtension($c);
        $this->session = $s;
        $this->mycontainer = $c;
        $this->locale="en";
        $lang = $this->mycontainer->get("request_stack")->getCurrentRequest()->cookies->get("lang");


        if(in_array($lang,["fr","en"])) {
            $this->mycontainer->get('translator')->setLocale($lang);
            $this->locale = $lang;
        }

        $token = $this->mycontainer->get("request_stack")->getCurrentRequest()->cookies->get("token");
        if($token == null) {
            $this->tokenUser = null;
        }
        else{
            $this->connected = true;
            $this->token =$token;
            $tokenPayload=null;
            if(!is_null($this->token))
            {
                $tokenParts = explode(".", $this->token);
                //$tokenHeader = base64_decode($tokenParts[0]);
                $tokenPayload = base64_decode($tokenParts[1]);
                $this->tokenUser=  json_decode($tokenPayload);

            }


        }

    }

    public function home(Request $request)
    {

        if(!is_null($this->getUser())) return $this->redirectToRoute("web_dashboard");

        $domains = $this->mycontainer->get("domain_manager")->showList(10,0);

        $jobs = $this->mycontainer->get("intervention_manager")->getJobs(3,0);




       return $this->render('client/home.html.twig',["domains"=>$domains["data"],"jobs"=>$jobs["data"]]);
    }

   public function about(Request $request)
   {
       return $this->render('client/about.html.twig');
   }

   public function jobs(Request $request)
   {
       return $this->render('client/jobs.html.twig');
   }

   public function services(Request $request)
   {
       return $this->render('client/services.html.twig');
   }

    public function contact(Request $request)
    {

        return $this->render('client/contact.html.twig');
    }

    public function login2(Request $request)
    {
        /*$this->addFlash(
            'notice',
            'Your changes were saved!'
        );*/


        if($this->session->get('user')!=null) return $this->redirectToRoute("web_dashboard");

        if($request->getMethod() == "GET")
        {
            return $this->render('client/login.html.twig');
        }
        else
        {
            $submittedToken = $request->request->get('csrf');

            if (!$this->isCsrfTokenValid('csrf', $submittedToken))
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('invalid_csrf'));
                $this->redirectToRoute('web_home');
            }
            else
            {


                $email = $request->request->get("email");
                $password = $request->request->get("password");

                $res = $this->mycontainer->get("user_manager")->login($email,$password);

                if($res["statut"]==false){

                    return $this->render('client/login.html.twig',["email"=>$email,"password"=>$password,'message' => $this->mycontainer->get('translator')->trans('login_incorrect')]);

                }
                else
                {

                    if($res["code"]==401)
                    {
                        return $this->render('client/login.html.twig',["email"=>$email,"password"=>$password,'message' => $this->mycontainer->get('translator')->trans('account_not_activated')]);

                    }
                    else
                    {
                        $response = new Response();
                        $token = $res["data"]["token"];

                        $cookie = new Cookie('token',  $token,  time()+(86400*30*30));
                        $utab=$res["data"]["user"]->toArray();
                        $utab["uid"]=$this->mycontainer->get("hash_service")->encrypt($res["data"]["user"]->getId());
                        $this->session->set("user",$utab);
                        $this->session->set("token",$token);





                        $res = new Response();
                        $res->headers->setCookie( $cookie );
                        $res->send();

                        //setcookie("token",$token,time()+(86400*30*30),"/");


                        return $this->redirectToRoute('web_dashboard');
                    }


                }




            }


        }

    }

    public function password(Request $request)
    {
        if(!is_null($this->getUser())) return $this->redirectToRoute("web_dashboard");

        if($request->getMethod()=="GET")
        {
            return $this->render('client/password.html.twig');
        }
        else
        {
            $submittedToken = $request->request->get('csrf');

            if (!$this->isCsrfTokenValid('csrf', $submittedToken))
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('invalid_csrf'));
                $this->redirectToRoute('web_home');
            }
            else
            {
                $email = $request->request->get("email");

                $res = $this->mycontainer->get("user_manager")->resetPassword($email);

                if($res["statut"]==false)
                {
                    $this->addFlash('notice',$this->mycontainer->get('translator')->trans('reset_password_msg_error'));
                    return $this->render('client/password.html.twig');
                }
                else
                {
                    $this->addFlash('notice',$this->mycontainer->get('translator')->trans('reset_password_msg'));
                    return $this->render('client/password.html.twig');
                }

            }

        }

    }

    public function signup(Request $request)
    {
        if(!is_null($this->getUser())) return $this->redirectToRoute("web_dashboard");

        if($request->getMethod() == "GET")
        {
            return $this->render('client/create.html.twig');
        }
        else
        {
            $data =[];
            $data["name"]=trim($request->request->get("first_name"));
            $data["surname"]=trim($request->request->get("last_name"));
            $data["email"]=trim($request->request->get("email"));
            $data["phone"]=trim($request->request->get("phone"));
            $data["gender"]=$request->request->get("gender");
            $data["account"]=$request->request->get("account");
            $data["password"]=$request->request->get("password");
            $data["quater"]=$request->request->get("location_quater");
            $data["city"]=$request->request->get("location_city");
            $data["longitude"]=$request->request->get("location_lon");
            $data["latitude"]=$request->request->get("location_lat");
            $data["company"]=$request->request->get("company");

            $account = intval($data['account']);

            if($account==1)
               $res =  $this->mycontainer->get("user_manager")->createClient($data);
            else if($account==0)
                $res = $this->mycontainer->get("user_manager")->createTechnician($data,"ROLE_TECHNICIAN_PERSON");
            else if($account==3)
                $res = $this->mycontainer->get("user_manager")->createTechnician($data,"ROLE_MANAGER_COMPANY");
            else
            {
                $data["user"]=$this->tokenUser;
                return $this->render('client/create.html.twig',$data);
            }

            if($res["statut"] ==false)
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                $data["message"]=$this->mycontainer->get('translator')->trans($res["message"]);
                return $this->render('client/create.html.twig',$data);
            }
            else
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('account_created'));
                return $this->redirectToRoute("web_login");
            }

        }


    }

    public function navMenu(Request $request)
    {
        $request->setLocale($this->locale);
        var_dump($request->getLocale());
        $tokenPayload=null;
        if(!is_null($this->token))
        {
            $tokenParts = explode(".", $this->token);
            //$tokenHeader = base64_decode($tokenParts[0]);
            $tokenPayload = base64_decode($tokenParts[1]);
            $tokenPayload = json_decode($tokenPayload);
        }
        //$lang = $this->mycontainer->get("request_stack")->getCurrentRequest()->cookies->get("lang");
        //if(in_array($lang,["fr","en"])) $this->mycontainer->get('translator')->setLocale($lang);

        return $this->render('client/base/nav.html.twig',['user' => $tokenPayload]);
    }

    public function logout(Request $request)
    {

        $res = new Response();
        $res->headers->clearCookie("token");
        $this->session->clear();
        $res->setStatusCode(201);
        $res->send();




        return $this->redirectToRoute("web_home");

    }

    public function validateAccount(Request $request,$id,$token)
    {
        $id = $this->mycontainer->get("hash_service")->decrypt($id);

        $res = $this->mycontainer->get('user_manager')->active($id,$token);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res['message']));

        return $this->redirectToRoute("web_login");


    }

    public function profile(Request $request,$name,$id)
    {
        if(is_null($this->getUser())) return $this->redirectToRoute("web_login");


        $id = $this->mycontainer->get("hash_service")->decrypt($id);

        $res = $this->mycontainer->get('user_manager')->getUserById($id);

        if(is_null($res)) return $this->render('client/404.html.twig',["userp"=>[]]);

        if($request->getMethod()=="POST")
        {

            //var_dump($request->request->get("domain-name"));


            $data = $request->request->get("data");





            if(!is_null($data))
            {
                if($data == "picture")
                {
                    $file = $request->files->get("picture");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "password")
                {
                    $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["c-password"=>$request->request->get("c-password"),"n-password"=>$request->request->get("n-password")]);
                    if($result["statut"]==false) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($result["message"]));
                    else{

                        $res =$result["data"];
                        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                        $this->session->set("user",$res->toArray());
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                    }


                }
                if($data == "cnif")
                {
                    $file = $request->files->get("cnif");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,"cni_file",["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "criminal-record")
                {
                    $file = $request->files->get("criminal-record");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,"criminal_record",["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "cv")
                {
                    $file = $request->files->get("cv");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,"cv",["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "domain-name")
                {
                    $ids = $request->request->get($data);

                    if(count($ids)>=0)
                    {
                        $result= $this->mycontainer->get("user_manager")->updateProfile($res,"domain",["ids"=>$ids,"domain"=>$ids]);
                        if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                        else{

                            $res =$result["data"];
                            $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                            $this->session->set("user",$res->toArray());
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                        }

                    }


                }
                if($data == "company-domain-name")
                {
                    $ids = $request->request->get($data);

                    if(count($ids)>=0)
                    {
                        $result= $this->mycontainer->get("user_manager")->updateProfile($res,"company-domain",["ids"=>$ids,"domain"=>$ids]);
                        if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                        else{

                            $res =$result["data"];
                            $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                            $this->session->set("user",$res->toArray());
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                        }

                    }


                }
                if($data == "validate")
                {
                    if($res->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
                    {
                        $result= $this->mycontainer->get("user_manager")->sendCompanyForValidation($res);
                    }
                    else
                    {
                        $result= $this->mycontainer->get("user_manager")->sendAccountForValidation($res);
                    }




                    if($result["statut"]==false) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($result["message"]));
                    else{

                        $res =$result["data"];
                        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                        $this->session->set("user",$res->toArray());
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_sended'));
                    }
                }
                if($data == "company-logo")
                {
                    $file = $request->files->get("company-logo");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "tax_card")
                {
                    $file = $request->files->get("tax_card");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "trade_register")
                {
                    $file = $request->files->get("trade_register");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "company-location-plan")
                {
                    $file = $request->files->get("company-location-plan");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                if($data == "company-director")
                {
                    $file = $request->files->get("company-director");

                    if(!is_null($file))
                    {

                        $fu = $this->mycontainer->get("upload_service");
                        $img = $fu->upload($file);
                        if($img["statut"]==false)
                        {
                            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        }
                        else
                        {
                            $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,["file"=>$img["file"]]);
                            if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                            else{

                                $res =$result["data"];
                                $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                                $this->session->set("user",$res->toArray());
                                $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                            }
                        }
                    }


                }
                $input = $request->request->get("edit-".$data);

                if(!is_null($input))
                {
                    $result= $this->mycontainer->get("user_manager")->updateProfile($res,$data,[$data=>$input]);

                    if($result["statut"]==false) $this->addFlash('notice',$result["message"]);
                    else{
                        $res =$result["data"];
                        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));
                        $this->session->set("user",$res->toArray());
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans('profile_updated'));
                    }
                }
            }

            $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));

            return $this->redirectToRoute("web_profile",["name"=>$res->getProfileName(),"id"=>$res->getUid()]);

        }


        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));

        if(in_array($res->getRole()->getCode(),["ROLE_CLIENT","ROLE_ADMIN","ROLE_SADMIN","ROLE_OPERATOR","ROLE_MANAGER_COMPANY"]))
        {
            if($res->getRole()->getCode() == "ROLE_MANAGER_COMPANY")
            {


                $s = $this->mycontainer->get("user_manager")->getManagerStatut($res);

                if($s["statut"]==false)
                {
                    if($this->session->get("user")["id"]==$res->getId())
                    {
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($s["message"]));
                    }
                }

                $stat = $this->mycontainer->get("intervention_manager")->userStat($res);
                $notes = $this->mycontainer->get("intervention_manager")->getTechnicianNote($res);

                return $this->render('client/profile_manager.html.twig',["notes"=>$notes["data"],"userp"=>$res->toArray(),"company"=>$s["data"]["company"],"done"=>$stat["data"]["done"],"earning"=>$stat["data"]["earning"]]);

            }
            else if(in_array($res->getRole()->getCode(),["ROLE_ADMIN","ROLE_SADMIN","ROLE_OPERATOR"]))
            {
                return $this->render('client/profile_admin.html.twig',["userp"=>$res->toArray()]);

            }
            else
            {
                $jobs = $this->mycontainer->get("intervention_manager")->userJobs($res,4,0);

                $stat = $this->mycontainer->get("intervention_manager")->clientStat($res);

                return $this->render('client/profile_client.html.twig',["userp"=>$res->toArray(),"jobs"=>$jobs["data"],
                    "nb_job"=>$stat["data"]["nb_job"],"spent"=>$stat["data"]["spent"]]);
            }



        }

        else{
            $s = $this->mycontainer->get("user_manager")->getTechnicianStatut($res);

            if($s["statut"]==false)
            {
                if($this->session->get("user")["id"]==$res->getId())
                {
                    $this->addFlash('notice',$this->mycontainer->get('translator')->trans($s["message"]));
                }
            }

            $stat = $this->mycontainer->get("intervention_manager")->userStat($res);
            $notes = $this->mycontainer->get("intervention_manager")->getTechnicianNote($res);

            return $this->render('client/profile.html.twig',["notes"=>$notes["data"], "userp"=>$res->toArray(),"statut"=>["message"=>$s["message"],"statut"=>$s["statut"]],"done"=>$stat["data"]["done"],"earning"=>$stat["data"]["earning"]]);
        }


    }

    public function dashboard(Request $request)
    {

        $cu = $this->getUser();

        if(is_null($cu)) return $this->redirectToRoute("web_login");

        $code = $cu->getRole()->getCode();

        if(in_array($code,["ROLE_SADMIN","ROLE_OPERATOR","ROLE_ADMIN"])) {

            $data = $this->mycontainer->get("intervention_manager")->userStat($cu);

            $limit =20;
            $offset = 0;

            $devis = $this->mycontainer->get("intervention_manager")->getDevis($limit,$offset);



            return $this->render('client/dashboard_admin.html.twig',["menu"=>"","data"=>$data["data"],"quotes"=>$devis["data"]]);
        }
        else if($code=="ROLE_CLIENT"){

            $data = $this->mycontainer->get("intervention_manager")->userStat($cu);
            return $this->render('client/dashboard_client.html.twig',["menu"=>"","data"=>$data["data"]]);
        }
        else if(in_array($code,["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"])){

            $data = $this->mycontainer->get("intervention_manager")->userStat($cu);

            return $this->render('client/dashboard_technician.html.twig',["menu"=>"","data"=>$data["data"]]);
        }
        else
        {
            return $this->render('client/dashboard.html.twig',["menu"=>""]);
        }


    }

    public function reports(Request $request)
    {
        if(is_null($this->getUser())) return $this->redirectToRoute("web_login");

        return $this->render('client/reports.html.twig');

    }

    public function notifications(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $u = $cu;


        $limit =20;

        $offset =0;

        if(!empty($request->query->get('p')))
        {
            $page = intval($request->query->get('p'));

            $offset = $limit*($page-1);
        }
        else
        {
            $page=1;
        }


        $res = $this->mycontainer->get("notification_service")->getMyNotification($u,$limit,$offset);
        $res2 = $this->mycontainer->get("notification_service")->countAllNotification($u->getId());

        $totalPage=1;

        if($res2 > 0)
        {
            $totalPage = ceil($res2/$limit);
        }




        return $this->render("client/dashboard_notifications.html.twig",["notifications"=>$res["data"],"page"=>$page,"total_page"=>$totalPage, "menu"=>"notification"]);


    }

    public function dashboardUser(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name"]=trim($request->request->get("first_name"));
                $data["surname"]=trim($request->request->get("last_name"));
                $data["email"]=trim($request->request->get("email"));
                $data["phone"]=trim($request->request->get("phone"));
                $data["gender"]=$request->request->get("gender");
                $data["account"]=$request->request->get("account");
                $data["password"]=$this->getRandom(8);
                $data["quater"]=$request->request->get("location_quater");
                $data["city"]=$request->request->get("location_city");
                $data["longitude"]=$request->request->get("location_lon");
                $data["latitude"]=$request->request->get("location_lat");

                if($data["account"]==5) $role = "ROLE_OPERATOR";
                else $role = "ROLE_ADMIN";

                $res = $this->mycontainer->get("user_manager")->createAdmin($data,$role);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans("user_added"));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute('web_dashboard_user');


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =100;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data1 = $this->mycontainer->get("user_manager")->usersList($cu->getRole()->getCode(), $limit,$offset);



            $d = $this->mycontainer->get("user_manager")->nbAwaitingValidation();

            return $this->render('client/dashboard_user.html.twig',["menu"=>"user","users"=>$data1["data"],"validation"=>$d["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }


    }

    public function dashboardUserDetail(Request $request,$name,$id)
    {

        $cu = $this->getUser();
        if( $cu==null) return $this->redirectToRoute("web_login");

        $id = $this->mycontainer->get("hash_service")->decrypt($id);

        if($request->getMethod()=="POST")
        {
            $ans = $request->request->get("answer");

            if(!is_null($ans))
            {
                $resp = $this->mycontainer->get("user_manager")->validateTechnician($this->mycontainer->get('user_manager')->getUserById($cu->getId()),$id,["answer"=>$ans]);

               if($resp["statut"]==false)
               {
                   $this->addFlash('notice',$resp["message"]);
               }
               else
               {
                   $this->addFlash('notice',$this->mycontainer->get('translator')->trans("operation_did"));
               }

            }

            return $this->redirectToRoute("web_dashboard_user");

        }

        $res = $this->mycontainer->get('user_manager')->getUserById($id);

        if(is_null($res)) return $this->render('client/404.html.twig',["userp"=>[]]);
        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));




        if(!in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {
            return $this->render('client/401.html.twig',["userp"=>[]]);
        }

        if(in_array($res->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
            return $this->render('client/dashboard_profile_admin.html.twig',["userp"=>$res->toArray(),"menu"=>"user","statut"=>false]);
        else if($res->getRole()->getCode()=="ROLE_CLIENT")
            return  $this->render('client/dashboard_profile_client.html.twig',["userp"=>$res->toArray(),"menu"=>"user"]);
        else if($res->getRole()->getCode()=="ROLE_TECHNICIAN_PERSON" || $res->getRole()->getCode()=="ROLE_TECHNICIAN_COMPANY")
        {
            $s = $this->mycontainer->get("user_manager")->getTechnicianStatut($res);



            if($s["message"]=="operation_denied") $st=false;
            if($s["message"]=="account_validated") $st=false;
            if($s["message"]=="profile_awaiting") $st=true;
            if($s["message"]=="profile_not_send") $st=false;


            return $this->render('client/dashboard_profile_techninician.html.twig',["userp"=>$res->toArray(),"menu"=>"user","statut"=>$st,"mess"=>$s]);
        }
        else
        {

            $s = $this->mycontainer->get("user_manager")->getTechnicianStatut($res);

            if($s["message"]=="operation_denied") $st=false;
            if($s["message"]=="account_validated") $st=false;
            if($s["message"]=="profile_awaiting") $st=true;
            if($s["message"]=="profile_not_send") $st=false;


            $r = $this->mycontainer->get("user_manager")->userCompany($res);
            return $this->render('client/dashboard_profile_manager.html.twig',["userp"=>$res->toArray(),"company"=>$r["data"], "menu"=>"user","statut"=>$s,"mess"=>$s]);
        }




    }

    public function dashboardUserValidation(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =100;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data1 = $this->mycontainer->get("user_manager")->userValidationAwaiting( $limit,$offset);

            $res=[];


            foreach ($data1["data"] as $us1)
            {
                $us = $us1->getUser();

                $res[]=$us;
            }


            return $this->render('client/dashboard_user_validation.html.twig',["menu"=>"user","users"=>$res]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }


    }

    public function dashboardDomain(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));



                $res = $this->mycontainer->get("domain_manager")->create($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_domain");


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =100;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data1 = $this->mycontainer->get("domain_manager")->showList( $limit,$offset);





            return $this->render('client/dashboard_domain.html.twig',["menu"=>"domain","domains"=>$data1["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardDomainEdit(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));
                $data["id"]=$id;



                $res = $this->mycontainer->get("domain_manager")->edit($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_domain");


            }


            $res = $this->mycontainer->get("domain_manager")->find($id);

            if($res["statut"]==false)
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                return $this->redirectToRoute("web_dashboard_domain");
            }

            $get = "getName".ucfirst($this->session->get("lang"));
            $d = $res["data"];
            $d->setName($d->$get());



            return $this->render('client/dashboard_domain_edit.html.twig',["menu"=>"domain","domain"=>$d]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardDomainUsers(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =100;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;

            $res = $this->mycontainer->get("domain_manager")->listTechnicians($id,$limit,$offset);

            if($res["statut"]==false)
            {
                $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                return $this->redirectToRoute("web_dashboard_domain");
            }

            $mq = $this->mycontainer->get("material_manager")->showDomain($res["data"]["domain"]->getId())["data"];


            return $this->render('client/dashboard_domain_users.html.twig',["menu"=>"domain","materials"=>$mq,"domain"=>$res["data"]["domain"], "companies"=>$res["data"]["companies"],"users"=>$res["data"]["users"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardMaterial(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));

                $data["domain"]=$request->request->get("domain");

                $res = $this->mycontainer->get("material_manager")->create($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_material");


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =200;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data = $this->mycontainer->get("material_manager")->showList( $limit,$offset);

            $ds = $this->mycontainer->get("domain_manager")->showList(100,0);


            return $this->render('client/dashboard_material.html.twig',["menu"=>"material","materials"=>$data["data"],"domains"=>$ds["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardMaterialEdit(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));

                $data["id"]=$id;

                $res = $this->mycontainer->get("material_manager")->edit($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_material");


            }


            $data = $this->mycontainer->get("material_manager")->find($id);

            if($data["statut"]==false) return $this->render('client/404.html.twig',["userp"=>[]]);


            return $this->render('client/dashboard_material_edit.html.twig',["menu"=>"material","material"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardMaterialRemove(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["id"]=$id;



                $res = $this->mycontainer->get("material_manager")->removeFromDomain($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_material");


            }
            else
            {
                return $this->redirectToRoute("web_dashboard_material");
            }

        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function createJob(Request $request)
    {

        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR","ROLE_CLIENT"]))
        {
            $domains = $this->mycontainer->get("domain_manager")->showList(100,0);

            if($request->getMethod()=="POST")
            {
                $data["description"]=trim($request->request->get("description"));
                $data["title"]=trim($request->request->get("title"));
                $data["category"]=trim($request->request->get("category"));
                $data["scategory"]=$request->request->get("scategory");
                $data["amount"]=trim($request->request->get("amount"));
                $data["domain"]=trim($request->request->get("domain"));
                $data["location_city"]=trim($request->request->get("city"));
                $data["location_quater"]=trim($request->request->get("quater"));
                $data["location_detail"]=trim($request->request->get("street"));
                $data["start_date"]=trim($request->request->get("start_date"));
                $data["user"]=$request->request->get("imagechecku");
                $data["company"]=$request->request->get("imagecheckc");

                if(is_null($data["company"])) $data["company"]=[];
                if(is_null($data["user"])) $data["user"]=[];



                if($cu->getRole()->getCode()=="ROLE_OPERATOR") $data["client_name"]=trim($request->request->get("client"));
                $data["files"]=[];



                $file = $request->files->get("image1");

                if(!is_null($file))
                {

                    $fu = $this->mycontainer->get("upload_service");
                    $img = $fu->upload($file);
                    if($img["statut"]==false)
                    {
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        return $this->render('client/create_job.html.twig',["menu"=>"","domains"=>$domains["data"], "title"=>$data["title"],"description"=>$data["description"]]);

                    }
                    else
                    {
                       $data["files"][]=$img["file"];
                    }
                }

                $file = $request->files->get("image2");

                if(!is_null($file))
                {

                    $fu = $this->mycontainer->get("upload_service");
                    $img = $fu->upload($file);
                    if($img["statut"]==false)
                    {
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        return $this->render('client/create_job.html.twig',["menu"=>"","domains"=>$domains["data"], "title"=>$data["title"],"description"=>$data["description"]]);

                    }
                    else
                    {
                        $data["files"][]=$img["file"];
                    }
                }

                $file = $request->files->get("video");

                if(!is_null($file))
                {

                    $fu = $this->mycontainer->get("upload_service");
                    $img = $fu->upload($file);
                    if($img["statut"]==false)
                    {
                        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($img["message"]));
                        return $this->render('client/create_job.html.twig',["menu"=>"","domains"=>$domains["data"], "title"=>$data["title"],"description"=>$data["description"]]);

                    }
                    else
                    {
                        $data["files"][]=$img["file"];
                    }
                }

                $u = $cu;

                $res = $this->mycontainer->get("intervention_manager")->create($u,$data);

                if($res["statut"]==false)
                {
                    $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                    return $this->render('client/create_job.html.twig',["menu"=>"","domains"=>$domains["data"], "title"=>$data["title"],"description"=>$data["description"]]);

                }
                else
                {
                    $this->addFlash('notice',$this->mycontainer->get('translator')->trans("operation_did"));

                    return $this->redirectToRoute("web_dashboard_job");
                }

            }
            else{

                return $this->render('client/create_job.html.twig',["menu"=>"jobs","domains"=>$domains["data"]]);
            }
        }
        else{
            return $this->redirectToRoute("web_dashboard");
        }





    }

    public function dashboardCategory(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));
                $data["budget"]=trim($request->request->get("budget"));

                $data["domain"]=$request->request->get("domain");

                $res = $this->mycontainer->get("category_manager")->create($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_category");


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =200;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data = $this->mycontainer->get("category_manager")->showList( $limit,$offset);
            $res = $this->mycontainer->get("domain_manager")->showList( 100,0);


            return $this->render('client/dashboard_category.html.twig',["menu"=>"category","domains"=>$res["data"], "categories"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardCategoryEdit(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));
                $data["budget"]=trim($request->request->get("budget"));

                $data["id"]=$id;

                $res = $this->mycontainer->get("category_manager")->edit($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_category");


            }


            $data = $this->mycontainer->get("category_manager")->find($id);

            if($data["statut"]==false) return $this->render('client/404.html.twig',["userp"=>[]]);


            return $this->render('client/dashboard_category_edit.html.twig',["menu"=>"domain","category"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function dashboardCategoryRemove(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["id"]=$id;



                $res = $this->mycontainer->get("category_manager")->removeFromDomain($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_category");


            }
            else
            {
                return $this->redirectToRoute("web_dashboard_category");
            }

        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function categoryDomain(Request $request)
    {
        $res = $this->mycontainer->get("category_manager")->findByDomain(intval($request->request->get("id")));

        $comp=[];
        $users=[];

        if($request->request->get("other")==0)
        {

            $re = $this->mycontainer->get("user_manager")->getTechnicianByDomain(intval($request->request->get("id")));

            foreach ($re["data"]["users"] as $el)
            {
                $users[]=$el->toArray();
            }

            foreach ($re["data"]["companies"] as $el)
            {
                $t = $el->toArray();
                $t["manager"] = $el->getManager()->toArrayShort();
                $comp[]=$t;
            }

        }

        $response = $this->mycontainer->get('response_service');
        $response->setStatut(201);

        $data=[];
        foreach ($res["data"] as $el)
        {
            $data[]=$el->toArray();
        }
        $response->setContent(["message"=>"","data"=>$data,"users"=>$users,"companies"=>$comp]);
        return $response->getResponse();
    }

    public function dashboardJobs(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $u = $cu;

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =100;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        $res = $this->mycontainer->get("intervention_manager")->userJobs($u,$limit,$offset);

        return $this->render("client/dashboard_job.html.twig",["jobs"=>$res["data"],"menu"=>"jobs"]);



    }

    public function showJob(Request $request,$slug)
    {
        $u = $this->getUser();

        if($u==null) return $this->redirectToRoute("web_login");

        $data = $this->mycontainer->get("intervention_manager")->showJob($slug,$u,20,0);



        if($data["code"] !=201)
        {
            return $this->render("client/403.html.twig",["userp"=>""]);
        }
        else
        {

            //$domains = $this->mycontainer->get('domain_manager')->showList(20,0);

            return $this->render("client/show_job.html.twig",["job"=>$data["data"]["job"],"quotes"=>$data["data"]["quotes"],"files"=>$data["data"]["files"],
                "nb_job"=>$data["data"]["nb_job"],"spent"=>$data["data"]["spent"],"invite"=>$data["data"]["invite"],"qid"=>$data["data"]["qid"]]);
        }
    }

    public function findWork(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        $res = $this->mycontainer->get("intervention_manager")->getJobs($limit,$offset);

        $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
        $cities =$this->mycontainer->get("city_service")->getAll();


        return $this->render('client/find_work.html.twig',["jobs"=>$res["data"],"domains"=>$domains["data"],"towns"=>$cities]);
    }

    public function findMoreWork(Request $request)
    {

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =4;

        $res = $this->mycontainer->get("intervention_manager")->getJobs($limit,$offset);

        $data =[];



        foreach ($res["data"] as $el)
        {
            $a = $el->toArray();
            $a["date"]=$this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
            $data[]=$a;
        }

        $res = $this->mycontainer->get('response_service');
        $res->setContent(["jobs"=>$data]);
        $res->setStatut(201);

        return $res->getResponse();
    }

    public function categoryWork(Request $request,$slug)
    {
        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        $res = $this->mycontainer->get("intervention_manager")->getJobsCategory($slug,$limit,$offset);

        $domains = $this->mycontainer->get('domain_manager')->showList(20,0);


        return $this->render('client/category_work.html.twig',["jobs"=>$res["data"]["jobs"],"category"=>$res["data"]["category"], "domains"=>$domains["data"]]);
    }

    public function domainWork(Request $request,$slug)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        $code =$cu->getRole()->getCode();

        $domains = $this->mycontainer->get('domain_manager')->showList(20,0);

        $cities =$this->mycontainer->get("city_service")->getAll();

        if($code=="ROLE_CLIENT")
        {
            $res = $this->mycontainer->get("intervention_manager")->getTechniciansDomain($slug,$limit,$offset);
            return $this->render('client/domain_users.html.twig',["towns"=>$cities,  "jobs"=>$res["data"]["jobs"],"domain"=>$res["data"]["domain"],"domains"=>$domains["data"]]);

        }
        else
        {
            $res = $this->mycontainer->get("intervention_manager")->getJobsDomain($slug,$limit,$offset);

            return $this->render('client/domain_work.html.twig',["towns"=>$cities,  "jobs"=>$res["data"]["jobs"],"domain"=>$res["data"]["domain"],"domains"=>$domains["data"]]);

        }






            }

    public function findMoreCategoryWork(Request $request,$slug)
    {

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =4;

        $res = $this->mycontainer->get("intervention_manager")->getJobsCategory($slug,$limit,$offset);

        $data =[];



        foreach ($res["data"]["jobs"] as $el)
        {
            $a = $el->toArray();
            $a["date"]=$this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
            $data[]=$a;
        }

        $res = $this->mycontainer->get('response_service');
        $res->setContent(["jobs"=>$data]);
        $res->setStatut(201);

        return $res->getResponse();
    }

    public function findMoreDomainWork(Request $request,$slug)
    {

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =4;

        $res = $this->mycontainer->get("intervention_manager")->getJobsDomain($slug,$limit,$offset);

        $data =[];



        foreach ($res["data"]["jobs"] as $el)
        {
            $a = $el->toArray();
            $a["date"]=$this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
            $data[]=$a;
        }

        $res = $this->mycontainer->get('response_service');
        $res->setContent(["jobs"=>$data]);
        $res->setStatut(201);

        return $res->getResponse();
    }

    public function formSearch()
    {


        $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
        $cities =$this->mycontainer->get("city_service")->getAll();

        return $this->render(
            'client/form_search.html.twig',
            ['domains' => $domains["data"],"towns"=>$cities]
        );
    }

    public function makeQuote(Request $request,$slug, $id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

         $type = 0;
         $message ="";
         if(!is_null($request->request->get("description1"))){
             $type =1;
             $message = trim($request->request->get("description1"));
         }

        $date=trim($request->request->get("start_date3"));



        $res = $this->mycontainer->get("intervention_manager")->makeQuote($cu,$type,$date,$message,$id);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

        return $this->redirectToRoute('web_show_job',["slug"=>$slug]);




    }

    public function alertJob(Request $request,$slug, $id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $res = $this->mycontainer->get("intervention_manager")->alertJob($cu,$id);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

        return $this->redirectToRoute('web_show_job',["slug"=>$slug]);


    }

    public function addJobToFavorite(Request $request,$slug, $id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $res = $this->mycontainer->get("intervention_manager")->addFavorite($cu,$id);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

        return $this->redirectToRoute('web_show_job',["slug"=>$slug]);

    }

    public function favoriteJob(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $u = $cu;

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =100;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        $res = $this->mycontainer->get("intervention_manager")->userFavoriteJobs($u,$limit,$offset);

        return $this->render("client/dashboard_job.html.twig",["jobs"=>$res["data"],"menu"=>"fav"]);
    }

    public function dashboardAlert(Request $request)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {


                $res = $this->mycontainer->get("intervention_manager")->deleteAlert($cu,(int)$request->request->get("id"));

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_dashboard_alerts");


            }

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =200;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;


            $data = $this->mycontainer->get("intervention_manager")->getAlerts($cu,$limit,$offset);


            return $this->render('client/dashboard_alerts.html.twig',["menu"=>"alert","alerts"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function deleteJob(Request $request,$slug, $id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_ADMIN","ROLE_SADMIN","ROLE_CLIENT"]))
        {
            $res = $this->mycontainer->get("intervention_manager")->deleteJob($cu,$id);

            $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
        }
        else
        {
            $this->addFlash('notice',$this->mycontainer->get('translator')->trans("operation_denied"));
        }





        return $this->redirectToRoute('web_find_job');

    }

    public function search(Request $request)
    {
        $data["city"] =$request->request->get("city-form");
        $data["domain"] =$request->request->get("domain-form");

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =10;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =0;

        if(is_null($request->request->get("keyword")))
        {
            $data["type"]="technician";
            $data["key"]=" ";
            $data["user"]=trim($request->request->get("user-type-form"));
        }
        else
        {
            $data["type"]="client";
            $data["key"]=trim($request->request->get("keyword"));
            $data["user"]=" ";
        }

        $d = $this->mycontainer->get("intervention_manager")->search($data["domain"],$data["city"],$data["key"],$data["type"],$data["user"],$limit,$offset);



        $dat=[];

        foreach ($d["data"] as $el)
        {
            $dat[]=$el->toArray();
        }


        $this->mycontainer->get("session")->set("post_data",$dat);
        $this->mycontainer->get("session")->set("offset",$offset);



        if($data["type"]=="technician")
        {
            return $this->redirectToRoute("web_dashboard_search_result",["type"=>"technician","d"=>$data["domain"],"c"=>$data["city"],"t"=>$data["user"]]);
        }
        else
        {
            return $this->redirectToRoute("web_dashboard_search_result",["type"=>"job","d"=>$data["domain"],"c"=>$data["city"],"k"=>$data["key"]]);
        }




    }

    public function searchResult(Request $request,$type)
    {
        $res = $this->mycontainer->get("session")->get("post_data");


        $d =  $request->query->get("d");
        $c = $request->query->get("c");
        $t=$request->query->get("t");

        if(is_null($request->query->get("k"))) $k="";
        else $k=$request->query->get("k");


        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =10;

        if(!is_null($this->mycontainer->get("session")->get("offset")))
        {
            $offset = intval($this->mycontainer->get("session")->get("offset"));
        }
        else $offset =0;

        $this->mycontainer->get("session")->set("offset",null);


        if($type == "technician")
        {

            if(is_null($res))
            {

                if($d!=null && $c!=null && $t!=null)
                {
                    $da = $this->mycontainer->get("intervention_manager")->search($d,$c,"","technician",$t,$limit,$offset);


                    $dat=[];

                    foreach ($da["data"] as $el)
                    {
                        $dat[]=$el->toArray();
                    }

                    $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
                    $cities =$this->mycontainer->get("city_service")->getAll();

                    return $this->render('client/search_technician_result.html.twig',["users"=>$dat,"domains"=>$domains["data"],"towns"=>$cities,"d"=>$d,"c"=>$c,"t"=>$t,"r"=>"technician","k"=>"","offset"=>$offset]);


                }
                else
                {

                    return $this->redirectToRoute("web_find_job");
                }
            }
            else
            {


                $this->mycontainer->get("session")->set("post_data",null);

                $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
                $cities =$this->mycontainer->get("city_service")->getAll();

                return $this->render('client/search_technician_result.html.twig',["users"=>$res,"domains"=>$domains["data"],"towns"=>$cities,"d"=>$d,"c"=>$c,"t"=>$t,"r"=>"technician","k"=>"","offset"=>$offset]);


            }
        }
        else
        {
            if(is_null($res))
            {


                if($d!=null && $c!=null)
                {
                    $da = $this->mycontainer->get("intervention_manager")->search($d,$c,$k,"client"," ",$limit,$offset);

                    $dat=[];

                    foreach ($da["data"] as $el)
                    {
                        $dat[]=$el->toArray();
                    }

                    $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
                    $cities =$this->mycontainer->get("city_service")->getAll();

                    return $this->render('client/search_job_result.html.twig',["users"=>$dat,"domains"=>$domains["data"],"towns"=>$cities,"d"=>$d,"c"=>$c,"t"=>$t,"r"=>"client","k"=>$k,"offset"=>$offset]);


                }
                else
                {

                    return $this->redirectToRoute("web_find_job");
                }
            }
            else
            {


                $this->mycontainer->get("session")->set("post_data",null);

                $domains = $this->mycontainer->get('domain_manager')->showList(20,0);
                $cities =$this->mycontainer->get("city_service")->getAll();

                return $this->render('client/search_job_result.html.twig',["users"=>$res,"domains"=>$domains["data"],"towns"=>$cities,"d"=>$d,"c"=>$c,"t"=>$t,"r"=>"client","k"=>$k,"offset"=>$offset]);


            }

        }

    }

    public function searchMoreResult(Request $request)
    {
        $d = (int) $request->request->get("d");
        $c = $request->request->get("c");
        $t= $request->request->get("t");
        $r= $request->request->get("r");
        $key= $request->request->get("key");

        $offset= $request->request->get("offset");
        $limit= 10;





        $res = $this->mycontainer->get('response_service');

        if(is_null($r))
        {
            $res->setStatut(401);
            $res->setContent(["users"=>[],"msg"=>"empty role"]);

            return $res->getResponse();
        }
        else
        {
            if(is_null($d))
            {
                $res->setStatut(401);
                $res->setContent(["users"=>[],"msg"=>"empty domain"]);

                return $res->getResponse();
            }

            if(is_null($c))
            {
                $res->setStatut(401);
                $res->setContent(["users"=>[],"msg"=>"empty city"]);

                return $res->getResponse();
            }

            if(is_null($t))
            {
                $res->setStatut(401);
                $res->setContent(["users"=>[],"msg"=>"empty type"]);

                return $res->getResponse();
            }

            if($r == "technician")
            {

                $data = $this->mycontainer->get("intervention_manager")->search($d,$c,"","technician",$t,$limit,$offset);


                $dat=[];

                foreach ($data["data"] as $el)
                {
                    if($t=="user")
                    {
                        $d=$el->toArray();
                        $d["date"] = $this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
                        $dat[]=$d;
                    }
                    else
                    {
                        $d=$el->toArray();
                        $d["company"]["date"] = $this->extension->timeagoFunction($el->getCompany()->getDate()->format('y-m-d H:i:s'));
                        $dat[]=$d;
                    }

                }

                $res->setStatut(201);
                $res->setContent(["users"=>$dat,"msg"=>""]);

                return $res->getResponse();




            }
            else
            {


                $data = $this->mycontainer->get("intervention_manager")->search($d,$c,$key,"client",$t,$limit,$offset);


                $dat=[];

                foreach ($data["data"] as $el)
                {
                    $d=$el->toArray();
                    $d["date"] = $this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
                    $dat[]=$d;

                }

                $res->setStatut(201);
                $res->setContent(["users"=>$dat,"msg"=>""]);

                return $res->getResponse();
            }
        }






    }

    public function showTechnician(Request $request,$name,$id)
    {
        if(is_null($this->getUser())) return $this->redirectToRoute("web_login");


        $id = $this->mycontainer->get("hash_service")->decrypt($id);

        $res = $this->mycontainer->get('user_manager')->getUserById($id);

        if(is_null($res)) return $this->render('client/404.html.twig',["userp"=>[]]);

        $res->setUid($this->mycontainer->get("hash_service")->encrypt($res->getId()));

        if(in_array($res->getRole()->getCode(),["ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY","ROLE_MANAGER_COMPANY"]))
        {
            $jobs = $this->mycontainer->get("intervention_manager")->userJobsDone($res,4,0);

            $stat = $this->mycontainer->get("intervention_manager")->clientStat($res);

            $notes = $this->mycontainer->get("intervention_manager")->getTechnicianNote($res);

            if($res->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
            {
                $comp = $res->getCompany();

                return $this->render('client/show_technician_profile.html.twig',["notes"=>$notes["data"],"userp"=>$res->toArray(),"jobs"=>$jobs["data"],
                    "nb_job"=>$stat["data"]["nb_job"],"spent"=>$stat["data"]["spent"],
                    "company"=>$comp->toArray()]);


            }
            else
            {
                return $this->render('client/show_technician_profile.html.twig',["notes"=>$notes["data"],"userp"=>$res->toArray(),"jobs"=>$jobs["data"],
                    "nb_job"=>$stat["data"]["nb_job"],"spent"=>$stat["data"]["spent"]]);
            }


        }

    }

    public function getNotifications(Request $request)
    {

        $res = $this->mycontainer->get('response_service');

        $cu = $this->getUser();
        if($cu==null){
            $res->setStatut(401);
            $res->setContent(["notifications"=>[],"msg"=>"empty role"]);

            return $res->getResponse();
        }
        else
        {
            $notifs = $this->mycontainer->get("notification_service")->getMyNotification($cu,5,0);

            $tab =[];

            foreach ($notifs["data"] as $no)
            {
                $tab[]=$no->toArray();
            }

            $res->setStatut(201);
            $res->setContent(["notifications"=>$tab,"msg"=>""]);

            return $res->getResponse();
        }




    }

    public function showNotification(Request $request,$id)
    {
        $res = $this->mycontainer->get('response_service');

        $cu = $this->getUser();

        if($cu==null) return $this->redirectToRoute("web_login");

        $data= $this->mycontainer->get("notification_service")->getOne($id);

        if($data["statut"]==true)
        {

        }
        else
        {
            $code = $data["data"]->getNotification()->getCode();

            if(in_array($code,[0,1,2,3,7,8])){

                //afficher le job

            }
            else if(in_array($code,[4,5,6])){

                //gestion des évaluations sur un job

            }
            else if(in_array($code,[8,9,10]))
            {
                //gestion des notifications à afficher sur le profile

            }
            else{
                //gestion des payements

            }
        }






    }

    public function showJobEvaluation(Request $request)
    {

    }

    public function getClientJob(Request $request)
    {

    }

    public function makeQuoteToTechnician(Request $request)
    {

    }

    public function showDomainCategories(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =200;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;



            $do = $this->mycontainer->get("domain_manager")->find($id);
            $data = $this->mycontainer->get("domain_manager")->showCategories($id,$limit,$offset);
            $res = $this->mycontainer->get("domain_manager")->showList( 100,0);


            return $this->render('client/dashboard_category.html.twig',["domain"=>$do["data"], "menu"=>"domain","domains"=>$res["data"], "categories"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }

    }

    public function showCategorySub(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {

            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));
                $data["budget"]=trim($request->request->get("budget"));

                $data["category"]=$request->request->get("category");

                $res = $this->mycontainer->get("category_manager")->createSub($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_categorie_scategories",["id"=>$id]);


            }


            if(!empty($request->query->get('limit')))
            {
                $limit = intval($request->query->get('limit'));
            }
            else $limit =200;

            if(!empty($request->query->get('offset')))
            {
                $offset = intval($request->query->get('offset'));
            }
            else $offset =0;



            $do = $this->mycontainer->get("category_manager")->find($id);
            $data = $this->mycontainer->get("category_manager")->showSubCategories($id,$limit,$offset);
            //$res = $this->mycontainer->get("category_manager")->showList( 200,0);


            return $this->render('client/dashboard_scategory.html.twig',["category"=>$do["data"], "menu"=>"domain","domains"=>array($do["data"]), "scategories"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }

    }

    public function subCategoryEdit(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {



            if($request->getMethod()=="POST")
            {
                $data["name_en"]=trim($request->request->get("name_en"));
                $data["name_fr"]=trim($request->request->get("name_fr"));
                $data["budget"]=trim($request->request->get("budget"));

                $data["id"]=$id;

                $sub= $this->mycontainer->get("category_manager")->findSub($id);

                $res = $this->mycontainer->get("category_manager")->editSub($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_categorie_scategories",["id"=>$sub["data"]->getCategory()->getId()]);


            }

            $data = $this->mycontainer->get("category_manager")->findSub($id);



            if($data["statut"]==false) return $this->render('client/404.html.twig',["userp"=>[]]);


            return $this->render('client/dashboard_scategory_edit.html.twig',["menu"=>"domain","category"=>$data["data"]]);
        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function subCategoryRemove(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        if(in_array($cu->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {
            $sub= $this->mycontainer->get("category_manager")->findSub($id);

            if($request->getMethod()=="POST")
            {
                $data["id"]=$id;



                $res = $this->mycontainer->get("category_manager")->removeSubCategory($cu->getRole()->getCode(),$data);

                if($res["statut"]==true) $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));
                else $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

                return $this->redirectToRoute("web_categorie_scategories",["id"=>$sub["data"]->getCategory()->getId()]);


            }
            else
            {
                return $this->redirectToRoute("web_categorie_scategories",["id"=>$sub["data"]->getCategory()->getId()]);
            }

        }
        else{
            return $this->render('client/dashboard.html.twig',["menu"=>"user"]);
        }
    }

    public function subCategoryCategory(Request $request)
    {
        $res = $this->mycontainer->get("category_manager")->findSubByCategory(intval($request->request->get("id")));


        $response = $this->mycontainer->get('response_service');
        $response->setStatut(201);

        $data=[];
        foreach ($res["data"] as $el)
        {
            $data[]=$el->toArray();
        }
        $response->setContent(["message"=>"","data"=>$data]);
        return $response->getResponse();
    }

    public function showJobProposals(Request $request,$slug)
    {
        $cu = $this->getUser();

        if($cu==null) return $this->redirectToRoute("web_login");

        $limit =1;

        $offset =0;

        $job = $this->mycontainer->get("intervention_manager")->getProposals($cu,$slug,$limit,$offset);

        if($job["statut"]==true)
        {
            return $this->render("client/job_proposals.html.twig",["job"=>null,"quotes"=>null,"message"=>$job["message"]]);

        }
        else
        {
            return $this->render("client/job_proposals.html.twig",["job"=>$job["data"]["job"],"quotes"=>$job["data"]["quotes"]]);

        }




    }
    public function showJobProposalsMore(Request $request,$slug)
    {
        $cu = $this->getUser();

        if($cu==null) return $this->redirectToRoute("web_login");

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =1;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =1;

        $job = $this->mycontainer->get("intervention_manager")->getProposals($cu,$slug,$limit,$offset);

        $res = $this->mycontainer->get('response_service');


        $data=[];

        if($job["statut"]==true)
        {
            $res->setStatut(401);


        }
        else
        {
            foreach ($job["data"]["quotes"] as $el)
            {
                $d=$el->toArray();
                $d["date"]=$this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));

                $intl = new \IntlDateFormatter($this->locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);


                $d["suggested_date"]=$intl->format($el->getSuggestedDate());
                $data[]=$d;
            }

            $res->setStatut(201);

        }

        $res->setContent(["quotes"=>$data]);


        return $res->getResponse();




    }

    public function editJob(Request $request,$slug,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $data["description"]=trim($request->request->get("description1"));
        $data["title"]=trim($request->request->get("title1"));
        $data["startdate"]=trim($request->request->get("start_date1"));


        $res = $this->mycontainer->get("intervention_manager")->editJob($cu,$slug,$data);


       $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

       if($res["code"]==201)
       {
           return $this->redirectToRoute("web_show_job",["slug"=>$res["data"]["job"]->getSlug()]);
       }
       else
       {
           return $this->render("client/403.html.twig",["userp"=>""]);
       }









    }

    public function validDevis(Request $request,$id)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $answer = (bool)trim($request->request->get("answer"));



        $res = $this->mycontainer->get("intervention_manager")->validDevis($cu,$answer,$id);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

        return $this->redirectToRoute("web_dashboard");

    }

    public function validInvitation(Request $request,$slug,$qid)
    {
        $cu = $this->getUser();
        if($cu==null) return $this->redirectToRoute("web_login");

        $answer = (bool)trim($request->request->get("answer"));


        $res = $this->mycontainer->get("intervention_manager")->validInvitation($cu,$answer,$qid);

        $this->addFlash('notice',$this->mycontainer->get('translator')->trans($res["message"]));

        return $this->redirectToRoute("web_show_job",["slug"=>$slug]);
    }

    public function findMoreDomainTechnicians(Request $request,$slug)
    {

        if(!empty($request->query->get('limit')))
        {
            $limit = intval($request->query->get('limit'));
        }
        else $limit =4;

        if(!empty($request->query->get('offset')))
        {
            $offset = intval($request->query->get('offset'));
        }
        else $offset =4;


        $res = $this->mycontainer->get("intervention_manager")->getTechniciansDomain($slug,$limit,$offset);

        $data =[];



        foreach ($res["data"]["jobs"] as $el)
        {
            $a = $el->toArray();
            $a["date"]=$this->extension->timeagoFunction($el->getDate()->format('y-m-d H:i:s'));
            $data[]=$a;
        }

        $res = $this->mycontainer->get('response_service');
        $res->setContent(["users"=>$data]);
        $res->setStatut(201);

        return $res->getResponse();
    }



}