<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\JournalLineItemRepository;
use App\Validator\Constraints as CustomAssert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalLineItemRepository::class)]
#[CustomAssert\ValidLineItem]
#[ApiResource]
class JournalLineItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'journalLineItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JournalEntry $journalEntry = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[ORM\Column(nullable: true)]
    private ?float $debitAmount = null;

    #[ORM\Column(nullable: true)]
    private ?float $creditAmount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournalEntry(): ?JournalEntry
    {
        return $this->journalEntry;
    }

    public function setJournalEntry(?JournalEntry $journalEntry): static
    {
        $this->journalEntry = $journalEntry;
        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): static
    {
        $this->account = $account;
        return $this;
    }

    public function getDebitAmount(): ?float
    {
        return $this->debitAmount;
    }

    public function setDebitAmount(float $debitAmount): static
    {
        $this->debitAmount = $debitAmount;

        return $this;
    }

    public function getCreditAmount(): ?float
    {
        return $this->creditAmount;
    }

    public function setCreditAmount(float $creditAmount): static
    {
        $this->creditAmount = $creditAmount;
        return $this;
    }
}
