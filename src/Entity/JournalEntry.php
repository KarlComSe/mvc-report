<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\JournalEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalEntryRepository::class)]
#[ApiResource]
class JournalEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column(nullable: true)]
    private ?float $Amount = null;

    #[ORM\ManyToOne(inversedBy: 'journalEntries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Journal $Journal = null;

    #[ORM\ManyToOne(inversedBy: 'JournalEntry')]
    private ?JournalLineItem $journalLineItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->Amount;
    }

    public function setAmount(?float $Amount): static
    {
        $this->Amount = $Amount;

        return $this;
    }

    public function getJournal(): ?Journal
    {
        return $this->Journal;
    }

    public function setJournal(?Journal $Journal): static
    {
        $this->Journal = $Journal;

        return $this;
    }

    public function getJournalLineItem(): ?JournalLineItem
    {
        return $this->journalLineItem;
    }

    public function setJournalLineItem(?JournalLineItem $journalLineItem): static
    {
        $this->journalLineItem = $journalLineItem;

        return $this;
    }
}
