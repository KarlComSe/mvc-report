<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Asset\Exception\InvalidArgumentException;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[ApiResource]
class Account
{
    public const TYPE_ASSET = 'asset';           // Tillgångar (1000-1999)
    public const TYPE_LIABILITY = 'liability';   // Skulder (2000-2999)
    public const TYPE_EQUITY = 'equity';         // Eget kapital (2000-2999)
    public const TYPE_REVENUE = 'revenue';       // Intäkter (3000-3999)
    public const TYPE_EXPENSE = 'expense';       // Kostnader (4000-8999)

    public const TYPES = [
        self::TYPE_ASSET => 'Tillgångar',
        self::TYPE_LIABILITY => 'Skulder',
        self::TYPE_EQUITY => 'Eget kapital',
        self::TYPE_REVENUE => 'Intäkter',
        self::TYPE_EXPENSE => 'Kostnader'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $accountNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column]
    private ?bool $isStandard = true;

    #[ORM\ManyToOne(inversedBy: 'accounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ChartOfAccounts $chartOfAccounts = null;

    // ==========================================
    // AI-assisted: Added hierarchy support fields
    // Purpose: Support 4-level BAS account hierarchy (1 > 10 > 101 > 1010)
    // ==========================================

    /**
     * AI-assisted: Parent account number for hierarchy navigation
     * Example: For account 1010, parent is 101
     */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $parentAccountNumber = null;

    /**
     * AI-assisted: Hierarchy level (1-4) based on account number length
     * Level 1: Top categories (1 digit)
     * Level 2: Sub-categories (2 digits)
     * Level 3: Group accounts (3 digits)
     * Level 4: Detail accounts (4 digits)
     */
    #[ORM\Column(type: 'smallint')]
    private int $hierarchyLevel = 4;

    /**
     * AI-assisted: Flag to distinguish detail (posting) accounts from group accounts
     * true = Detail account (4 digits, can receive transactions)
     * false = Group account (1-3 digits, used for organization/reporting only)
     */
    #[ORM\Column]
    private bool $isDetailAccount = true;

    // ==========================================
    // End of AI-assisted hierarchy fields
    // ==========================================


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        if (!in_array($type, array_keys(self::TYPES))) {
            throw new InvalidArgumentException('Invalid account type');
        }
        $this->type = $type;
        return $this;
    }

    public function getTypeName(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
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

    public function getChartOfAccounts(): ?ChartOfAccounts
    {
        return $this->chartOfAccounts;
    }

    public function setChartOfAccounts(?ChartOfAccounts $chartOfAccounts): static
    {
        $this->chartOfAccounts = $chartOfAccounts;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->accountNumber . ' - ' . $this->name;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    // ==========================================
    // AI-assisted: Hierarchy getters and setters
    // ==========================================

    public function getParentAccountNumber(): ?string
    {
        return $this->parentAccountNumber;
    }

    public function setParentAccountNumber(?string $parentAccountNumber): static
    {
        $this->parentAccountNumber = $parentAccountNumber;
        return $this;
    }

    public function getHierarchyLevel(): int
    {
        return $this->hierarchyLevel;
    }

    public function setHierarchyLevel(int $hierarchyLevel): static
    {
        $this->hierarchyLevel = $hierarchyLevel;
        return $this;
    }

    public function isDetailAccount(): bool
    {
        return $this->isDetailAccount;
    }

    public function setIsDetailAccount(bool $isDetailAccount): static
    {
        $this->isDetailAccount = $isDetailAccount;
        return $this;
    }

    // ==========================================
    // AI-assisted: Helper methods for hierarchy navigation and display
    // ==========================================

    /**
     * AI-assisted: Get indented name based on hierarchy level
     * Used for displaying accounts in hierarchical lists
     * Example: "  1010 - Utvecklingsutgifter" (indented by level)
     */
    public function getIndentedName(): string
    {
        $indent = str_repeat('  ', $this->hierarchyLevel - 1);
        return $indent . $this->getFullName();
    }

    /**
     * AI-assisted: Check if this account can have transactions posted to it
     * Only detail accounts (level 4) can receive journal entries
     * Group accounts are for organization and reporting only
     */
    public function canHaveTransactions(): bool
    {
        return $this->isDetailAccount;
    }

    // ==========================================
    // End of AI-assisted helper methods
    // ==========================================
}
