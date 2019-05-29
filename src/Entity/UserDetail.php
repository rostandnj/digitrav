<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserDetailRepository")
 */
class UserDetail
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20,nullable=true)
     */
    private $cni;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValid;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCompany;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company",cascade={"persist","remove"})
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File",cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $criminalRecord;

    /**
     * @ORM\Column(type="integer")
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File",cascade={"persist","remove"})
     */
    private $cniFile;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File",cascade={"persist","remove"})
     */
    private $cv;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $citation;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Domain",cascade={"persist","remove"})
     */
    private $domains;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbDomain;

    /**
     * @ORM\Column(type="boolean")
     */
    private $avilability;

    const MAXDOMAIN=3;

    /**
     * UserDetail constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->note =0;
        $this->isCompany = false;
        $this->isValid = false;
        $this->domains = new ArrayCollection();
        $this->nbDomain=0;
        $this->avilability=true;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCni(): ?string
    {
        return $this->cni;
    }

    public function setCni(string $cni): self
    {
        $this->cni = $cni;

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

    public function getIsCompany(): ?bool
    {
        return $this->isCompany;
    }

    public function setIsCompany(bool $isCompany): self
    {
        $this->isCompany = $isCompany;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCriminalRecord(): ?File
    {
        return $this->criminalRecord;
    }

    public function setCriminalRecord(?File $criminalRecord): self
    {
        $this->criminalRecord = $criminalRecord;

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

    public function getCniFile(): ?File
    {
        return $this->cniFile;
    }

    public function setCniFile(?File $cniFile): self
    {
        $this->cniFile = $cniFile;

        return $this;
    }
    public function toArray()
    {
        $tab = ["id"=>$this->id,"cni"=>$this->cni,"is_valid"=>$this->isValid,"is_company"=>$this->isCompany,
        "note"=>$this->note,"date"=>$this->getDate()->format(\DateTime::ISO8601),
            "citation"=>$this->citation,"availability"=>$this->avilability];

        $tab["description"]=$this->getDescription();

        $tab["criminal_record"]=null;
        $tab["cni_file"]=null;
        $tab["cv"]=null;

        $cr = $this->getCriminalRecord();
        if(!is_null($cr)) $tab["criminal_record"]= $cr->toArray();

        $cnif = $this->getCniFile();
        if(!is_null($cnif)) $tab["cni_file"]= $cnif->toArray();

        $cv = $this->getCv();
        if(!is_null($cv))  $tab["cv"]=$cv->toArray();




        if($this->isCompany == true)
        {
            $company =  $this->getCompany();
            if(is_null($company)) $tab["company"] = null;
            else $tab["company"] = $this->getCompany()->toArray();
        }

        $tab["domains"] = [];

        $d = $this->getDomains();
        if(count($d)>0)
        {
            foreach ($d as $el)
            {
                $tab["domains"][]=$el->toArray();
            }
        }



        return $tab;
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

    public function getCv(): ?File
    {
        return $this->cv;
    }

    public function setCv(?File $cv): self
    {
        $this->cv = $cv;

        return $this;
    }

    public function getCitation(): ?string
    {
        return $this->citation;
    }

    public function setCitation(?string $citation): self
    {
        $this->citation = $citation;

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

    public function getNbDomain(): ?int
    {
        return $this->nbDomain;
    }

    public function setNbDomain(int $nbDomain): self
    {
        $this->nbDomain = $nbDomain;

        return $this;
    }

    public function getAvilability(): ?bool
    {
        return $this->avilability;
    }

    public function setAvilability(bool $avilability): self
    {
        $this->avilability = $avilability;

        return $this;
    }


}
