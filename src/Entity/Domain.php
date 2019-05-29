<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DomainRepository")
 */
class Domain
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $name;



    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Category", mappedBy="domain", orphanRemoval=true,cascade={"persist","remove"})
     */
    private $categories;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameFr;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $nameEn;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MaterialQuote", mappedBy="domain", orphanRemoval=true)
     */
    private $materialQuotes;

    /**
     * @Gedmo\Slug(fields={"nameEn"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbJob;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $note;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->materialQuotes = new ArrayCollection();
        $this->nbJob=0;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $nameFr): self
    {
        $this->name = $nameFr;

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

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setDomain($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getDomain() === $this) {
                $category->setDomain(null);
            }
        }

        return $this;
    }

    public function toArray()
    {
        $tab =["id"=>$this->id,
            "date"=>$this->getDate()->format(\DateTime::ISO8601),
            "is_active"=>$this->isActive,"name"=>$this->name,"name_fr"=>$this->nameFr,"name_en"=>$this->nameEn,"slug"=>$this->slug];

        return $tab;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(?string $nameFr): self
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): self
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    /**
     * @return Collection|MaterialQuote[]
     */
    public function getMaterialQuotes(): Collection
    {
        return $this->materialQuotes;
    }

    public function addMaterialQuote(MaterialQuote $materialQuote): self
    {
        if (!$this->materialQuotes->contains($materialQuote)) {
            $this->materialQuotes[] = $materialQuote;
            $materialQuote->setDomain($this);
        }

        return $this;
    }

    public function removeMaterialQuote(MaterialQuote $materialQuote): self
    {
        if ($this->materialQuotes->contains($materialQuote)) {
            $this->materialQuotes->removeElement($materialQuote);
            // set the owning side to null (unless already changed)
            if ($materialQuote->getDomain() === $this) {
                $materialQuote->setDomain(null);
            }
        }

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

    public function getNbJob(): ?int
    {
        return $this->nbJob;
    }

    public function setNbJob(int $nbJob): self
    {
        $this->nbJob = $nbJob;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

}
