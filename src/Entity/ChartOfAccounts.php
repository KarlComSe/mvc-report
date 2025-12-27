<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChartOfAccountsRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChartOfAccountsRepository::class)]
#[ApiResource]
class ChartOfAccounts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $basVersion = null;

    #[ORM\Column]
    private ?bool $isStandard = true;

    #[ORM\ManyToOne(inversedBy: 'chartOfAccounts')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Organization $organization = null; // null for standard BAS, set for custom versions

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'customVersions')]
    #[ORM\JoinColumn(nullable: true)]
    private ?self $basedOn = null; // reference to standard chart if this is custom

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'basedOn')]
    private Collection $customVersions;

    /**
     * @var Collection<int, Account>
     */
    #[ORM\OneToMany(targetEntity: Account::class, mappedBy: 'chartOfAccounts', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['accountNumber' => 'ASC'])]
    private Collection $accounts;

    /**
     * @var Collection<int, Journal>
     */
    #[ORM\OneToMany(targetEntity: Journal::class, mappedBy: 'chartOfAccounts')]
    private Collection $journals;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->customVersions = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->journals = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * AI-assisted: Getter for BAS version
     * Used to identify which BAS standard (2024, 2025, etc.)
     */
    public function getBasVersion(): ?string
    {
        return $this->basVersion;
    }

    /**
     * AI-assisted: Setter for BAS version
     * Used to track which BAS standard this chart follows
     */
    public function setBasVersion(?string $basVersion): static
    {
        $this->basVersion = $basVersion;
        return $this;
    }

    public function isStandard(): ?bool
    {
        return $this->isStandard;
    }

    public function setIsStandard(bool $isStandard): static
    {
        $this->isStandard = $isStandard;
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

    public function getBasedOn(): ?self
    {
        return $this->basedOn;
    }

    public function setBasedOn(?self $basedOn): static
    {
        $this->basedOn = $basedOn;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCustomVersions(): Collection
    {
        return $this->customVersions;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function getAccount(string $accountNumber): ?Account
    {
        foreach ($this->accounts as $account) {
            if ($account->getAccountNumber() === $accountNumber) {
                return $account;
            }
        }

        return null;
    }

    public function addAccount(Account $account): static
    {
        if (!$this->accounts->contains($account)) {
            $this->accounts->add($account);
            $account->setChartOfAccounts($this);
        }
        return $this;
    }

    public function removeAccount(Account $account): static
    {
        if ($this->accounts->removeElement($account)) {
            if ($account->getChartOfAccounts() === $this) {
                $account->setChartOfAccounts(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Journal>
     */
    public function getJournals(): Collection
    {
        return $this->journals;
    }

    public function addJournal(Journal $journal): static
    {
        if (!$this->journals->contains($journal)) {
            $this->journals->add($journal);
            $journal->setChartOfAccounts($this);
        }
        return $this;
    }

    public function removeJournal(Journal $journal): static
    {
        if ($this->journals->removeElement($journal)) {
            if ($journal->getChartOfAccounts() === $this) {
                $journal->setChartOfAccounts(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function __toString(): string
    {
        $suffix = $this->isStandard ? ' (Standard)' : ' (Anpassad)';
        return $this->name . $suffix;
    }

    /**
     * Get the base chart name and version for display
     */
    public function getDisplayName(): ?string
    {
        if ($this->organization) {
            return $this->name . ' - ' . $this->organization->getName();
        }
        return $this->name;
    }
}
