<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\JournalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JournalRepository::class)]
#[ApiResource]
class Journal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $FirstDay = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $LastDay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ChartOfAccount = null;

    /**
     * @var Collection<int, JournalEntry>
     */
    #[ORM\OneToMany(targetEntity: JournalEntry::class, mappedBy: 'Journal', orphanRemoval: true)]
    private Collection $journalEntries;

    public function __construct()
    {
        $this->journalEntries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstDay(): ?\DateTimeInterface
    {
        return $this->FirstDay;
    }

    public function setFirstDay(\DateTimeInterface $FirstDay): static
    {
        $this->FirstDay = $FirstDay;

        return $this;
    }

    public function getLastDay(): ?\DateTimeInterface
    {
        return $this->LastDay;
    }

    public function setLastDay(\DateTimeInterface $LastDay): static
    {
        $this->LastDay = $LastDay;

        return $this;
    }

    public function getChartOfAccount(): ?string
    {
        return $this->ChartOfAccount;
    }

    public function setChartOfAccount(?string $ChartOfAccount): static
    {
        $this->ChartOfAccount = $ChartOfAccount;

        return $this;
    }

    /**
     * @return Collection<int, JournalEntry>
     */
    public function getJournalEntries(): Collection
    {
        return $this->journalEntries;
    }

    public function addJournalEntry(JournalEntry $journalEntry): static
    {
        if (!$this->journalEntries->contains($journalEntry)) {
            $this->journalEntries->add($journalEntry);
            $journalEntry->setJournal($this);
        }

        return $this;
    }

    public function removeJournalEntry(JournalEntry $journalEntry): static
    {
        if ($this->journalEntries->removeElement($journalEntry)) {
            // set the owning side to null (unless already changed)
            if ($journalEntry->getJournal() === $this) {
                $journalEntry->setJournal(null);
            }
        }

        return $this;
    }
}
