<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\JournalEntryRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CustomAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JournalEntryRepository::class)]
#[CustomAssert\BalancedJournalEntry]
#[CustomAssert\WithinFiscalYear]
#[ApiResource]
class JournalEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'journalEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Journal $journal = null;

    #[ORM\OneToMany(targetEntity: JournalLineItem::class, mappedBy: 'journalEntry', cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private Collection $journalLineItems;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isClosingEntry = false;

    public function __construct()
    {
        $this->journalLineItems = new ArrayCollection();
    }

    public function isClosingEntry(): bool
    {
        return $this->isClosingEntry;
    }

    public function setIsClosingEntry(bool $isClosingEntry): static
    {
        $this->isClosingEntry = $isClosingEntry;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getJournal(): ?Journal
    {
        return $this->journal;
    }

    public function setJournal(?Journal $journal): static
    {
        $this->journal = $journal;
        return $this;
    }

    /**
     * @return Collection<int, JournalLineItem>
     */
    public function getJournalLineItems(): Collection
    {
        return $this->journalLineItems;
    }

    public function addJournalLineItem(JournalLineItem $journalLineItem): static
    {
        if (!$this->journalLineItems->contains($journalLineItem)) {
            $this->journalLineItems->add($journalLineItem);
            $journalLineItem->setJournalEntry($this);
        }

        return $this;
    }

    public function removeJournalLineItem(JournalLineItem $journalLineItem): static
    {
        if ($this->journalLineItems->removeElement($journalLineItem)) {
            // set the owning side to null (unless already changed)
            if ($journalLineItem->getJournalEntry() === $this) {
                $journalLineItem->setJournalEntry(null);
            }
        }

        return $this;
    }

    public function setJournalLineItems(Collection $journalLineItems): static
    {
        foreach ($this->journalLineItems as $existingItem) {
            $this->removeJournalLineItem($existingItem);
        }

        foreach ($journalLineItems as $lineItem) {
            $this->addJournalLineItem($lineItem);
        }

        return $this;
    }
}
