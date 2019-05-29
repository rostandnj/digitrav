<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CompanyRepository")
 */
class Company
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $name;



    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     */
    private $logo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $location;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $tradeRegister;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $taxCard;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist", "remove"},inversedBy="company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValid;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Domain")
     */
    private $domains;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     */
    private $directorCni;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     */
    private $locationPlan;

    /**
     * @ORM\Column(type="integer")
     */
    private $note;

    /**
     * Company constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->domains = new ArrayCollection();
        $this->isValid = false;
        $this->note=0;

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




    public function getLogo(): ?File
    {
        return $this->logo;
    }

    public function setLogo(?File $logo): self
    {
        $this->logo = $logo;

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

    public function getTradeRegister(): ?File
    {
        return $this->tradeRegister;
    }

    public function setTradeRegister(?File $tradeRegister): self
    {
        $this->tradeRegister = $tradeRegister;

        return $this;
    }

    public function getTaxCard(): ?File
    {
        return $this->taxCard;
    }

    public function setTaxCard(?File $taxCard): self
    {
        $this->taxCard = $taxCard;

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

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getManager(): ?User
    {
        return $this->manager;
    }

    public function setManager(?User $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function toArray()
    {
        $tab = ["id"=>$this->id,"name"=>$this->name,"description"=>$this->description,"is_valid"=>$this->isValid,
            "date"=>$this->getDate()->format(\DateTime::ISO8601),"note"=>$this->note];

        $logo = $this->getLogo();

        $tab["logo"]=[];

        if(is_null($logo))
        {
           $tab["logo"]["name"]="brand.png";
           $tab["logo"]["path"]="brand.png";

           $tab["logo"]["type"]="image";
           $tab["logo"]["is_profile"]=false;
           $tab["logo"]["is_default"]=true;
        }
        else
        {
            $tab["logo"]=$this->getLogo()->toArray();
        }

        $tab["location"]=$this->getLocation()->toArray();

        $tab["trade_register"]=null;

        $tr = $this->getTradeRegister();

        if(!is_null($tr)) $tab["trade_register"]=$tr->toArray();

        $tab["tax_card"] = null;

        $tc=$this->getTaxCard();

        if(!is_null($tc)) $tab["tax_card"] = $tc->toArray();

        $tab["domains"] = [];

        $d = $this->getDomains();
        if(count($d)>0)
        {
            foreach ($d as $el)
            {
                $tab["domains"][]=$el->toArray();
            }
        }

        //$tab["manager"]=$this->getManager()->toArrayShort();


        $tab["director_cni"]=null;
        $di = $this->getDirectorCni();
        if(!is_null($di)) $tab["director_cni"]=$di->toArray();

        $tab["location_plan"]=null;
        $l = $this->getLocationPlan();
        if(!is_null($l)) $tab["location_plan"]=$l->toArray();

       // $tab["manager"]=null;

       // $m=$this->getManager();

        //if(!is_null($m)) $tab["manager"]=$m->toArray();

        return $tab;
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

    /**
     * @return Collection|Domain[]
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains[] = $domain;
        }

        return $this;
    }

    public function removeDomain(Domain $domain): self
    {
        if ($this->domains->contains($domain)) {
            $this->domains->removeElement($domain);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDirectorCni(): ?File
    {
        return $this->directorCni;
    }

    public function setDirectorCni(?File $directorCni): self
    {
        $this->directorCni = $directorCni;

        return $this;
    }

    public function getLocationPlan(): ?File
    {
        return $this->locationPlan;
    }

    public function setLocationPlan(?File $locationPlan): self
    {
        $this->locationPlan = $locationPlan;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): self
    {
        $this->note = $note;

        return $this;
    }
}
