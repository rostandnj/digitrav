<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment
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
    private $reference;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $receiver;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $notifToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payUrl;



    const ORANGE_MONEY=0;
    const MOBILE_MONEY=1;
    const EXPRESS_UNION=2;
    const ORDER=3;

    /**
     * Payment constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;
        $this->statut = false;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver): self
    {
        $this->receiver = $receiver;

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

    public function toArray()
    {
        $tab=["id"=>$this->id,"reference"=>$this->reference,"amount"=>$this->amount,"type"=>$this->type,"client"=>$this->client,
            "receiver"=>$this->receiver,"date"=>$this->getDate()->format(\DateTime::ISO8601)];

        return $tab;
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

    public function getPayToken(): ?string
    {
        return $this->payToken;
    }

    public function setPayToken(?string $payToken): self
    {
        $this->payToken = $payToken;

        return $this;
    }

    public function getNotifToken(): ?string
    {
        return $this->notifToken;
    }

    public function setNotifToken(?string $notifToken): self
    {
        $this->notifToken = $notifToken;

        return $this;
    }

    public function getPayUrl(): ?string
    {
        return $this->payUrl;
    }

    public function setPayUrl(?string $payUrl): self
    {
        $this->payUrl = $payUrl;

        return $this;
    }
}
