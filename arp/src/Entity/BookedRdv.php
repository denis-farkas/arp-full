<?php

namespace App\Entity;

use App\Repository\BookedRdvRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookedRdvRepository::class)]
class BookedRdv
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $beginAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rdv $rdv = null;

    #[ORM\Column(length: 100)]
    private ?string $clientSurname = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $bookingToken = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isPaid = false;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentId = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->beginAt;
    }

    public function setBeginAt(\DateTimeImmutable $beginAt): static
    {
        $this->beginAt = $beginAt;
        return $this;
    }

    public function getRdv(): ?Rdv
    {
        return $this->rdv;
    }

    public function setRdv(?Rdv $rdv): static
    {
        $this->rdv = $rdv;
        return $this;
    }

    public function getClientSurname(): ?string
    {
        return $this->clientSurname;
    }

    public function setClientSurname(string $clientSurname): static
    {
        $this->clientSurname = $clientSurname;
        return $this;
    }

    public function getBookingToken(): ?string
    {
        return $this->bookingToken;
    }

    public function setBookingToken(string $bookingToken): static
    {
        $this->bookingToken = $bookingToken;
        return $this;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;
        return $this;
    }
    
    // Alias method for PaymentController compatibility
    public function setPaid(bool $paid): static
    {
        return $this->setIsPaid($paid);
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(?string $paymentId): static
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    public function generateBookingToken(): string
    {
        $this->bookingToken = bin2hex(random_bytes(16));
        return $this->bookingToken;
    }
}