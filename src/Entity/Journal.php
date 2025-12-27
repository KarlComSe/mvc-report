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
    private ?\DateTimeInterface $firstDay = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $lastDay = null;

    #[ORM\ManyToOne(inversedBy: 'journals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ChartOfAccounts $chartOfAccounts = null;

    /**
     * @var Collection<int, JournalEntry>
     */
    #[ORM\OneToMany(targetEntity: JournalEntry::class, mappedBy: 'journal', orphanRemoval: true)]
    private Collection $journalEntries;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'journals')]
    private ?Organization $organization = null;

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
        return $this->firstDay;
    }

    public function setFirstDay(\DateTimeInterface $firstDay): static
    {
        $this->firstDay = $firstDay;

        return $this;
    }

    public function getLastDay(): ?\DateTimeInterface
    {
        return $this->lastDay;
    }

    public function setLastDay(\DateTimeInterface $lastDay): static
    {
        $this->lastDay = $lastDay;

        return $this;
    }

    public function getChartOfAccounts(): ?ChartOfAccounts
    {
        return $this->chartOfAccounts;
    }

    public function setChartOfAccounts(?ChartOfAccounts $chartOfAccounts): static
    {
        $this->chartOfAccounts = $chartOfAccounts;

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

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            'Journal %d (%s - %s)',
            $this->id,
            $this->firstDay?->format('Y-m-d'),
            $this->lastDay?->format('Y-m-d')
        );
    }
}
