<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
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
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Intervention")
     */
    private $intervention;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quote")
     */
    private $quote;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluation")
     */
    private $evaluation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Note",cascade={"persist","remove"})
     */
    private $note;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Bill")
     */
    private $bill;

    private $url;

    private $uid;

    const QUOTATION_INVITATION_ACCEPTED=-1;
    const QUOTATION_INVITATION=0;
    const QUOTATION_NEW=1;
    const QUOTATION_ACCEPTED=2;
    const QUOTATION_REFUSED=3;
    const EVALUATION_NEW=4;
    const EVALUATION_ACCEPTED=5;
    const EVALUATION_REFUSED=6;
    const QUOTATION_ENDED=7;
    const NOTE_RECEIVED=8;
    const ACCOUNT_VALIDATED=9;
    const ACCOUNT_LOCKED=10;
    const PAYMENT_RECEIVED=11;


    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->isActive = true;

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

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getEvaluation(): ?Evaluation
    {
        return $this->evaluation;
    }

    public function setEvaluation(?Evaluation $evaluation): self
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    public function getNote(): ?Note
    {
        return $this->note;
    }

    public function setNote(?Note $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Notification
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
     * @return Notification
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
        return $this;
    }



    public function toArray()
    {
        $tab =["id"=>$this->id,
            "date"=>$this->getDate()->format(\DateTime::ISO8601),
            "code"=>$this->code,"user"=>$this->getUser()->toArrayShort(),
        "url"=>$this->getUrl(),
            "uid"=>$this->getUid()
        ];

        switch ($this->code)
        {
            case self::QUOTATION_INVITATION_ACCEPTED:
                $tab["quote"]=$this->getQuote()->toArray();
                break;
            case self::QUOTATION_NEW:
                $tab["quote"]=$this->getQuote()->toArray();
                break;
            case self::QUOTATION_ACCEPTED:
                $tab["quote"]=$this->getQuote()->toArray();
                break;
            case self::QUOTATION_REFUSED:
                $tab["quote"]=$this->getQuote()->toArray();
                break;
            case self::QUOTATION_ENDED:
                $tab["quote"]=$this->getQuote()->toArray();
                break;
            case self::EVALUATION_NEW:
                $tab["evaluation"]=$this->getEvaluation()->toArray();
                break;
            case self::EVALUATION_ACCEPTED:
                $tab["evaluation"]=$this->getEvaluation()->toArray();
                break;
            case self::EVALUATION_REFUSED:
                $tab["evaluation"]=$this->getEvaluation()->toArray();
                break;
            case self::ACCOUNT_LOCKED:
                //$tab["user"]=$this->getUser()->toArray();
                break;
            case self::ACCOUNT_VALIDATED:
                //$tab["user"]=$this->getUser()->toArray();
                break;
            case self::NOTE_RECEIVED:
                $tab["note"]=$this->getNote()->toArray();
                break;
            case self::PAYMENT_RECEIVED:
                $tab["bill"]=$this->getBill()->toArray();
                break;
            default:
                break;
        }

        return $tab;
    }
}
