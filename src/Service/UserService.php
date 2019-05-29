<?php
/**
 * Created by PhpStorm.
 * User: rostandnj
 * Date: 13/3/19
 * Time: 2:02 PM
 */
namespace App\Service;



use App\Entity\Domain;
use App\Entity\Notification;
use App\Entity\Statut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

use App\Entity\Company;
use App\Entity\User;
use App\Entity\Role;
use App\Entity\UserDetail;
use App\Entity\Location;
use App\Entity\File;
use App\Entity\AccountValidation;

class UserService
{
    private $em;
    private $container;
    private $answer;
    private $encoder;
    private $jwt;

    public function __construct(EntityManagerInterface $em,ContainerInterface $c,UserPasswordEncoderInterface $passwordEncoder,JWTTokenManagerInterface $jwt)
    {
        $this->em = $em;
        $this->container = $c;
        //$this->answer =[];
        $this->encoder = $passwordEncoder;
        $this->jwt = $jwt;
    }

    private function getRandom(int $n)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers='0123456789';
        $maj='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {

            if($i ==$n-1)
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

        return $randomString;
    }

    private function passwordStrength(string $password) {
        $returnVal = true;
        if ( strlen($password) < 8 ) {
            $returnVal = false;
        }
        if ( !preg_match("#[0-9]+#", $password) ) {
            $returnVal = false;
        }
       /* if ( !preg_match("#[a-z]+#", $password) ) {
            $returnVal = False;
        }*/
        if ( !preg_match("#[A-Z]+#", $password) ) {
            $returnVal = false;
        }
        /*if ( !preg_match("/[\'^£$%&*()}{@#~?><>,|=_+!-]/", $password) ) {
            $returnVal = False;
        }*/
        return $returnVal;
    }

    private function verifyBasicInfo(array $data)
    {
        $requireField =["name","email","phone","password","gender","city","quater"];

        foreach ($requireField as $el)
        {
            if(!array_key_exists($el,$data))
            {
                return ["message"=>$el." field is required","statut"=>false,"code"=>"FIELD_REQUIRED"];
            }
        }

        if(!filter_var($data["email"], FILTER_VALIDATE_EMAIL))
        {
            return ["message"=>"invalid_email","statut"=>false,"code"=>"EMAIL_INVALID"];
        }

        if($this->passwordStrength($data["password"])==false)
        {
            return ["message"=>"invalid_password","statut"=>false,"code"=>"PASSWORD_PATTERN"];
        }

        if(strlen($data["name"])<1)
        {
            return ["message"=>"name_too_short","statut"=>false,"code"=>"NAME_SHORT"];
        }

        if(strlen($data["phone"])!=9)
        {
            return ["message"=>"invalid_phone","statut"=>false,"code"=>"PHONE_LENGTH"];
        }

        if (!preg_match('/^[0-9]+$/', $data["phone"]))
        {
            return ["message"=>"invalid_phone","statut"=>false,"code"=>"PHONE_NUMBER"];

        }

        /*if(strlen($data["city"])<1)
        {
            return ["message"=>"city name too short","statut"=>false,"code"=>"CITY_SHORT"];
        }*/

        /*if(strlen($data["quater"])<1)
        {
            return ["message"=>"quater name too short","statut"=>false,"code"=>"QUATER_SHORT"];
        }*/

        if(!in_array($data["gender"],[0,1]))
        {
            return ["message"=>"invalid_gender","statut"=>false,"code"=>"GENDER_INVALID"];
        }

        $user = $this->em->getRepository(User::class)->findOneBy(array("email"=>$data["email"]));

        if(!is_null($user))  return ["message"=>"email_already_used","statut"=>false,"code"=>"EMAIL_USED"];

        $user = $this->em->getRepository(User::class)->findOneBy(array("phone"=>$data["phone"]));

        if(!is_null($user))  return ["message"=>"phone_already_used","statut"=>false,"code"=>"PHONE_USED"];

        if(!array_key_exists("longitude",$data))
        {
            $data["longitude"]="";
        }
        if(!array_key_exists("latitude",$data))
        {
            $data["latitude"]="";
        }
        if(!array_key_exists("surname",$data))
        {
            $data["surname"]="";
        }

        return ["message"=>"","statut"=>true,"data"=>$data];

    }

    private function hydrateBasicInfo(array $data,string $role_name)
    {

        $user = new User();
        $user->setName($data["name"]);
        $user->setSurname($data["surname"]);
        $user->setEmail($data["email"]);
        $user->setPhone($data["phone"]);
        $user->setPassword($this->container->get("security.password_encoder")->encodePassword($user,$data["password"] ));
        $user->setGender((boolean)$data["gender"]);
        $user->setIsValid(false);
        $user->setIsClose(false);
        $user->setIsActive(false);

        $location = new Location();
        $location->setCity($data["city"]);
        $location->setQuater($data["quater"]);
        $location->setLongitude($data["longitude"]);
        $location->setLatitude($data["latitude"]);

        $user->setLocation($location);

        $role = $this->em->getRepository(Role::class)->findOneBy(array("code"=>$role_name));
        $user->setRole($role);


        $user->setToken(uniqid());

        return $user;
    }

    public function createClient(array $data)
    {

        $result = $this->verifyBasicInfo($data);

        if($result["statut"]==false)
        {
            return $result;
        }
        else
        {
            $user = $this->hydrateBasicInfo($result["data"],"ROLE_CLIENT");
            $this->em->persist($user);
            $this->em->flush();

            $this->container->get("mail_manager")->welcome($user->getId(),$user->getUsername(),$user->getEmail(),$user->getToken());

            return ["message"=>"","statut"=>true,"data"=>$user];


        }

    }

    public function createTechnician(array $data,string $role_name)
    {
        $result = $this->verifyBasicInfo($data);

        if(!in_array( $role_name,["ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"]))
        {
            $result["statut"]=false;
            $result["message"]=" bad technician role name";
        }

        if($result["statut"]==false)
        {
            return $result;
        }
        else
        {
            $user = $this->hydrateBasicInfo($result["data"],$role_name);

            $userDetail = new UserDetail();
            $userDetail->setIsValid(false);

            if($role_name == "ROLE_TECHNICIAN_COMPANY")
            {
                $userDetail->setIsCompany(true);
                if($data["company"]==null) return ["message"=>"invalid_company","statut"=>false,"code"=>401,"data"=>""];

                $company = $this->em->getRepository(Company::class)->findOneBy(array("id"=>$data["company"]));
                if(is_null($company)) return ["message"=>"company_not_fonud","statut"=>false,"code"=>404,"data"=>""];

                $userDetail->setCompany($company);
                $user->setUserDetail($userDetail);

                $this->em->persist($user);
            }
            else if($role_name =="ROLE_MANAGER_COMPANY")
            {
                //$userDetail->setIsCompany(true);
                $company = new Company();
                $company->setName($data["company"]);


                $loc = new Location();
                $company->setLocation($loc);

                //$user->setUserDetail($userDetail);

                $company->setManager($user);

                $this->em->persist($company);



            }
            else
            {
                $userDetail->setIsCompany(false);
                $user->setUserDetail($userDetail);

                $this->em->persist($user);
            }

            $this->em->flush();

            $this->container->get("mail_manager")->welcome($user->getId(),$user->getUsername(),$user->getEmail(),$user->getToken());



            return ["message"=>"","statut"=>true,"data"=>$user];

        }

    }

    public function createAdmin(array $data,string $role_name)
    {
        $result = $this->verifyBasicInfo($data);

        if(!in_array( $role_name,["ROLE_ADMIN","ROLE_OPERATOR"]))
        {
            $result["statut"]=false;
            $result["message"]=" bad admin role name";
        }

        if($result["statut"]==false)
        {
            return $result;
        }
        else
        {
            $user = $this->hydrateBasicInfo($result["data"],$role_name);



            $this->em->persist($user);
            $this->em->flush();

           $this->container->get("mail_manager")->welcomeAdmin($user->getId(),$user->getUsername(),$user->getEmail(),$user->getToken(),$data["password"]);


            return ["message"=>"","statut"=>true,"data"=>$user];


        }

    }

    public function getUserByEmail(string $email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("email"=>$email));

        return $user;


    }
    public function getUserById(int $id)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("id"=>$id));

        return $user;


    }
    public function getUserByEmailAnPassword(string $email,string $password)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("email"=>$email,"password"=>$password));

        return $user;


    }

    public function active(int $id,string $token)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("id"=>$id,"token"=>$token));

        if(is_null($user))
        {
            return ["message"=>"user_not_found","statut"=>false,"data"=>"","code"=>401];
        }
        else
        {
            if($user->getIsActive()==false)
            {
                $r = $user->getRole();

                if(in_array($r->getCode(),["ROLE_CLIENT","ROLE_ADMIN","ROLE_OPERATOR"])) $user->setIsValid(true);

                $user->setIsActive(true);
                $user->setActivationDate(new \DateTime());
                $this->em->persist($user);
                $this->em->flush();

                $this->container->get("mail_manager")->registrationOk($user->getUsername(),$user->getEmail());

                return ["message"=>"account_activated","statut"=>true,"data"=>$user,"code"=>201];
            }
            else
            {
                if($user->getActivationDate() !=null)
                {
                    $nb = (int)(new \DateTime())->diff($user->getActivationDate())->days;
                    if( $nb <= 3)
                    {
                        return ["message"=>"account_already_activated","statut"=>true,"data"=>$user,"code"=>401];
                    }
                    else
                    {
                        return ["message"=>"expired_link","statut"=>false,"data"=>$user,"code"=>401];
                    }
                }
                else
                {
                    return ["message"=>"oups","statut"=>false,"data"=>$user,"code"=>401];
                }

            }

        }
    }

    public function updateProfile(User $user,string $type,array $data)
    {

        switch ($type)
        {
            case "picture":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $file = $user->getPicture();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $user->setPicture($file);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "cni_field":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }

                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data["cni_field"])<6)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userDetail= $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $userDetail->setCni($data[$type]);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"cni field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "cni_file":

                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>"operation denied","statut"=>false,"data"=>"","code"=>401];
                }
                if(!array_key_exists("file",$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];



                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $userDetail = $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $file = $userDetail->getCniFile();

                if(is_null($file)) $file = new File();

                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(false);
                $file->setDate(new \DateTime());

                $userDetail->setCniFile($file);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"cni picture field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "criminal_record":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>"operation denied","statut"=>false,"data"=>"","code"=>401];
                }
                if(!array_key_exists("file",$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $userDetail = $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $file = $userDetail->getCriminalRecord();

                if(is_null($file)) $file = new File();

                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(false);

                $userDetail->setCriminalRecord($file);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"criminal record field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "company":
                if(!in_array($user->getRole()->getCode(),["ROLE_TECHNICIAN_P","ROLE_TECHNICIAN_COMPANY","ROLE_MANAGER_COMPANY"]))
                {
                    return ["message"=>"operation denied","statut"=>false,"data"=>"","code"=>401];
                }

                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];


                $company = $this->em->getRepository(Company::class)->findOneBy(array("id"=>$data["company"]));

                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $userDetail= $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $userDetail->setCompany($company);


                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"cni field updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "description":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }

                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data["description"])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userDetail= $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $userDetail->setDescription($data[$type]);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"description field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "citation":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }

                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data["citation"])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userDetail= $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $userDetail->setCitation($data[$type]);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"description field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "cv":

                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>"operation denied","statut"=>false,"data"=>"","code"=>401];
                }
                if(!array_key_exists("file",$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];



                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $userDetail = $user->getUserDetail();

                if(is_null($userDetail)) return ["message"=>"user detail not found","statut"=>false,"data"=>"","code"=>404];

                $file = $userDetail->getCv();

                if(is_null($file)) $file = new File();

                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(false);
                $file->setDate(new \DateTime());

                $userDetail->setCv($file);

                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>"cv field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "street":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data["street"])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userLocation= $user->getLocation();

                if(is_null($userLocation)) return ["message"=>"user location not found","statut"=>false,"data"=>"","code"=>404];

                $userLocation->setDetail($data["street"]);

                $user->setLocation($userLocation);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "city":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data[$type])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userLocation= $user->getLocation();

                if(is_null($userLocation)) return ["message"=>"user location not found","statut"=>false,"data"=>"","code"=>404];

                $userLocation->setCity($data[$type]);

                $user->setLocation($userLocation);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "quater":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data[$type])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $userLocation= $user->getLocation();

                if(is_null($userLocation)) return ["message"=>"user location not found","statut"=>false,"data"=>"","code"=>404];

                $userLocation->setQuater($data[$type]);

                $user->setLocation($userLocation);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "domain":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                $userDetail = $user->getUserDetail();

                $domains = $userDetail->getDomains();
                $nb = count($data["domain"]);

                foreach ($domains as $el)
                {
                    if($nb==0)
                    {
                        $userDetail->removeDomain($el);
                    }
                    else
                    {
                        if(!in_array($el->getId(),$data["domain"]))
                        {
                            $userDetail->removeDomain($el);
                        }
                    }


                }
                if($nb>0)
                {
                    foreach ($data["domain"] as $id)
                    {
                        $el=$this->em->getRepository(Domain::class)->findOneBy(array("id"=>$id));

                        if(!is_null($el))
                        {
                            $userDetail->addDomain($el);
                        }

                    }
                }



                $user->setUserDetail($userDetail);

                $this->em->persist($user);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "company-city":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data[$type])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $loc= $company->getLocation();

                if(is_null($loc)) return ["message"=>"company location not found","statut"=>false,"data"=>"","code"=>404];

                $loc->setCity($data[$type]);

                $company->setLocation($loc);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "company-quater":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data[$type])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $loc= $company->getLocation();

                if(is_null($loc)) return ["message"=>"company location not found","statut"=>false,"data"=>"","code"=>404];

                $loc->setQuater($data[$type]);

                $company->setLocation($loc);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "company-street":
                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data[$type])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $loc= $company->getLocation();

                if(is_null($loc)) return ["message"=>"company location not found","statut"=>false,"data"=>"","code"=>404];

                $loc->setDetail($data[$type]);

                $company->setLocation($loc);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];
                break;
            case "company-domain":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }
                if(!array_key_exists("domain",$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $domains = $company->getDomains();
                $nb = count($data["domain"]);

                foreach ($domains as $el)
                {
                    if($nb==0)
                    {
                        $company->removeDomain($el);
                    }
                    else
                    {
                        if(!in_array($el->getId(),$data["domain"]))
                        {
                            $company->removeDomain($el);
                        }
                    }


                }
                if($nb>0)
                {
                    foreach ($data["domain"] as $id)
                    {
                        $el=$this->em->getRepository(Domain::class)->findOneBy(array("id"=>$id));

                        if(!is_null($el))
                        {
                            $company->addDomain($el);
                        }

                    }
                }





                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>$type." field updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "company-description":
                if(in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
                {
                    return ["message"=>$type."operation denied","statut"=>false,"data"=>"","code"=>401];
                }

                if(!array_key_exists($type,$data)) return ["message"=>$type." field is required","statut"=>false,"data"=>"","code"=>401];

                if(strlen($data["company-description"])<1)return ["message"=>$type." field lenght is small","statut"=>false,"data"=>"","code"=>401];

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $company->setDescription($data[$type]);



                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"description field updated","statut"=>true,"data"=>$user,"code"=>201];


                break;
            case "company-logo":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $file = $company->getLogo();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $company->setLogo($file);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "tax_card":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $file = $company->getTaxCard();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $company->setTaxCard($file);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "trade_register":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $file = $company->getTradeRegister();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $company->setTradeRegister($file);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "password":
                if(!array_key_exists("c-password",$data)) return ["message"=>"c-password field is required","statut"=>false,"data"=>$user,"code"=>401];
                if(!array_key_exists("n-password",$data)) return ["message"=>"n-password field is required","statut"=>false,"data"=>$user,"code"=>401];

                $res=$this->encoder->isPasswordValid($user,$data["c-password"]);

                if($res ==false) return ["message"=>"current_password_invalid","statut"=>false,"data"=>$user,"code"=>401];

                $res = $this->passwordStrength($data["n-password"]);

                if($res ==false) return ["message"=>"new_password_invalid","statut"=>false,"data"=>$user,"code"=>401];

                $user->setPassword($this->container->get("security.password_encoder")->encodePassword($user,$data["n-password"] ));

                $this->em->persist($user);

                $this->em->flush();

                return ["message"=>"profile_updated","statut"=>true,"data"=>$user,"code"=>201];



                break;
            case "company-location-plan":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $file = $company->getLocationPlan();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $company->setLocationPlan($file);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            case "company-director":

                if(!array_key_exists("file",$data)) return ["message"=>"file field is required","statut"=>false,"data"=>"","code"=>401];
                $required =["name","size","extension","type","path"];

                foreach ($required as $key=>$el)
                {
                    if(!array_key_exists($el,$data["file"]))
                    {
                        return ["message"=>$el." field is required","statut"=>false,"data"=>"","code"=>401];
                    }
                }

                $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
                if(is_null($company)) return ["message"=>"company not found","statut"=>false,"data"=>"","code"=>404];

                $file = $company->getDirectorCni();

                if(is_null($file)) $file = new File();


                $file->setIsActive(true);
                $file->setName($data["file"]["name"]);
                $file->setPath($data["file"]["path"]);
                $file->setSize($data["file"]["size"]);
                $file->setType($data["file"]["type"]);
                $file->setExtension($data["file"]["extension"]);
                $file->setIsProfile(true);
                $file->setDate(new \DateTime());

                $company->setDirectorCni($file);

                $this->em->persist($company);
                $this->em->flush();

                return ["message"=>"picture updated","statut"=>true,"data"=>$user,"code"=>201];

                break;
            default:
                return ["message"=>"no type added","statut"=>false,"data"=>"","code"=>401];
                break;
        }
    }

    public function getSimpleProfile(int $id)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("id"=>$id));

        if(is_null($user)) return["message"=>"user not found","statut"=>false,"code"=>404,"data"=>""];

        if(!in_array($user->getRole()->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
        {
            $userDetail = $user->getUserDetail();


            return["message"=>"","statut"=>true,"code"=>201,"data"=>["user"=>$user->toArray(),"user_detail"=>$userDetail->toArray()]];

        }
        else{
            return["message"=>"","statut"=>true,"code"=>201,"data"=>["user"=>$user->toArray(),"user_detail"=>[]]];

        }
    }

    public function sendAccountForValidation(User $user)
    {
        if(is_null($user)) return["message"=>"user not found","statut"=>false,"code"=>404,"data"=>""];

        $role = $user->getRole();

        if(in_array($role->getCode(),["ROLE_CLIENT","ROLE_SADMIN","ROLE_ADMIN","ROLE_OPERATOR"]))
            return["message"=>"operation denied","statut"=>false,"code"=>401,"data"=>""];

        if($user->getIsValid() == true) return["message"=>"account already validated","statut"=>false,"code"=>401,"data"=>""];

        $old = $this->em->getRepository(AccountValidation::class)->findOneBy(array("user"=>$user,"statut"=>false));

        if(!is_null($old)) return["message"=>"validation pending","statut"=>false,"code"=>401,"data"=>""];
        else
        {
            $userDetail = $user->getUserDetail();

            if($userDetail->getIsCompany() ==true && is_null($userDetail->getCompany()))
            {
                return["message"=>"company_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if($user->getLocation()->getCity()=="")
            {
                return["message"=>"city_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }
            if($user->getLocation()->getQuater()=="")
            {
                return["message"=>"quater_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if(strlen($userDetail->getDescription())<50)
            {
                return["message"=>"description_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if(is_null($userDetail->getCniFile()))
            {
                return["message"=>"cni_file_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if(is_null($userDetail->getCriminalRecord()))
            {
                return["message"=>"criminal_record_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if(is_null($userDetail->getCv()))
            {
                return["message"=>"cv_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            if(count($userDetail->getDomains())<0)
            {
                return["message"=>"domain_is_empty","statut"=>false,"code"=>401,"data"=>""];
            }

            $av =new AccountValidation();
            $av->setStatut(false);
            $av->setUser($user);


            if($role->getCode() == "ROLE_TECHNICIAN_PERSON")
                $av->setType(AccountValidation::INDIVIDUAL);
            else
            {
                if($role->getCode() == "ROLE_TECHNICIAN_COMPANY")
                    $av->setType(AccountValidation::COMPANY);
                else
                    $av->setType(AccountValidation::COMPANYMANAGER);

            }

            $this->em->persist($av);
            $this->em->flush();

            return["message"=>"profile_sended","statut"=>true,"code"=>201,"data"=>$user];


        }


    }

    public function lockAccount(User $user,int $id)
    {
        $cr = $user->getRole();
        $u = $this->em->getRepository(User::class)->findOneBy(array("id"=>$id));
        if(is_null($u)) return ["message"=>"user not found","statut"=>false,"code"=>404,"data"=>""];

        $role = $u->getRole();

        if($role->getCode() =="ROLE_SADMIN" || $role->getCode() =="ROLE_SYSTEM")
            return ["message"=>"operation denied","statut"=>false,"code"=>401,"data"=>""];

        if($user->getId()==$u->getId())
        {
            $user->setIsClose(true);

            $this->em->persist($user);
            $this->em->flush();

            $this->container->get("mail_manager")->accountLocked($user->getUsername(),$user->getEmail());

            return ["message"=>"account locked","statut"=>true,"code"=>201,"data"=>$user->toArray()];
        }
        else
        {
            if(in_array($role->getCode(),["ROLE_CLIENT","ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"]))
            {
                if(in_array($cr->getCode(),["ROLE_CLIENT","ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"]))
                {
                    return ["message"=>"operation denied","statut"=>false,"code"=>401,"data"=>""];
                }
                else
                {
                    $u->setIsClose(true);
                    $this->em->persist($u);
                    $this->em->flush();

                    $this->container->get("mail_manager")->accountLocked($u->getUsername(),$u->getEmail());

                    return ["message"=>"account locked","statut"=>true,"code"=>201,"data"=>$u->toArray()];
                }

            }
            else
            {
                if(in_array($role->getCode(),["ROLE_OPERATOR","ROLE_ADMIN"]))
                {
                    if($cr->getCode() !="ROLE_SADMIN")
                    {
                        return ["message"=>"operation denied","statut"=>false,"code"=>401,"data"=>""];
                    }
                    else
                    {
                        $u->setIsClose(true);
                        $this->em->persist($u);
                        $this->em->flush();

                        $this->container->get("mail_manager")->accountLocked($u->getUsername(),$u->getEmail());

                        return ["message"=>"account locked","statut"=>true,"code"=>201,"data"=>$u->toArray()];
                    }
                }
                else
                {
                    return ["message"=>"system operation denied","statut"=>false,"code"=>401,"data"=>""];
                }
            }
        }




    }

    public function validateTechnician(User $user,int $id,array $data)
    {
        if(!array_key_exists("answer",$data))
            return ["message"=>"answer field is required","statut"=>false,"code"=>401,"data"=>""];

        $answer = $data["answer"];

        $cr = $user->getRole();
        $u = $this->em->getRepository(User::class)->findOneBy(array("id"=>$id));
        if(is_null($u)) return ["message"=>"user not found","statut"=>false,"code"=>404,"data"=>""];

        $role = $u->getRole();

        if(!in_array($role->getCode(),["ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"])) {
            return ["message" => "operation denied", "statut" => false, "code" => 401, "data" => ""];
        }
        else
        {
            if(in_array($cr->getCode(),["ROLE_CLIENT","ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"]))
                return ["message"=>"operation denied","statut"=>false,"code"=>401,"data"=>""];
            else
            {
                $old = $this->em->getRepository(AccountValidation::class)->findOneBy(array("user"=>$u,"statut"=>false));

                if(is_null($old)) return ["message"=>"no validation found for this user","statut"=>false,"code"=>401,"data"=>""];
                else
                {
                    $old->setStatut(true);

                    if($answer == 1)
                    {
                        $u->setIsValid(true);

                        if($role->getCode()=="ROLE_MANAGER_COMPANY")
                        {
                            $company = $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$u));
                            if(!is_null($company))
                            {
                                $company->setIsValid(true);
                                $this->em->persist($company);
                            }
                        }
                        else
                        {
                            $userDetail = $u->getUserDetail();
                            $userDetail->setIsValid(true);
                            $u->setUserDetail($userDetail);

                            //$this->em->persist($u);
                        }

                        $notif = new Notification();

                        $notif->setUser($user);
                        $notif->setIsActive(false);
                        $notif->setCode(Notification::ACCOUNT_VALIDATED);


                        $statut = new Statut();
                        $statut->setNotification($notif);
                        $statut->setUser($u);
                        $statut->setStatut(false);

                        $this->em->persist($statut);


                    }



                    $this->em->persist($old);

                    $this->em->flush();

                    return ["message"=>"operation done","statut"=>true,"code"=>201,"data"=>$u];
                }

            }
        }



    }

    public function login(string $email,string $password)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("email"=>$email));


        if(is_null($user)) return ["message"=>"user not found","statut"=>false,"code"=>401,"data"=>""];

        if($user->getRpassword() != null)
        {

            $user->setPassword($user->getRpassword());
            $res=$this->encoder->isPasswordValid($user,$password);
            if($res ==true){

                $user->setRpassword(null);

                $this->em->persist($user);
                $this->em->flush();
            }
        }
        else
        {
            $res=$this->encoder->isPasswordValid($user,$password);
        }


        if($res == false) return ["message"=>"user not found","statut"=>false,"code"=>401,"data"=>""];
        else{

            if($user->getIsActive()==0)
            {
                return ["message"=>"account_not_activated","statut"=>true,"code"=>401,"data"=>""];
            }
            else
            {
                $token = $this->jwt->create($user);
                return ["message"=>"","statut"=>true,"code"=>201,"data"=>["user"=>$user,"token"=>$token]];
            }

        }

    }

    public function resetPassword(string $email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(array("email"=>$email));

        if(is_null($user)) return ["message"=>"user not found","statut"=>false,"code"=>404,"data"=>""];

        $pass = $this->getRandom(10);

        $user2 =new User();

        $user2->setPassword($this->container->get("security.password_encoder")->encodePassword($user2,$pass ));

        $user->setRpassword($user2->getPassword());

        $this->em->persist($user);
        $this->em->flush();

        $this->container->get("mail_manager")->resetPassword($user->getUsername(),$user->getEmail(),$pass);

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>$user];

    }

    public function getTechnicianStatut(User $user)
    {
        $role=$user->getRole();
        if(!in_array($role->getCode(),["ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON","ROLE_MANAGER_COMPANY"]))
            return ["message"=>"operation_denied","statut"=>false,"code"=>401,"data"=>$user];

        if($user->getIsValid() == true) return["message"=>"account_validated","statut"=>true,"code"=>201,"data"=>$user];

        $old = $this->em->getRepository(AccountValidation::class)->findOneBy(array("user"=>$user,"statut"=>false));
        if(!is_null($old)) return["message"=>"profile_awaiting","statut"=>false,"code"=>401,"data"=>$user];
        else
        {
            return["message"=>"profile_not_send","statut"=>false,"code"=>401,"data"=>$user];
        }

    }

    public function getManagerStatut(User $user)
    {

        $role=$user->getRole();
        if($role->getCode()!="ROLE_MANAGER_COMPANY")
            return ["message"=>"operation_denied","statut"=>false,"code"=>401,"data"=>$user];

        $company = $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));


        if(is_null($company))  return["message"=>"company_not_found","statut"=>false,"code"=>401,"data"=>["user"=>$user,"company"=>null]];

        if($company->getIsValid() ==true) return["message"=>"company_ok","statut"=>true,"code"=>201,"data"=>["user"=>$user,"company"=>$company->toArray()]];


        $old = $this->em->getRepository(AccountValidation::class)->findOneBy(array("user"=>$user,"statut"=>false));
        if(!is_null($old)) return["message"=>"profile_awaiting","statut"=>false,"code"=>401,"data"=>["user"=>$user,"company"=>$company->toArray()]];
        else
        {
            //$status=false;

            //if(is_null($company->getTaxCard())) $status=true;
            //if(is_null($company->getTradeRegister())) $status = true;
            //if(is_null($company->getLogo())) $status = true;


            return["message"=>"company_not_send","statut"=>false,"code"=>401,"data"=>["user"=>$user,"company"=>$company->toArray()]];

        }

    }

    public function sendCompanyForValidation(User $user)
    {
        $role = $user->getRole();

        if($role->getCode() !="ROLE_MANAGER_COMPANY") return["message"=>"operation_denied","statut"=>false,"code"=>404,"data"=>$user];

        $company= $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));
        if(is_null($company)) return ["message"=>"company_not_found","statut"=>false,"data"=>"","code"=>404];

        if($company->getIsValid() == true) return["message"=>"account already validated","statut"=>false,"code"=>401,"data"=>""];

        $old = $this->em->getRepository(AccountValidation::class)->findOneBy(array("user"=>$user,"statut"=>false));

        if(!is_null($old)) return["message"=>"profile_awaiting","statut"=>false,"code"=>401,"data"=>""];
        else
        {
            if(is_null($company->getTradeRegister())) return["message"=>"empty_trade_register","statut"=>false,"code"=>401,"data"=>""];
            if(is_null($company->getTaxCard())) return["message"=>"empty_tax_card","statut"=>false,"code"=>401,"data"=>""];
            if(is_null($company->getDescription())) return["message"=>"empty_description","statut"=>false,"code"=>401,"data"=>""];
            if(is_null($company->getDirectorCni())) return["message"=>"empty_director_cni","statut"=>false,"code"=>401,"data"=>""];
            if(is_null($company->getLocationPlan())) return["message"=>"empty_location_plan","statut"=>false,"code"=>401,"data"=>""];
            if(count($company->getDomains())==0) return["message"=>"empty_domain","statut"=>false,"code"=>401,"data"=>""];
            if(empty($company->getLocation()->getCity())) return["message"=>"city_is_empty","statut"=>false,"code"=>401,"data"=>""];
            if(empty($company->getLocation()->getQuater())) return["message"=>"quater_is_empty","statut"=>false,"code"=>401,"data"=>""];
            if(empty($company->getLocation()->getDetail())) return["message"=>"street_is_empty","statut"=>false,"code"=>401,"data"=>""];

            $av =new AccountValidation();
            $av->setStatut(false);
            $av->setUser($user);
            $av->setType(AccountValidation::COMPANYMANAGER);



            $this->em->persist($av);
            $this->em->flush();

            return["message"=>"profile_sended","statut"=>true,"code"=>201,"data"=>$user];


        }

    }

    public function usersList( string $code,int $limit,int $offset)
    {



        $tab=[];


        if(in_array($code,["ROLE_CLIENT","ROLE_TECHNICIAN_PERSON","ROLE_TECHNICIAN_COMPANY","ROLE_MANAGER_COMPANY"]))
            return["message"=>"operation_denied","statut"=>false,"code"=>401,"data"=>[]];
        else if($code=="ROLE_ADMIN")
        {
            $tab[]="ROLE_SADMIN";
        }
        else if ($code=="ROLE_OPERATOR"){
            $tab[]="ROLE_SADMIN";
            $tab[]="ROLE_ADMIN";

        }
        else if ($code=="ROLE_SADMIN"){
            $tab[]="ROLE_SADMIN";


        }


        $users = $this->em->getRepository(User::class)->loadAllUser($tab,$limit,$offset);

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>$users];

    }

    public function userCompany(User $user)
    {
        $role = $user->getRole();
        if($role->getCode()!="ROLE_MANAGER_COMPANY")  return ["message"=>"operation_denied","statut"=>false,"code"=>401,"data"=>null];

        $company = $this->em->getRepository(Company::class)->findOneBy(array("manager"=>$user));

        if(is_null($company)) return ["message"=>"operation_denied","statut"=>false,"code"=>401,"data"=>null];

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>$company->toArray()];

    }

    public function nbAwaitingValidation()
    {
        $res = $this->em->getRepository(AccountValidation::class)->nbAwait();

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>$res[0]["nb"]];
    }

    public function userValidationAwaiting(int $limit,int $offset)
    {
        $all = $this->em->getRepository(AccountValidation::class)->findBy(array("statut"=>false),array(),$limit,$offset);

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>$all];
    }

    public function getTechnicianByDomain(int $id)
    {
        $users = $this->em->getRepository(User::class)->findByDomain($id,6,0);
        $comp = $this->em->getRepository(Company::class)->findCompanyByDomain($id,6,0);

        return ["message"=>"","statut"=>true,"code"=>201,"data"=>["users"=>$users,"companies"=>$comp]];

    }

}