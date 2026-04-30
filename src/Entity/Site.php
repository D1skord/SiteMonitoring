<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: StatusLog::class, orphanRemoval: true)]
    private Collection $statusLogs;

    #[OneToOne(targetEntity: ExpireDate::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: true)]
    private ?ExpireDate $expireDate = null;

    #[OneToOne(targetEntity: PaymentInfo::class, cascade: ['persist', 'remove'])]
    #[JoinColumn(nullable: true)]
    private ?PaymentInfo $paymentInfo = null;

    #[ORM\Column(nullable: true)]
    private array $transport = [];

    public function __construct()
    {
        $this->statusLogs = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, StatusLog>
     */
    public function getStatusLogs(): Collection
    {
        return $this->statusLogs;
    }

    public function addStatusLog(StatusLog $statusLog): self
    {
        if (!$this->statusLogs->contains($statusLog)) {
            $this->statusLogs->add($statusLog);
            $statusLog->setSite($this);
        }

        return $this;
    }

    public function removeStatusLog(StatusLog $statusLog): self
    {
        if ($this->statusLogs->removeElement($statusLog)) {
            // set the owning side to null (unless already changed)
            if ($statusLog->getSite() === $this) {
                $statusLog->setSite(null);
            }
        }

        return $this;
    }

    public function getTransport(): array
    {
        return $this->transport;
    }

    public function setTransport(?array $transport): self
    {
        $this->transport = $transport;

        return $this;
    }

    public function getExpireDate(): ?ExpireDate
    {
        return $this->expireDate;
    }

    public function setExpireDate(?ExpireDate $expireDate): void
    {
        $this->expireDate = $expireDate;
    }

    public function getPaymentInfo(): ?PaymentInfo
    {
        return $this->paymentInfo;
    }

    public function setPaymentInfo(?PaymentInfo $paymentInfo): Site
    {
        $this->paymentInfo = $paymentInfo;
        return $this;
    }
}
