<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\JournalLineItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalLineItemRepository::class)]
#[ApiResource]
class JournalLineItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, JournalEntry>
     */
    #[ORM\OneToMany(targetEntity: JournalEntry::class, mappedBy: 'journalLineItem')]
    private Collection $JournalEntry;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'journalLineItem')]
    private Collection $Account;

    #[ORM\Column]
    private ?float $DebitAmount = null;

    #[ORM\Column]
    private ?float $CreditAmount = null;

    public function __construct()
    {
        $this->JournalEntry = new ArrayCollection();
        $this->Account = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, JournalEntry>
     */
    public function getJournalEntry(): Collection
    {
        return $this->JournalEntry;
    }

    public function addJournalEntry(JournalEntry $journalEntry): static
    {
        if (!$this->JournalEntry->contains($journalEntry)) {
            $this->JournalEntry->add($journalEntry);
            $journalEntry->setJournalLineItem($this);
        }

        return $this;
    }

    public function removeJournalEntry(JournalEntry $journalEntry): static
    {
        if ($this->JournalEntry->removeElement($journalEntry)) {
            // set the owning side to null (unless already changed)
            if ($journalEntry->getJournalLineItem() === $this) {
                $journalEntry->setJournalLineItem(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccount(): Collection
    {
        return $this->Account;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->Account->contains($account)) {
            $this->Account->add($account);
            $account->setJournalLineItem($this);
        }

        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->Account->removeElement($account)) {
            // set the owning side to null (unless already changed)
            if ($account->getJournalLineItem() === $this) {
                $account->setJournalLineItem(null);
            }
        }

        return $this;
    }

    public function getDebitAmount(): ?float
    {
        return $this->DebitAmount;
    }

    public function setDebitAmount(float $DebitAmount): static
    {
        $this->DebitAmount = $DebitAmount;

        return $this;
    }

    public function getCreditAmount(): ?float
    {
        return $this->CreditAmount;
    }

    public function setCreditAmount(float $CreditAmount): static
    {
        $this->CreditAmount = $CreditAmount;

        return $this;
    }
}
