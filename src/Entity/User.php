<?php

namespace App\Entity;

use Composer\DependencyResolver\Request;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    private $profileName;

    /**
     * @ORM\Column(type="string", length=35)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=35, nullable=true)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $gender;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isClose;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File",cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $picture;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location",cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserDetail",cascade={"persist","remove"})
     */
    private $userDetail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=40,unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $activationDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rpassword;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Company", cascade={"persist", "remove"},mappedBy="manager",fetch="EAGER")
     */
    private $company;

    private $uid;
    private $container;

    /**
     * User constructor.
     */
    public function __construct( )
    {
        $this->isActive = true;
        $this->date = new \DateTime();
        $this->isValid = false;
        $this->isClose = true;


    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getGender(): ?bool
    {
        return $this->gender;
    }

    public function setGender(bool $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getIsValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getIsClose(): ?bool
    {
        return $this->isClose;
    }

    public function setIsClose(bool $isClose): self
    {
        $this->isClose = $isClose;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getPicture(): ?File
    {
        return $this->picture;
    }

    public function setPicture(?File $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getUserDetail(): ?UserDetail
    {
        return $this->userDetail;
    }

    public function setUserDetail(?UserDetail $userDetail): self
    {
        $this->userDetail = $userDetail;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles()
    {
        return [$this->getRole()->getCode()];
    }





    public function getSalt()
    {
        return $this->token;
    }


    public function getUsername()
    {
        return $this->name.' '.$this->surname;
    }


    public function eraseCredentials()
    {

    }

    /**
     * @return mixed
     */
    public function getProfileName()
    {
        return str_replace(" ","-",$this->getName()." ".$this->getSurname());
    }



    /**
     * @param mixed $profileName
     * @return User
     */
    public function setProfileName($profileName)
    {
        $this->profileName = $profileName;
        return $this;
    }



    public function toArray()
    {
        $tab =[];
        $tab["id"]=$this->id;
        $tab["name"]=$this->name;
        $tab["surname"]=$this->surname;
        $tab["phone"]=$this->phone;
        $tab["email"]=$this->email;
        $tab["gender"]=$this->gender;
        $tab["is_valid"]=$this->isValid;
        $tab["is_close"]=$this->isClose;
        $tab["is_active"]=$this->isActive;
        $tab["date"]=$this->getDate()->format(\DateTime::ISO8601);
        $tab["profile_name"]=$this->getProfileName();
        $tab["username"] = $this->getUsername();
        $tab["uid"] = $this->getUid();

        $tab["role"]=$this->getRole()->toArray();

        if($this->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
        {
            $tab["company"] = $this->getCompany()->toArray();
        }

        //$tab["user_detail"]=null;

       if(in_array($tab["role"]["code"],["ROLE_TECHNICIAN_COMPANY","ROLE_TECHNICIAN_PERSON"]))
        {
            $tab["user_detail"]=$this->getUserDetail()->toArray();
        }

        $picture = $this->getPicture();
        if(is_null($picture))
        {
            $tab["picture"]=[];
            if($this->gender == false) {
                $tab["picture"]["name"]="woman.png";
                $tab["picture"]["path"]="woman.png";
            }
            else{
                $tab["picture"]["name"]="man.png";
                $tab["picture"]["path"]="man.png";

            }
            $tab["picture"]["type"]="image";
            $tab["picture"]["is_profile"]=true;
            $tab["picture"]["is_default"]=true;



        }
        else{
            $tab["picture"]=$picture->toArray();
        }
        $tab["location"]=null;
        $lo = $this->getLocation();
        if(!is_null($lo))$tab["location"]=$lo->toArray();

        return $tab;

    }

    public function getActivationDate(): ?\DateTimeInterface
    {
        return $this->activationDate;
    }

    public function setActivationDate(?\DateTimeInterface $activationDate): self
    {
        $this->activationDate = $activationDate;

        return $this;
    }

    public function getRpassword(): ?string
    {
        return $this->rpassword;
    }

    public function setRpassword(?string $rpassword): self
    {
        $this->rpassword = $rpassword;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     * @return User
     */
    public function setCompany(Company $company): self
    {
        $this->company = $company;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param mixed $uid
     * @return User
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }


    public function toArrayShort()
    {
        $tab =[];
        $tab["id"]=$this->id;
        $tab["name"]=$this->name;
        $tab["email"]=$this->email;
        $tab["surname"]=$this->surname;
        $tab["phone"]=$this->phone;
        $tab["gender"]=$this->gender;
        $tab["is_valid"]=$this->isValid?true:false;
        $tab["is_close"]=(bool)$this->isClose?true:false;
        $tab["is_active"]=(bool)$this->isActive?true:false;
        $tab["profile_name"]=$this->getProfileName();
        $tab["username"] = $this->getUsername();
        $tab["uid"] = $this->getUid();

        $tab["role"]=$this->getRole()->toArray();

        $picture = $this->getPicture();
        if(is_null($picture))
        {
            $tab["picture"]=[];
            if($this->gender == false) {
                $tab["picture"]["name"]="woman.png";
                $tab["picture"]["path"]="woman.png";
            }
            else{
                $tab["picture"]["name"]="man.png";
                $tab["picture"]["path"]="man.png";

            }
            $tab["picture"]["type"]="image";
            $tab["picture"]["is_profile"]=true;
            $tab["picture"]["is_default"]=true;



        }
        else{
            $tab["picture"]=$picture->toArray();
        }

        if($this->getRole()->getCode()=="ROLE_MANAGER_COMPANY")
        {
            $tab["company"] = $this->getCompany()->toArray();
        }


        $lo = $this->getLocation();
        if(!is_null($lo))$tab["location"]=$lo->toArray();
        return $tab;

    }





}
