<?php
namespace App\Entity;

use App\Enum\Status;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $number = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalTtc = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: InvoiceItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->items     = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->status    = Status::DRAFT;
    }

    public function getId(): ?int { return $this->id; }

    public function getNumber(): ?string { return $this->number; }
    public function setNumber(string $number): static { $this->number = $number; return $this; }

    public function getStatus(): ?Status { return $this->status; }
    public function setStatus(Status $status): static { $this->status = $status; return $this; }

    public function getTotalTtc(): ?float { return $this->totalTtc; }
    public function setTotalTtc(?float $totalTtc): static { $this->totalTtc = $totalTtc; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getItems(): Collection { return $this->items; }

    public function addItem(InvoiceItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setInvoice($this);
        }
        return $this;
    }

    public function removeItem(InvoiceItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getInvoice() === $this) $item->setInvoice(null);
        }
        return $this;
    }

    public function computeAndSaveTotalTtc(): void
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        $this->totalTtc = $total;
    }

    public function isDraft(): bool   { return $this->status === Status::DRAFT; }
    public function isPending(): bool { return $this->status === Status::PENDING_PAYMENT; }
    public function isPaid(): bool    { return $this->status === Status::PAID; }

    public function isCancelled():bool{return $this->status === Status::CANCELLED;}
    public function isDeletable(): bool { return $this->isDraft(); }
}