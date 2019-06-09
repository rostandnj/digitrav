<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ToolQuoteRepository")
 */
class ToolQuote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="datetimetz")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MaterialQuote")
     * @ORM\JoinColumn(nullable=false)
     */
    private $material;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $technician;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quote", inversedBy="toolQuotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $quote;

    /**
     * ToolQuote constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->quantity=0;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getMaterial(): ?MaterialQuote
    {
        return $this->material;
    }

    public function setMaterial(?MaterialQuote $material): self
    {
        $this->material = $material;

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

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function toArray()
    {
        $tab["id"] =$this->id;
        $tab["quantity"]=$this->quantity;
        $tab["material"]=$this->getMaterial()->toArray();

        return $tab;
    }
}
