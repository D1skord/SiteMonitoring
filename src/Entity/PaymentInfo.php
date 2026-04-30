<?php

namespace App\Entity;

use App\Repository\PaymentInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentInfoRepository::class)]
class PaymentInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true, options: ['comment' => 'Стоимость поддержки сайта'])]
    private ?float $cost = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => 'Дата оплаты поддержки'])]
    private ?\DateTimeInterface $paymentDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): PaymentInfo
    {
        $this->id = $id;
        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): PaymentInfo
    {
        $this->cost = $cost;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): PaymentInfo
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }
}