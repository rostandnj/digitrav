<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuoteRepository")
 */
class Quote
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
     * @ORM\Column(type="boolean")
     */
    private $statut;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $technician;



    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Bill")
     */
    private $bill;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Intervention", inversedBy="quotes")
     */
    private $intervention;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $amount;

    const NEW =0;
    const ACCEPTED =1;
    const REFUSED =2;
    const PAID =3;
    const DONE=4;


    /**
     * Quote constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->statut = self::NEW;
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

    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    public function getTechnician(): ?User
    {
        return $this->technician;
    }

    public function setTechnician(?User $technician): self
    {
        $this->technician = $technician;

        return $this;
    }



    public function getBill(): ?Bill
    {
        return $this->bill;
    }

    public function setBill(?Bill $bill): self
    {
        $this->bill = $bill;

        return $this;
    }

    public function toArray()
    {
        $tab =["id"=>$this->id,
            "intervention_id"=>$this->getIntervention()->getId(),
            "intervention_slug"=>$this->getIntervention()->getSlug(),
            "amount"=>$this->amount,
            "date"=>$this->getDate()->format(\DateTime::ISO8601),
            "is_active"=>$this->isActive,"statut"=>$this->statut,"technician"=>$this->getTechnician()->toArrayShort()];

        return $tab;
    }

    public function getIntervention(): ?Intervention
    {
        return $this->intervention;
    }

    public function setIntervention(?Intervention $intervention): self
    {
        $this->intervention = $intervention;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
