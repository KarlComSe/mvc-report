<?php

namespace App\Service;

use App\Entity\Journal;
use App\Entity\JournalLineItem;
use App\Entity\Account;

class FinancialReportService
{

    /*
    * Default should be to skipClosingEntries in the income statement
    */

    /**
     * @return array<string,mixed>
     */

    public function getIncomeStatement(Journal $journal): array
    {
        return $this->buildIncomeStatement($journal, skipClosingEntries: true);
    }

    /**
     * @return array<string,mixed>
     */
    public function getIncomeStatementWithClosingEntries(Journal $journal): array
    {
        return $this->buildIncomeStatement($journal, skipClosingEntries: false);
    }

    /**
     * Generate income statement (Resultaträkning)
     * @return array<string,mixed>
     */
    public function buildIncomeStatement(Journal $journal, bool $skipClosingEntries): array
    {
        $revenues = [];
        $expenses = [];

        // Traverse journal entries and line items
        foreach ($journal->getJournalEntries() as $entry) {
            if ($skipClosingEntries && $entry->isClosingEntry()) {
                continue;
            }

            foreach ($entry->getJournalLineItems() as $lineItem) {
                $account = $lineItem->getAccount();

                // Filter by account type
                if ($account->getType() === Account::TYPE_REVENUE) {
                    $this->addToAccountGroup($revenues, $lineItem);
                } elseif ($account->getType() === Account::TYPE_EXPENSE) {
                    $this->addToAccountGroup($expenses, $lineItem);
                }
            }
        }

        // Build hierarchies and calculate totals
        $revenueTree = $this->buildHierarchy($revenues);
        $expenseTree = $this->buildHierarchy($expenses);

        $totalRevenue = $this->calculateTotalForAccountType($revenues, Account::TYPE_REVENUE);
        $totalExpenses = $this->calculateTotalForAccountType($expenses, Account::TYPE_EXPENSE);

        return [
            'revenues' => $revenueTree,
            'expenses' => $expenseTree,
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netIncome' => $totalRevenue - $totalExpenses
        ];
    }

    /*
    * Default should be to not skipClosingEntries in the balance sheet
    */
    /**
     * @return array<string,mixed>
     */
    public function getBalanceSheet(Journal $journal): array
    {
        return $this->buildBalanceSheet($journal, skipClosingEntries: false);
    }

    /**
     * @return array<string,mixed>
     */
    public function getBalanceSheetWithoutClosingEntries(Journal $journal): array
    {
        return $this->buildBalanceSheet($journal, skipClosingEntries: true);
    }

    /**
     * Generate balance sheet (Balansräkning)
     * @return array<string,mixed>
     */
    public function buildBalanceSheet(Journal $journal, bool $skipClosingEntries): array
    {
        $assets = [];
        $liabilities = [];
        $equity = [];

        // Traverse journal entries and line items
        foreach ($journal->getJournalEntries() as $entry) {
            if ($skipClosingEntries && $entry->isClosingEntry()) {
                continue;
            }

            foreach ($entry->getJournalLineItems() as $lineItem) {
                $account = $lineItem->getAccount();

                // Filter by account type
                match ($account->getType()) {
                    Account::TYPE_ASSET => $this->addToAccountGroup($assets, $lineItem),
                    Account::TYPE_LIABILITY => $this->addToAccountGroup($liabilities, $lineItem),
                    Account::TYPE_EQUITY => $this->addToAccountGroup($equity, $lineItem),
                    default => null
                };
            }
        }

        // Build hierarchies and calculate totals
        $assetTree = $this->buildHierarchy($assets);
        $liabilityTree = $this->buildHierarchy($liabilities);
        $equityTree = $this->buildHierarchy($equity);

        $totalAssets = $this->calculateTotalForAccountType($assets, Account::TYPE_ASSET);
        $totalLiabilities = $this->calculateTotalForAccountType($liabilities, Account::TYPE_LIABILITY);
        $totalEquity = $this->calculateTotalForAccountType($equity, Account::TYPE_EQUITY);

        return [
            'assets' => $assetTree,
            'liabilities' => $liabilityTree,
            'equity' => $equityTree,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01
        ];
    }

    /**
     * Add line item amounts to account group
     * @param array<string, mixed> &$group
     */
    private function addToAccountGroup(array &$group, JournalLineItem $lineItem): void
    {
        $accountId = $lineItem->getAccount()->getId();

        if (!isset($group[$accountId])) {
            $group[$accountId] = [
                'account' => $lineItem->getAccount(),
                'totalDebit' => 0,
                'totalCredit' => 0,
                'balance' => 0
            ];
        }

        $group[$accountId]['totalDebit'] += $lineItem->getDebitAmount() ?? 0;
        $group[$accountId]['totalCredit'] += $lineItem->getCreditAmount() ?? 0;
    }

    /**
     * Build hierarchical structure from flat account groups
     * @param array<string, mixed> $accountGroups
     * @return array<int, mixed>
     */
    private function buildHierarchy(array $accountGroups): array
    {
        // Calculate balances for all accounts
        foreach ($accountGroups as &$accountData) {
            $account = $accountData['account'];
            $accountData['balance'] = $this->calculateAccountBalance(
                $accountData['totalDebit'],
                $accountData['totalCredit'],
                $account->getType()
            );
        }

        // Sort by account number
        uasort($accountGroups, function ($account, $otherAccount) {
            return strcmp($account['account']->getAccountNumber(), $otherAccount['account']->getAccountNumber());
        });

        return array_values($accountGroups);
    }

    /**
     * Calculate balance based on account type
     * Debit accounts (Assets, Expenses): Debit - Credit
     * Credit accounts (Liabilities, Equity, Revenue): Credit - Debit
     */
    private function calculateAccountBalance(float $debit, float $credit, string $accountType): float
    {
        $debitAccounts = [Account::TYPE_ASSET, Account::TYPE_EXPENSE];

        if (in_array($accountType, $debitAccounts)) {
            return $debit - $credit;
        }

        return $credit - $debit;
    }

    /**
     * Calculate total for all accounts of a specific type
     * @param array<string, mixed> $accountGroups
     */
    private function calculateTotalForAccountType(array $accountGroups, string $accountType): float
    {
        $total = 0;

        foreach ($accountGroups as $accountData) {
            $total += $this->calculateAccountBalance(
                $accountData['totalDebit'],
                $accountData['totalCredit'],
                $accountType
            );
        }

        return $total;
    }
}
