<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 22/3/19
 * Time: 6:29 PM
 */

namespace App\Service;

use App\Entity\Bill;
use App\Entity\Company;
use App\Entity\JobAlert;
use App\Entity\Notification;
use App\Entity\Payment;
use App\Entity\Quote;
use App\Entity\SaveJob;
use App\Entity\Statut;
use App\Entity\SubCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Unirest;


use App\Entity\User;
use App\Entity\Intervention;
use App\Entity\Category;
use App\Entity\Location;
use App\Entity\Domain;
use App\Entity\File;
use App\Entity\Note;

class InterventionService
{

    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em,ContainerInterface $c)
    {
        $this->em = $em;
        $this->container = $c;
    }

    private function  slugify($str) {
  // Convert to lowercase and remove whitespace
  $str = strtolower(trim($str));

  // Replace high ascii characters
  $chars = array("ä", "ö", "ü", "ß");
  $replacements = array("ae", "oe", "ue", "ss");
  $str = str_replace($chars, $replacements, $str);
  $pattern = array("/(é|è|ë|ê)/", "/(ó|ò|ö|ô)/", "/(ú|ù|ü|û)/");
  $replacements = array("e", "o", "u");
  $str = preg_replace($pattern, $replacements, $str);

  // Remove puncuation
  $pattern = array(":", "!", "?", ".", "/", "'");
  $str = str_replace($pattern, "", $str);

  // Hyphenate any non alphanumeric characters
  $pattern = array("/[^a-z0-9-]/", "/-+/");
  $str = preg_replace($pattern, "-", $str);

  return $str;
}

    private function verifyData(User $user,array $data)
    {
        $r = $user->getRole();
        $role = $r->getCode();
        $required =["description","title","start_date", "category","scategory","domain","location_city","location_quater","location_detail","amount"];

        if(!in_array($role,["ROLE_CLIENT","ROLE_OPERATOR"])) return ["message"=>" operation denied","code"=>401,"statut"=>false,"data"=>""];

        if($role =="ROLE_OPERATOR")
        {
            $required[]="client_name";
            $required[]="client_email";
            $required[]="client_phone";
        }

        foreach ($required as $el)
        {
            if(!array_key_exists($el,$data)) return ["message"=>$el." field is required","code"=>401,"statut"=>false,"data"=>""];
        }

       if(array_key_exists("client_name",$data))
       {
           if(!is_null($data["client_name"]))
           {
               if(strlen($data["client_name"])<3) return ["message"=>" client name too short","code"=>401,"statut"=>false,"data"=>""];
           }
       }

        if(array_key_exists("client_phone",$data))
        {
            if(!is_null($data["client_phone"]))
            {
                if(strlen($data["client_phone"])!=9) return ["message"=>"invalid_phone","statut"=>false,"code"=>401,"data"=>""];
            }
        }
        if(array_key_exists("client_email",$data))
        {
            /*if($data["client_email"]!="")
            {
                if(!filter_var($data["client_email"], FILTER_VALIDATE_EMAIL)) return ["message"=>"invalid_email","statut"=>false,"code"=>401,"data"=>""];
            }*/
        }


        if(strlen($data["description"])<20) return ["message"=>"description field is too short ","code"=>401,"statut"=>false,"data"=>""];
        if(strlen($data["title"])<0) return ["message"=>"title field is too short ","code"=>401,"statut"=>false,"data"=>""];

       /* if(strlen($data["location_city"])<1) return ["message"=>"location city too short","code"=>401,"statut"=>false,"data"=>""];
        if(strlen($data["location_quater"])<1) return ["message"=>"location quater too short","code"=>401,"statut"=>false,"data"=>""];
        if(strlen($data["location_detail"])<20) return ["message"=>"location detail too short","code"=>401,"statut"=>false,"data"=>""];*/

        $category = $this->em->getRepository(Category::class)->findOneBy(array("id"=>$data["category"]));
        if(is_null($category)) return ["message"=>"category not found ","code"=>404,"statut"=>false,"data"=>""];

        $domain = $this->em->getRepository(Domain::class)->findOneBy(array("id"=>$data["domain"]));
        if(is_null($domain)) return ["message"=>"domain not found ","code"=>404,"statut"=>false,"data"=>""];

        if($category->getDomain() != $domain) return ["message"=>"category no belong to domain","code"=>401,"statut"=>false,"data"=>""];

        if(!is_null($data["scategory"]))
        {
            $scategory = $this->em->getRepository(SubCategory::class)->findOneBy(array("id"=>$data["scategory"]));
            if(is_null($scategory)) return ["message"=>"sub_category_not_found","code"=>404,"statut"=>false,"data"=>""];

            if($scategory->getCategory() != $category) return ["message"=>"sub category no belong to category","code"=>401,"statut"=>false,"data"=>""];
        }



        $intervention = new Intervention();
        $intervention->setReference($this->container->get("hash_service")->generateUUID("DIGII",12));
        //$intervention->setSlug($this->slugify($data["title"]));

        $intervention->setDescription($data["description"]);
        $intervention->setTitle($data["title"]);
        $intervention->setDomain($domain);
        $intervention->setCategory($category);
        $intervention->setBudget((int)$data["amount"]);

        if(!is_null($data["scategory"])){
            $intervention->setSubCategory($scategory);
        }

        $intervention->setStartDate(new \DateTime($data["start_date"]));

        if($role == "ROLE_CLIENT")
        {

            $intervention->setIsMain(true);
            $intervention->setClient($user);
        }
        else
        {
            $intervention->setIsMain(false);
            $intervention->setClientName($data["client_name"]);
            $intervention->setClientEmail($data["client_email"]);
            $intervention->setClientPhone($data["client_phone"]);
            $intervention->setOperator($user);
        }

        $location = new Location();
        $location->setCity($data["location_city"]);
        $location->setQuater($data["location_quater"]);
        $location->setDetail($data["location_detail"]);
        if(array_key_exists("location_longitude",$data)) $location->setLongitude($data["location_longitude"]);
        if(array_key_exists("location_latitude",$data)) $location->setLatitude($data["location_latitude"]);

        if(array_key_exists("location_file",$data))
        {
            $el = $data["location_file"];
            $file = new File();
            $file->setType($el["type"]);
            $file->setIsProfile(false);
            $file->setExtension($el["extension"]);
            $file->setName($el["name"]);
            $file->setPath($el["name"]);
            $file->setSize($el["size"]);

            $location->setFile($file);
        }

        $intervention->setLocation($location);
        $nb = 0;

        if(array_key_exists("files",$data))
        {

            foreach ($data["files"] as $el)
            {
                $file = new File();
                $file->setType($el["type"]);
                $file->setIsProfile(false);
                $file->setExtension($el["extension"]);
                $file->setName($el["name"]);
                $file->setPath($el["path"]);
                $file->setSize($el["size"]);

                $intervention->addFile($file);
                $nb++;
            }
        }

        $intervention->setNbFile($nb);



        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$intervention];
    }

    public function create(User $user,array $data)
    {

        $res = $this->verifyData($user,$data);

        if($res["statut"]==false)
            return $res;

        $intervention = $res["data"];

        foreach ($data["user"] as $item) {
            $u = $this->em->getRepository(User::class)->findOneBy(array("id"=>$item));

            if(!is_null($u))
            {
                $userDetail = $u->getUserDetail();

                if($userDetail->getDomains()->contains($intervention->getDomain()))
                {
                    $quote = new Quote();

                    $quote->setSuggestedDate($intervention->getStartDate());
                    if(strtolower($intervention->getLocation()->getCity())==strtolower($u->getLocation()->getCity()))
                    {
                        $quote->setAmount($intervention->getBudget()+2000);
                    }
                    else
                    {
                        $quote->setAmount($intervention->getBudget());
                    }

                    $quote->setIntervention($intervention);
                    $quote->setTechnician($u);

                    $intervention->addQuote($quote);



                }

            }

        }

        foreach ($data["company"] as $item) {
            $c = $this->em->getRepository(Company::class)->findOneBy(array("id"=>$item));

            if(!is_null($c))
            {
                $u = $c->getManager();

                //$userDetail = $u->getUserDetail();

                if($c->getDomains()->contains($intervention->getDomain()))
                {
                    $quote = new Quote();
                    $quote->setSuggestedDate($intervention->getStartDate());
                    if(strtolower($intervention->getLocation()->getCity())==strtolower($c->getLocation()->getCity()))
                    {
                        $quote->setAmount($intervention->getBudget()+2000);
                    }
                    else
                    {
                        $quote->setAmount($intervention->getBudget());
                    }

                    $quote->setIntervention($intervention);
                    $quote->setTechnician($u);

                    $intervention->addQuote($quote);


                }

            }

        }

        $this->em->persist($intervention);


        $quotes = $intervention->getQuotes();



        foreach ($quotes as $q)
        {
            $notif = new Notification();

            $notif->setUser($user);
            $notif->setIsActive(false);
            $notif->setCode(Notification::QUOTATION_INVITATION);
            $notif->setQuote($q);

            $statut = new Statut();
            $statut->setNotification($notif);
            $statut->setUser($q->getTechnician());
            $statut->setStatut(false);

            $this->em->persist($statut);

        }

        $this->em->flush();

        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$intervention];



    }

    public function userJobs(User $user,int $limit,int $offset)
    {
        $role = $user->getRole();

        if($role->getCode()=="ROLE_CLIENT")
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("client"=>$user,"isActive"=>true),array("statut"=>"ASC"),$limit,$offset);
        }
        else if(in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"]))
        {
            $jobs = $this->em->getRepository(Intervention::class)->findByTechnician($user->getId(),$limit,$offset);

        }
        /*else if($role->getCode()=="ROLE_OPERATOR")
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("isActive"=>true,"operator"=>$user),array("date"=>"DESC","statut"=>"ASC"),$limit,$offset);

        }*/
        else{
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("isActive"=>true),array("date"=>"DESC","statut"=>"ASC"),$limit,$offset);

        }

        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$jobs];


    }

    public function userJobsDone(User $user,int $limit,int $offset)
    {
        $role = $user->getRole();

        if($role->getCode()=="ROLE_CLIENT")
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("client"=>$user,"isActive"=>true),array("statut"=>"ASC"),$limit,$offset);
        }
        else if(in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"]))
        {
            $jobs = $this->em->getRepository(Intervention::class)->findByTechnicianDone($user->getId(),$limit,$offset);

        }
        else{
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("isActive"=>true),array("statut"=>"ASC"),$limit,$offset);

        }

        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$jobs];


    }

    public function showJob(string $slug,User $user=null,int $limit=20,int $offset=0)
    {
        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("slug"=>$slug));

        if(is_null($job)) return ["message"=>"job_not_found","code"=>404,"statut"=>false,"data"=>null];

        if(is_null($user)) $code="";
        else{
            $role = $user->getRole();
            $code =$role->getCode();

        }



        $quotes=[];

        $files = $this->em->getRepository(File::class)->findBy(array("intervention"=>$job));

        $quoteSatut= false;

        $nbJob=0;
        $spent=0;

        if($job->getIsMain()==true)
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("client"=>$job->getClient()));

            foreach ($jobs as $j)
            {
                if($j->getIsActive()==true)
                {
                    if($j->getStatut()!=Intervention::CANCELED)
                    {
                        $nbJob++;

                        if($j->getStatut()==Intervention::PAID)
                        {
                            $spent+=(int)$j->getBudget();
                        }
                    }
                }

            }

            if(!is_null($user))
            {
                if($user->getId() == $job->getClient()->getId())
                {
                    $quoteSatut= true;

                }
                else{
                    if(in_array($code,["ROLE_ADMIN","ROLE_SADMIN","ROLE_OPERATOR"]))
                    {
                        $quoteSatut= true;
                    }
                }
            }


        }
        else
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("clientName"=>$job->getClientName()));

            foreach ($jobs as $j)
            {
                if($j->getIsActive()==true)
                {
                    if($j->getStatut()!=Intervention::CANCELED)
                    {
                        $nbJob++;

                        if($j->getStatut()==Intervention::PAID)
                        {
                            $spent+=(int)$j->getBudget();
                        }
                    }
                }

            }

            if(in_array($code,["ROLE_ADMIN","ROLE_SADMIN","ROLE_OPERATOR"]))
            {
                $quoteSatut= true;
            }
        }



        if($quoteSatut == true)
        {


            if(in_array($job->getStatut(),[Intervention::PAID,Intervention::DONE]))
            {
                $testQ=true;
                $quotes= $this->em->getRepository(Quote::class)->findByJobValidated($job->getId(),1,0);
            }
            else
            {
                $quotes= $this->em->getRepository(Quote::class)->findByJob($job->getId(),$limit,$offset);
            }


        }


        $invite =false;
        $qid=0;

        $showDetails=false;


        $ownQuote = $this->em->getRepository(Quote::class)->findOneBy(array("intervention"=>$job,"technician"=>$user));


        if(!is_null($ownQuote))
        {
            $showDetails =true;
            if(!in_array($job->getStatut(),[Intervention::PAID,Intervention::DONE]))
            {
                if($ownQuote->getStatut()==Quote::NEW){
                    $invite =true;
                    $qid = $ownQuote->getId();
                }
            }
            else{
                $showDetails =true;
            }

        }






        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>["job"=>$job,"quotes"=>$quotes,"files"=>$files,"nb_job"=>$nbJob,"spent"=>$spent,"invite"=>$invite,"qid"=>$qid,"show_detail"=>$showDetails]];


    }

    public function getJobs(int $limit,int $offset)
    {
        $jobs = $this->em->getRepository(Intervention::class)->findNewJobs($limit,$offset);

        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>$jobs];
    }

    public function getJobsCategory(string $slug, int $limit,int $offset)
    {
        $jobs = $this->em->getRepository(Intervention::class)->findCategoryJobs($slug, $limit,$offset);

        $category=$this->em->getRepository(Category::class)->findOneBy(array("slug"=>$slug));

        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>["jobs"=>$jobs,"category"=>$category]];
    }

    public function getJobsDomain(string $slug, int $limit,int $offset)
    {
        $jobs = $this->em->getRepository(Intervention::class)->findDomainJobs($slug, $limit,$offset);
        $d=$this->em->getRepository(Domain::class)->findOneBy(array("slug"=>$slug));

        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>["jobs"=>$jobs,"domain"=>$d]];
    }

    public function makeQuote(User $user,int $type,string $date, string $message,int $id)
    {


        $role = $user->getRole();

        if(!in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON"]))
            return ["message"=>"operation_denied","code"=>401,"statut"=>false,"data"=>null];

        if($user->getIsValid()==false)
            return ["message"=>"profile_not_send","code"=>401,"statut"=>false,"data"=>null];


        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$id));

        if(is_null($job)) return ["message"=>"job_not_found","code"=>404,"statut"=>false,"data"=>null];

        if($role->getCode()=="ROLE_TECHNICIAN_PERSON")
        {
            $userDetail = $user->getUserDetail();

            if(!$userDetail->getDomains()->contains($job->getDomain()))
            {
                return ["message"=>"domain_not_matched","code"=>401,"statut"=>false,"data"=>null];
            }
            else
            {
                $old = $this->em->getRepository(Quote::class)->findOneBy(array("technician"=>$user,"intervention"=>$job));

                if(!is_null($old)) return ["message"=>"quotation_already_send","code"=>401,"statut"=>false,"data"=>null];

                $quote = new Quote();
                $quote->setStatut(Quote::SENDBYTECHNICIAN);
                $quote->setIntervention($job);
                $quote->setTechnician($user);
                $quote->setSuggestedDate(new \DateTime($date));
                if($type ==1)
                {
                    $quote->setType(Quote::DEVIS);
                    $quote->setMessage($message);
                    $quote->setIsActive(false);
                }
                else
                {
                    $notif = new Notification();

                    $notif->setUser($user);
                    $notif->setIsActive(true);
                    $notif->setCode(Notification::QUOTATION_NEW);
                    $notif->setQuote($quote);



                    $statut = new Statut();
                    $statut->setNotification($notif);

                    if($job->getIsMain()==false)
                    {
                        $statut->setUser($job->getOperator());
                    }
                    else
                    {
                        $statut->setUser($job->getClient());
                    }

                    $statut->setStatut(false);

                    $this->em->persist($statut);
                }




                $this->em->persist($quote);
                $this->em->flush();

                return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];

            }
        }
        else
        {
            $company = $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));

            if(is_null($company)) return ["message"=>"company_not_found","code"=>404,"statut"=>false,"data"=>null];

            if(!$company->getDomains()->contains($job->getDomain()))
            {
                return ["message"=>"domain_not_matched","code"=>401,"statut"=>false,"data"=>null];
            }
            else
            {
                $old = $this->em->getRepository(Quote::class)->findOneBy(array("technician"=>$user,"intervention"=>$job));

                if(!is_null($old)) return ["message"=>"quotation_already_send","code"=>401,"statut"=>false,"data"=>null];

                $quote = new Quote();
                $quote->setStatut(Quote::SENDBYTECHNICIAN);
                $quote->setIntervention($job);
                $quote->setTechnician($user);

                $quote->setSuggestedDate(new \DateTime($date));
                if($type ==1)
                {
                    $quote->setType(Quote::DEVIS);
                    $quote->setIsActive(false);
                    $quote->setMessage($message);
                }
                else
                {
                    $notif = new Notification();

                    $notif->setUser($user);
                    $notif->setIsActive(true);
                    $notif->setCode(Notification::QUOTATION_NEW);
                    $notif->setQuote($quote);



                    $statut = new Statut();
                    $statut->setNotification($notif);

                    if($job->getIsMain()==false)
                    {
                        $statut->setUser($job->getOperator());
                    }
                    else
                    {
                        $statut->setUser($job->getClient());
                    }

                    $statut->setStatut(false);

                    $this->em->persist($statut);
                }



                $this->em->persist($quote);
                $this->em->flush();



                return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];

            }


        }


    }

    public function alertJob(User $user,int $id)
    {

        if($user->getIsValid()==false)
            return ["message"=>"profile_not_send","code"=>401,"statut"=>false,"data"=>null];


        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$id));

        if(is_null($job)) return ["message"=>"job_not_found","code"=>404,"statut"=>false,"data"=>null];

        $alert = $this->em->getRepository(JobAlert::class)->findOneBy(array("intervention"=>$job,"user"=>$user));

        if(!is_null($alert))
        {
            return ["message"=>"alert_already_done","code"=>401,"statut"=>false,"data"=>null];
        }
        else
        {
            $a = new JobAlert();
            $a->setIntervention($job);
            $a->setUser($user);

            $this->em->persist($a);
            $this->em->flush();

            return ["message"=>"alert_done","code"=>201,"statut"=>true,"data"=>[]];

        }



    }

    public function addFavorite(User $user,int $id)
    {
        $role = $user->getRole();

        if(!in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"]))
            return ["message"=>"operation_denied","code"=>401,"statut"=>false,"data"=>null];

        if($user->getIsValid()==false)
            return ["message"=>"profile_not_send","code"=>401,"statut"=>false,"data"=>null];


        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$id));

        if(is_null($job)) return ["message"=>"job_not_found","code"=>404,"statut"=>false,"data"=>null];

        $fa = $this->em->getRepository(SaveJob::class)->findOneBy(array("intervention"=>$job,"technician"=>$user));

        if(!is_null($fa))
        {
            return ["message"=>"job_already_saved","code"=>401,"statut"=>false,"data"=>null];
        }
        else
        {
            $a = new SaveJob();
            $a->setIntervention($job);
            $a->setTechnician($user);

            $this->em->persist($a);
            $this->em->flush();

            return ["message"=>"job_saved","code"=>201,"statut"=>true,"data"=>[]];

        }



    }

    public function userStat(User $user)
    {
        $role = $user->getRole();



        if(in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"]))
        {
            $res=[];
            $res["done"]=0;
            $res["active"]=0;
            $res["awaiting"]=0;
            $res["earning"]=0;

            $quotes = $this->em->getRepository(Quote::class)->findBy(array("technician"=>$user));

            foreach ($quotes as $el)
            {
                if($el->getStatut()==Quote::DONE){
                    $res["done"]++;
                    $res["earning"] = $res["earning"] + (int)$el->getBill()->getAmount();
                }
                elseif ($el->getStatut()==Quote::NEW)
                {
                    $res["awaiting"]++;
                }
                elseif ($el->getStatut()==Quote::PAID)
                {
                    $res["active"]++;
                }


            }

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>$res];
        }

        elseif (in_array($role->getCode(),["ROLE_SADMIN","ROLE_OPERATOR","ROLE_ADMIN"]))
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("isActive"=>true));

            $res=[];
            $res["job"] = count($jobs);

            $clients = $this->em->getRepository(User::class)->countClient();
            $techs = $this->em->getRepository(User::class)->countTechnician();
            $comps = $this->em->getRepository(Company::class)->countCompany();

            $res["company"]=$comps[0]["nb"];
            $res["clients"]=$clients[0]["nb"];
            $res["techs"]=$techs[0]["nb"];

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>$res];

        }

        elseif ($role->getCode()=="ROLE_CLIENT")
        {
            $quotes = $this->em->getRepository(Intervention::class)->findBy(array("client"=>$user,"isActive"=>true));

            $res=[];

            $res["jobs"] =count($quotes);
            $res["earning"] =0;
            $res["reviewed"] =0;

            foreach ($quotes as $el)
            {
                if($el->getStatut()==Intervention::PAID)
                {
                    $q = $this->em->getRepository(Quote::class)->findOneBy(array("intervention"=>$el,"isActive"=>true,"statut"=>Quote::PAID));

                    if(!is_null($q))
                    {
                        $res["earning"]+=(int)$q->getBill()->getAmount();
                    }

                }
                elseif (in_array($el->getStatut(),[Intervention::NEW,Intervention::ACCEPTED]))
                {
                    $res["reviewed"]++;
                }
            }

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>$res];


        }
        else
        {

        }
    }

    public function userFavoriteJobs(User $user,int $limit,int $offset)
    {
        $role = $user->getRole();
        $data=[];


        if(in_array($role->getCode(),["ROLE_MANAGER_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY"]))
        {
            $jobs = $this->em->getRepository(SaveJob::class)->findByTechnician($user->getId(),$limit,$offset);

            foreach ($jobs as $el)
            {
                $data[]=$el->getIntervention();
            }

        }

        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$data];




    }

    public function getAlerts(User $user,int $limit,int $offset)
    {
        $da=[];
        if(in_array($user->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {
            $da = $this->em->getRepository(JobAlert::class)->findBy(array("isActive"=>true),array("date"=>"DESC"),$limit,$offset);
        }

        return ["message"=>"","code"=>201,"statut"=>true,"data"=>$da];
    }

    public function deleteJob(User $user, int $id)
    {
        if(in_array($user->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN"]))
        {
             $d = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$id));

             if(!is_null($d))
             {
                 $d->setIsActive(false);

                 $this->em->persist($d);

                 $this->em->flush();

                 return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];
             }
             else
             {
                 return ["message"=>"intervention_not_found","code"=>404,"statut"=>false,"data"=>[]];
             }



        }
        else if($user->getRole()->getCode() == "ROLE_CLIENT")
        {

            $d = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$id));

            if(!is_null($d))
            {
                if($d->getIsMain())
                {
                    if($d->getClient()->getId() == $user->getId())
                    {
                        $d->setIsActive(false);

                        $this->em->persist($d);

                        $this->em->flush();

                        return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];
                    }
                    return ["message"=>"operation_denied","code"=>401,"statut"=>false,"data"=>null];

                }
                return ["message"=>"operation_denied","code"=>401,"statut"=>false,"data"=>null];


            }
            else
            {
                return ["message"=>"intervention_not_found","code"=>404,"statut"=>false,"data"=>[]];
            }
        }
        else{
            return ["message"=>"operation_denied","code"=>401,"statut"=>false,"data"=>null];
        }


    }

    public function deleteAlert(User $user, int $id)
    {
        if(in_array($user->getRole()->getCode(),["ROLE_SADMIN","ROLE_ADMIN"]))
        {
            $d= $this->em->getRepository(JobAlert::class)->getSpecific($id);

            foreach ($d as $el)
            {
                $el->setIsActive(false);

                $this->em->persist($el);
            }

            if(count($d)>0)
            {
                $this->em->flush();
            }

        }

        return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];

    }

    public function search(int $domain,string $city,string $key,string $type,string $ut,int $limit,int $offset)
    {
        if($type =="technician")
        {
            if($ut == "user")
            {

                $users = $this->em->getRepository(User::class)->findTechnicianPerson($domain,$city,$limit,$offset);

            }
            elseif($ut =="company")
            {
                $users = $this->em->getRepository(User::class)->findTechnicianCompany($domain,$city,$limit,$offset);
            }
            else
            {
                $users = $this->em->getRepository(User::class)->findTechnicianAll($domain,$city,$limit,$offset);
            }

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>$users];
        }
        else
        {
            $jobs = $this->em->getRepository(Intervention::class)->findByKey($domain,$city,$key,$limit,$offset);

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>$jobs];
        }

    }

    public function clientStat(User $user)
    {
        $role = $user->getRole();

        $nbJob=0;
        $spent=0;

        if($role->getCode()=="ROLE_CLIENT")
        {
            $jobs = $this->em->getRepository(Intervention::class)->findBy(array("client"=>$user));

            foreach ($jobs as $j)
            {
                if($j->getIsActive()==true)
                {
                    if($j->getStatut()!=Intervention::CANCELED)
                    {
                        $nbJob++;

                        if($j->getStatut()==Intervention::PAID)
                        {
                            $spent+=(int)$j->getBudget();
                        }
                    }
                }

            }

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>["nb_job"=>$nbJob,"spent"=>$spent]];
        }
        elseif (in_array($role->getCode(),["ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY","ROLE_MANAGER_COMPANY"]))
        {
            $quotes = $this->em->getRepository(Quote::class)->findBy(array("technician"=>$user,"statut"=>Quote::DONE,"isActive"=>true));

            foreach ($quotes as $q)
            {
                $j = $q->getIntervention();
                if(!is_null($j))
                {
                    if($j->getIsActive()==true)
                    {
                        if($j->getStatut()!=Intervention::CANCELED)
                        {
                            $nbJob++;

                            if($j->getStatut()==Intervention::PAID)
                            {
                                $spent+=(int)$j->getBudget();
                            }
                        }
                    }
                }


            }

            return ["message"=>"","code"=>201,"statut"=>true,"data"=>["nb_job"=>$nbJob,"spent"=>$spent]];

        }
        else
        {
            return ["message"=>"","code"=>201,"statut"=>true,"data"=>["nb_job"=>1111,"spent"=>1111]];
        }

    }

    public function getTechnicianNote(User $user)
    {
        $notes = $this->em->getRepository(Note::class)->findBy(array("technician"=>$user),array("date"=>"DESC"),4,0);

        return ["message"=>"","code"=>201,"statut"=>false,"data"=>$notes];


    }

    public function getProposals(User $user,string  $slug,int $limit,int $offset)
    {
        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("slug"=>$slug));

        if(is_null($job))
        {
            return ["message"=>"job_not_found","code"=>404,"statut"=>true,"data"=>[]];
        }
        else
        {
            if($job->getIsActive()==false) return ["message"=>"job_not_found","code"=>404,"statut"=>true,"data"=>[]];
            else
            {

                if($job->getIsMain()==true)
                {

                    if(in_array($user->getRole()->getCode(),["ROLE_OPERATOR","ROLE_ADMIN"]))
                    {

                    }
                    else
                    {
                        if($job->getClient()->getId()!=$user->getId())
                        {
                            return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
                        }
                    }

                }
                else
                {
                    if(!in_array($user->getRole()->getCode(),["ROLE_OPERATOR","ROLE_ADMIN"]))
                    {
                        return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
                    }

                }




                if(in_array($job->getStatut(),[Intervention::PAID,Intervention::DONE]))
                {
                    $quotes = $this->em->getRepository(Quote::class)->findByJobValidated($job->getId(),$limit,$offset);
                }
                else
                {
                    $quotes = $this->em->getRepository(Quote::class)->findByJob($job->getId(),$limit,$offset);
                }



                return ["message"=>"","code"=>201,"statut"=>false,"data"=>["quotes"=>$quotes,"job"=>$job]];
            }

        }
    }

    public function editJob(User $user, string  $slug,array $data)
    {

        $job = $this->em->getRepository(Intervention::class)->findOneBy(array("slug"=>$slug));

        if(is_null($job)) return ["message"=>"job_not_found","code"=>401,"statut"=>true,"data"=>[]];

        if($job->getHasEdit()==true) return ["message"=>"job_already_edited","code"=>401,"statut"=>true,"data"=>[]];

        if($job->getIsMain()==true)
        {
            if($user->getId() != $job->getClient()->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
        }
        else
        {
            if($user->getId() != $job->getOperator()->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];

        }

       $req=["title","description","startdate"];

        foreach ($req as $el)
        {
            if(!array_key_exists($el,$data)) return ["message"=>"field ".$el." is empty ","code"=>401,"statut"=>true,"data"=>[]];
        }

        if(strlen($data["description"])<20) return ["message"=>"description field is too short ","code"=>401,"statut"=>false,"data"=>""];
        if(strlen($data["title"])<0) return ["message"=>"title field is too short ","code"=>401,"statut"=>false,"data"=>""];

        $job->setTitle($data["title"]);
        $job->setDescription($data["description"]);
        $job->setStartDate(new \DateTime($data["startdate"]));

        $job->setHasEdit(true);

        $this->em->persist($job);
        $this->em->flush();

        return ["message"=>"job_updated","code"=>201,"statut"=>false,"data"=>["job"=>$job]];




    }

    public function getDevis(int $limit,$offset)
    {
        $res = $this->em->getRepository(Quote::class)->findBy(array("type"=>Quote::DEVIS,"statut"=>Quote::NEW,"isActive"=>false),array("date"=>"DESC"),$limit,$offset);

        return ["message"=>"","code"=>201,"statut"=>false,"data"=>$res];

    }

    public function validDevis(User $user,bool $answer,int $id)
    {

        if(!in_array($user->getRole()->getCode(),["ROLE_OPERATOR","ROLE_ADMIN"]))
        {
            return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
        }
        else
        {
            $quote = $this->em->getRepository(Quote::class)->findOneBy(array("id"=>$id));

            if(is_null($quote)) return ["message"=>"devis_not_found","code"=>401,"statut"=>true,"data"=>[]];

            if($answer == true)
            {
                $quote->setStatut(Quote::VALIDATED);
                $quote->setIsActive(true);

                $notif = new Notification();

                $notif->setUser($quote->getTechnician());
                $notif->setIsActive(true);
                $notif->setCode(Notification::QUOTATION_NEW);
                $notif->setQuote($quote);



                $statut = new Statut();
                $statut->setNotification($notif);

                if($quote->getIntervention()->getIsMain()==false)
                {
                    $statut->setUser($quote->getIntervention()->getOperator());
                }
                else
                {
                    $statut->setUser($quote->getIntervention()->getClient());
                }

                $statut->setStatut(false);

                $this->em->persist($quote);

                $this->em->persist($statut);


            }
            else
            {
                $quote->setStatut(Quote::REFUSED);
                $this->em->persist($quote);


            }

            $this->em->flush();

            return ["message"=>"operation_did","code"=>201,"statut"=>true,"data"=>[]];


        }
    }

    public function validInvitation(User $user,int $answer, int $id)
    {
        $quote = $this->em->getRepository(Quote::class)->findOneBy(array("id"=>$id));

        if(is_null($quote)) return ["message"=>"devis_not_found","code"=>401,"statut"=>true,"data"=>[]];

        if($quote->getTechnician()->getId() != $user->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];

        if($quote->getStatut()!=Quote::NEW) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];

        if($answer ==true)
        {
            $quote->setStatut(Quote::ACCEPTEDBYTECHNICIAN);
        }
        else
        {
            $quote->setStatut(Quote::REFUSEDBYTECHNICIAN);
        }


        if($answer ==true)
        {
            $notif = new Notification();

            $notif->setUser($user);
            $notif->setIsActive(true);
            $notif->setCode(Notification::QUOTATION_INVITATION_ACCEPTED);
            $notif->setQuote($quote);



            $statut = new Statut();
            $statut->setNotification($notif);

            if($quote->getIntervention()->getIsMain()==false)
            {
                $statut->setUser($quote->getIntervention()->getOperator());
            }
            else
            {
                $statut->setUser($quote->getIntervention()->getClient());
            }

            $statut->setStatut(false);
            $this->em->persist($statut);
        }



        $this->em->persist($quote);



        $this->em->flush();

        return ["message"=>"operation_did","code"=>201,"statut"=>false,"data"=>[]];



    }

    public function getTechniciansDomain(string $slug, int $limit,int $offset)
    {

        $jobs = $this->em->getRepository(User::class)->findTechniciansDomain($slug, $limit,$offset);
        $d=$this->em->getRepository(Domain::class)->findOneBy(array("slug"=>$slug));

        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>["jobs"=>$jobs,"domain"=>$d]];

    }

    public function getTechniciansAll(int $limit,int $offset)
    {

        $jobs = $this->em->getRepository(User::class)->findTechniciansAll( $limit,$offset);


        return ["message"=>" ","code"=>201,"statut"=>true,"data"=>["users"=>$jobs]];

    }

    public function getClientJobForInvitation(User $user,int $techId,int $limit,int $offset)
    {
        $tech = $this->em->getRepository(User::class)->findOneBy(array("id"=>$techId));

        if(is_null($tech)) return ["message"=>"user_not_found","code"=>404,"statut"=>true,"data"=>["jobs"=>[]]];

        $domains=[];

        if($tech->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
        {
            foreach ($tech->getCompany()->getDomains() as $d)
            {
                $domains[]=$d->getId();
            }
        }
        elseif($tech->getRole()->getCode()=="ROLE_TECHNICIAN_PERSON")
        {

            foreach ($tech->getUserDetail()->getDomains() as $d)
            {
                $domains[]=$d->getId();
            }
        }

        $jobs = $this->em->getRepository(Intervention::class)->clientJobForInvitaion($user->getId(),$techId,$user->getRole()->getCode(),$domains,$limit,$offset);

        return ["message"=>"","code"=>201,"statut"=>false,"data"=>["jobs"=>$jobs]];


    }

    public function sendInvitation(User $user,int $jobId,int $techId)
    {
        if($user->getRole()->getCode()=="ROLE_CLIENT")
        {
            $job = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$jobId,"client"=>$user->getId()));
        }
        elseif($user->getRole()->getCode()=="ROLE_OPERATOR")
        {
            $job = $this->em->getRepository(Intervention::class)->findOneBy(array("id"=>$jobId,"operator"=>$user->getId()));

        }
        else
        {
            return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
        }

        if(is_null($job)) return ["message"=>"job_not_found","code"=>404,"statut"=>true,"data"=>[]];

        $tech = $this->em->getRepository(User::class)->findOneBy(array("id"=>$techId));

        if(is_null($tech)) return ["message"=>"user_not_found","code"=>404,"statut"=>true,"data"=>[]];

        if($tech->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
        {
            if(!$tech->getCompany()->getDomains()->contains($job->getDomain()))
            {
                return ["message"=>"user_not_matched","code"=>401,"statut"=>true,"data"=>[]];
            }
        }
        elseif($tech->getRole()->getCode()=="ROLE_TECHNICIAN_PERSON")
        {
            if(!$tech->getUserDetail()->getDomains()->contains($job->getDomain()))
            {
                return ["message"=>"user_not_matched","code"=>401,"statut"=>true,"data"=>[]];
            }
        }
        else{

            return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
        }

        $old = $this->em->getRepository(Quote::class)->findOneBy(array("intervention"=>$job,"technician"=>$tech));

        if(!is_null($old)) return ["message"=>"quotation_already_send","code"=>401,"statut"=>true,"data"=>[]];

        $quote = new Quote();

        $quote->setSuggestedDate($job->getStartDate());
        if(strtolower($job->getLocation()->getCity())==strtolower($job->getLocation()->getCity()))
        {
            $quote->setAmount($job->getBudget()+2000);
        }
        else
        {
            $quote->setAmount($job->getBudget());
        }

        $quote->setIntervention($job);
        $quote->setTechnician($tech);




        $notif = new Notification();

        $notif->setUser($user);
        $notif->setIsActive(false);
        $notif->setCode(Notification::QUOTATION_INVITATION);
        $notif->setQuote($quote);

        $statut = new Statut();
        $statut->setNotification($notif);
        $statut->setUser($quote->getTechnician());
        $statut->setStatut(false);

        $this->em->persist($quote);
        $this->em->persist($statut);

        $this->em->flush();


        return ["message"=>"operation_did","code"=>201,"statut"=>false,"data"=>["quote"=>$quote]];



    }

    public function getQuote(User $user,int $id)
    {
        $role = $user->getRole();

        if(!in_array($role->getCode(),["ROLE_CLIENT","ROLE_OPERATOR"]))
        {
            return ["message"=>"operation_denied1","code"=>401,"statut"=>true,"data"=>[]];
        }
        else
        {
            $quote = $this->em->getRepository(Quote::class)->findOneBy(array("id"=>$id));

            if(is_null($quote)) return ["message"=>"quotation_not_found","code"=>401,"statut"=>true,"data"=>[]];

            $int = $quote->getIntervention();

            if($int->getIsMain()==true)
            {
                if($user->getId()!= $int->getClient()->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];
            }
            else
            {
                if($user->getId()!= $int->getOperator()->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];

            }


            return ["message"=>"cool","code"=>201,"statut"=>false,"data"=>$quote];

        }
    }

    public function makePayment(User $user,Quote $quote)
    {
        $headers = array('Accept' => 'application/json',
            'Authorization'=>'Bearer sADnotJRf5Fwu1fSKgm8ogZGxjQw',
            'Content-Type'=>'application/json');

        $quote->setReference($this->container->get("hash_service")->generateUUID("DIGIQ",12));

        $this->em->persist($quote);
        $this->em->flush();

        $data=[
            "merchant_key"=>"5d936434",
            "currency"=> "OUV",
            "order_id"=> $quote->getReference(),
          "amount"=> 1000,
          "return_url"=> "http://69.55.55.225".$this->container->get("router")->generate("web_payment_callback",["ref"=>$quote->getReference(),"slug"=>$quote->getIntervention()->getSlug()]),
          "cancel_url"=> "http://69.55.55.225".$this->container->get("router")->generate("web_payment_callback_cancel",["ref"=>$quote->getReference(),"slug"=>$quote->getIntervention()->getSlug()]),
          "notif_url"=> "http://69.55.55.225".$this->container->get("router")->generate("web_payment_notification"),
          "lang"=> "fr",
          "reference"=> "DIGITRAV"
        ];

        $body = Unirest\Request\Body::json($data);

        $response = Unirest\Request::post('https://api.orange.com/orange-money-webpay/dev/v1/webpayment', $headers, $body);

        if($response->code==201)
        {
            $p = new Payment();
            $p->setReference($quote->getReference());
            $p->setAmount(1000);
            $p->setType(Payment::ORANGE_MONEY);

            $int = $quote->getIntervention();
            if($int->getIsMain()==true)
            {
                $p->setClient($int->getClient()->getSurname()." ".$int->getClient()->getName());

            }
            else
            {
                $p->setClient($int->getClientName());

            }

            $p->setReceiver("DIGITRAV");

            $p->setPayToken($response->body->pay_token);
            $p->setPayUrl($response->body->payment_url);
            $p->setNotifToken($response->body->notif_token);


            $this->em->persist($p);

            $this->em->flush();

        }


        return ["message"=>"","code"=>$response->code,"statut"=>false,"data"=>$response];
    }

    public function verifyPayment(string $orderId,int $amount,string $payToken)
    {
        $headers = array('Accept' => 'application/json',
            'Authorization'=>'Bearer sADnotJRf5Fwu1fSKgm8ogZGxjQw',
            'Content-Type'=>'application/json');

        $data=[
            "order_id"=> $orderId,
            "amount"=> $amount,
            "pay_token"=>$payToken
        ];

        $body = Unirest\Request\Body::json($data);

        $response = Unirest\Request::post('https://api.orange.com/orange-money-webpay/dev/v1/webpayment', $headers, $body);

        return ["message"=>"","code"=>$response->code,"statut"=>false,"data"=>$response];

    }

    public function getNotification(string $status,string $notifToken)
    {
        $tab=["SUCCESS"=>"payment_is_done",
            "INITIATED"=>"transaction_waiting_user",
            "PENDING"=>"transaction_in_progress",
            "EXPIRED"=>"transaction_expired",
            "FAILED"=>"payment_has_failed"
        ];

        if(!array_key_exists($status,$tab)) return ["message"=>"BAD_RESPONSE","code"=>401,"statut"=>true,"data"=>[]];


        if($status =="SUCCESS")
        {
            $payment = $this->em->getRepository(Payment::class)->findOneBy(array("notifToken"=>$notifToken));

            if(!is_null($payment))
            {
                $quote = $this->em->getRepository(Quote::class)->findOneBy(array("reference"=>$payment->getReference()));

                if(!is_null($quote))
                {
                    $quote->setStatut(Quote::PAID);

                    $payment->setStatut(true);

                    $this->em->persist($payment);




                    $int = $quote->getIntervention();

                    $int->setStatut(Intervention::PAID);


                    $bill = new Bill();
                    $bill->setPayment($payment);
                    $bill->setReference($this->container->get("hash_service")->generateUUID("DIGIB",12));
                    $bill->setReason(Bill::PAID);
                    $bill->setAmount($payment->getAmount());

                    $quote->setBill($bill);


                    $this->em->persist($int);

                    $quotes = $int->getQuotes();

                    foreach ($quotes as $elt)
                    {
                        if($elt->getId()!= $quote->getId())
                        {
                            $elt->setStatut(Quote::REFUSED);
                            $this->em->persist($elt);
                        }

                    }

                    $notif = new Notification();

                    $notif->setIsActive(true);
                    $notif->setQuote($quote);
                    $notif->setUser($payment->getClient());
                    $notif->setCode(Notification::QUOTATION_ACCEPTED);

                    $sta = new Statut();
                    $sta->setUser($quote->getTechnician());
                    $sta->setNotification($notif);


                    $this->em->persist($quote);
                    $this->em->persist($sta);
                    $this->em->flush();

                    return ["message"=>"","code"=>201,"statut"=>false,"data"=>[]];

                }
                else{
                    return ["message"=>"quotation_not_found","code"=>401,"statut"=>true,"data"=>[]];
                }

            }
            else
            {
                return ["message"=>"transaction_not_found","code"=>401,"statut"=>true,"data"=>[]];
            }
        }


        return ["message"=>$tab[$status],"code"=>401,"statut"=>true,"data"=>[]];


    }

    public function getQuoteAfterPayment(User $user, string $ref)
    {
        $q=$this->em->getRepository(Quote::class)->findOneBy(array("reference"=>$ref));

        if(!is_null($q))
        {
            $p = $this->em->getRepository(Payment::class)->findOneBy(array("reference"=>$q->getReference()));

            if(is_null($p)) return ["message"=>"transaction_not_found","code"=>404,"statut"=>true,"data"=>[]];

          //  if($p->getClient()->getId() != $user->getId()) return ["message"=>"operation_denied","code"=>401,"statut"=>true,"data"=>[]];


            if($p->getStatut()==false)
            {
                $headers = array('Accept' => 'application/json',
                    'Authorization'=>'Bearer sADnotJRf5Fwu1fSKgm8ogZGxjQw',
                    'Content-Type'=>'application/json');

                $data=[
                    "order_id"=> $q->getReference(),
                    "amount"=> (int)$q->getAmount(),
                    "pay_token"=>$p->getPayToken()
                ];

                $body = Unirest\Request\Body::json($data);

                $response = Unirest\Request::post('https://api.orange.com/orange-money-webpay/dev/v1/transactionstatus', $headers, $body);


                if($response->body->status == "SUCCESS")
                {
                        $q->setStatut(Quote::PAID);

                        $p->setStatut(true);
                        $this->em->persist($p);


                        $int = $q->getIntervention();

                        $int->setStatut(Intervention::PAID);

                        $this->em->persist($int);


                        $bill = new Bill();
                        $bill->setPayment($p);
                        $bill->setReference($this->container->get("hash_service")->generateUUID("DIGIB",12));
                        $bill->setReason(Bill::PAID);
                        $bill->setAmount($p->getAmount());

                        $q->setBill($bill);



                        $quotes = $int->getQuotes();

                        foreach ($quotes as $elt)
                        {
                            if($elt->getId()!= $q->getId())
                            {
                                $elt->setStatut(Quote::REFUSED);
                                $this->em->persist($elt);
                            }

                        }

                        $notif = new Notification();

                        $notif->setIsActive(true);
                        $notif->setQuote($q);

                        $int = $q->getIntervention();

                    if($int->getIsMain()==true)
                    {
                        $notif->setUser($int->getClient());

                    }
                    else
                    {
                        $notif->setUser($int->getOperator());

                    }


                        $notif->setCode(Notification::QUOTATION_ACCEPTED);

                        $sta = new Statut();
                        $sta->setUser($q->getTechnician());
                        $sta->setNotification($notif);


                        $this->em->persist($q);
                        $this->em->persist($sta);
                        $this->em->flush();

                        return ["message"=>"payment_is_done","code"=>201,"statut"=>false,"data"=>[]];

                }
                else
                {
                    $tab=["SUCCESS"=>"payment_is_done",
                        "INITIATED"=>"transaction_waiting_user",
                        "PENDING"=>"transaction_in_progress",
                        "EXPIRED"=>"transaction_expired",
                        "FAILED"=>"payment_has_failed"
                    ];

                    if(!array_key_exists($response->body->status,$tab)) return ["message"=>"BAD_RESPONSE","code"=>401,"statut"=>true,"data"=>[]];


                    return ["message"=>$tab[$response->body->status],"code"=>$response->code,"statut"=>false,"data"=>$response];
                }




            }
            else
            {
                return ["message"=>"payment_is_done","code"=>201,"statut"=>false,"data"=>$q];
            }



        }

        return ["message"=>"quotation_not_found","code"=>401,"statut"=>true,"data"=>[]];

    }




}