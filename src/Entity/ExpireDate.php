<?php

namespace App\Entity;

use App\Repository\ExpireDateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[ORM\Entity(repositoryClass: ExpireDateRepository::class)]
#[HasLifecycleCallbacks]
class ExpireDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $domain = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ssl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): ExpireDate
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    #[PreUpdate, PrePersist]
    public function updateAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getDomain(): ?\DateTimeInterface
    {
        return $this->domain;
    }

    public function setDomain(?\DateTimeInterface $domain): void
    {
        $this->domain = $domain;
    }

    public function getSsl(): ?\DateTimeInterface
    {
        return $this->ssl;
    }

    public function setSsl(?\DateTimeInterface $ssl): void
    {
        $this->ssl = $ssl;
    }
}