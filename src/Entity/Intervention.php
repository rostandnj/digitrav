<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InterventionRepository")
 */
class Intervention
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $statut;

    /**
     * @ORM\Column(type="string", length=25,unique=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $clientName;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $startDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="intervention",cascade={"persist","remove"})
     */
    private $files;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbFile;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isMain;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $operator;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Domain")
     * @ORM\JoinColumn(nullable=false)
     */
    private $domain;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $client;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location",cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $budget;

    private $canApply;
    private $canSave;
    private $canDelete;
    private $canAlert;
    private $hasApplied;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Quote", mappedBy="intervention",cascade={"persist","remove"})
     */
    private $quotes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="interventions")
     */
    private $subCategory;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasEdit;

    const NEW=0;
    const ACCEPTED=1;
    const PAID=2;
    const CANCELED=3;
    const DONE = 4;


    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->isActive = true;
        $this->date = new \DateTime();
        $this->statut = self::NEW;
        $this->quotes = new ArrayCollection();
        $this->hasEdit = false;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): self
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setIntervention($this);
        }

        return $this;
    }

    public function removeFile(File $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getIntervention() === $this) {
                $file->setIntervention(null);
            }
        }

        return $this;
    }

    public function getNbFile(): ?int
    {
        return $this->nbFile;
    }

    public function setNbFile(int $nbFile): self
    {
        $this->nbFile = $nbFile;

        return $this;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): self
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getOperator(): ?User
    {
        return $this->operator;
    }

    public function setOperator(?User $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    public function setDomain(?Domain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }



    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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

    /**
     * @return mixed
     */
    public function getCanApply()
    {
        return $this->canApply;
    }

    /**
     * @param mixed $canApply
     * @return Intervention
     */
    public function setCanApply($canApply)
    {
        $this->canApply = $canApply;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanSave()
    {
        return $this->canSave;
    }

    /**
     * @param mixed $canSave
     * @return Intervention
     */
    public function setCanSave($canSave)
    {
        $this->canSave = $canSave;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanDelete()
    {
        return $this->canDelete;
    }

    /**
     * @param mixed $canDelete
     * @return Intervention
     */
    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCanAlert()
    {
        return $this->canAlert;
    }

    /**
     * @param mixed $canAlert
     * @return Intervention
     */
    public function setCanAlert($canAlert)
    {
        $this->canAlert = $canAlert;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHasApplied()
    {
        return $this->hasApplied;
    }

    /**
     * @param mixed $hasApplied
     * @return Intervention
     */
    public function setHasApplied($hasApplied)
    {
        $this->hasApplied = $hasApplied;
        return $this;
    }



    public function toArray()
    {
        $tab=["id"=>$this->id,"date"=>$this->getDate()->format(\DateTime::ISO8601),
            "title"=>$this->title,
            "slug"=>$this->slug,
            "description"=>$this->description,"statut"=>$this->statut,"reference"=>$this->reference,
            "start_date"=>$this->getStartDate()->format(\DateTime::ISO8601),
            "client_name"=>$this->clientName,
            "is_main"=>$this->isMain,
            "nb_file"=>$this->nbFile,
            "category"=>$this->getCategory()->toArray(),
            "domain"=>$this->getDomain()->toArray(),
            "location"=>$this->getLocation()->toArray(),
            "can_apply"=>$this->canApply,
            "can_save"=>$this->canSave,
            "can_delete"=>$this->canDelete,
            "can_alert"=>$this->canAlert,
            "budget"=>$this->budget
        ];

        if($this->isMain ==true)
        {
            $tab["client"] = $this->getClient()->toArrayShort();

        }

        return $tab;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * @return Collection|Quote[]
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(Quote $quote): self
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes[] = $quote;
            $quote->setIntervention($this);
        }

        return $this;
    }

    public function removeQuote(Quote $quote): self
    {
        if ($this->quotes->contains($quote)) {
            $this->quotes->removeElement($quote);
            // set the owning side to null (unless already changed)
            if ($quote->getIntervention() === $this) {
                $quote->setIntervention(null);
            }
        }

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getHasEdit(): ?bool
    {
        return $this->hasEdit;
    }

    public function setHasEdit(bool $hasEdit): self
    {
        $this->hasEdit = $hasEdit;

        return $this;
    }



}
