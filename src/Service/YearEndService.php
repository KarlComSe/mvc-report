<?php

namespace App\Service;

use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeInterface;
use LogicException;
use InvalidArgumentException;

class YearEndService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FinancialReportService $reportService
    ) {
    }

    public function closeIncomeStatement(
        Journal $journal,
        ?DateTimeInterface $closingDate,
        ?string $description = null
    ): JournalEntry {
        if ($closingDate === null) {
            throw new InvalidArgumentException(
                'Closing date cannot be null. A valid date is required to close the income statement.'
            );
        }

        // need to include closing entries, if user is repeatedly closing, it should not include amounts that has already been closed.
        $incomeStatement = $this->reportService->getIncomeStatementWithClosingEntries($journal);

        $closingEntry = new JournalEntry();
        $closingEntry->setTitle(
            $description ?? 'Årsbokslut ' . $closingDate->format('Y')
        );
        $closingEntry->setDate($closingDate);
        $closingEntry->setJournal($journal);
        $closingEntry->setIsClosingEntry(true);

        // Hur hantera de fall där det inte finns något på intäkts eller kostnadskonton?

        // Nolla intäktskonton
        $this->closeRevenueAccounts($closingEntry, $incomeStatement['revenues']);

        // Nolla kostnadskonton
        $this->closeExpenseAccounts($closingEntry, $incomeStatement['expenses']);

        // Överför resultat till balanserad vinst
        $this->transferNetIncomeToEquity($closingEntry, $incomeStatement['netIncome']);

        $this->entityManager->persist($closingEntry);
        $this->entityManager->flush();

        return $closingEntry;
    }

    /**
     * @param array<string, mixed> $revenues
     */
    private function closeRevenueAccounts(JournalEntry $closingEntry, array $revenues): void
    {
        foreach ($revenues as $revenueData) {
            $balance = $revenueData['balance'];

            if (abs($balance) < 0.01) {
                continue;
            }

            $account = $revenueData['account'];

            // Debet intäktskonto för att nolla det
            $lineItem = new JournalLineItem();
            $lineItem->setAccount($account);
            $lineItem->setDebitAmount($balance);

            $closingEntry->addJournalLineItem($lineItem);
        }
    }

    /**
     * @param array<string, mixed> $expenses
     */
    private function closeExpenseAccounts(JournalEntry $closingEntry, array $expenses): void
    {
        foreach ($expenses as $expenseData) {
            $balance = $expenseData['balance'];

            if (abs($balance) < 0.01) {
                continue;
            }

            $account = $expenseData['account'];

            // Kredit kostnadskonto för att nolla det
            $lineItem = new JournalLineItem();
            $lineItem->setAccount($account);
            $lineItem->setCreditAmount($balance);

            $closingEntry->addJournalLineItem($lineItem);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function transferNetIncomeToEquity(JournalEntry $closingEntry, float $netIncome): void
    {
        if (abs($netIncome) < 0.01) {
            return;
        }

        $accountRepo = $this->entityManager->getRepository(Account::class);
        $chart = $closingEntry->getJournal()->getChartOfAccounts();
        $retainedAccount = $accountRepo->findOneBy([
            'accountNumber' => '2099',
            'chartOfAccounts' => $chart
        ]);

        if (!$retainedAccount) {
            // The lazy human in me implements this and assumes standard accounts.
            // Also jumps the step of taking the amount to 8999
            throw new LogicException(
                'Årets resultat (2099) finns inte i din kontoplan. \
                Programmet stödjer inte automatisk kontering med din kontoplan.'
            );
        }

        $lineItem = new JournalLineItem();
        $lineItem->setAccount($retainedAccount);

        if ($netIncome > 0) {
            // Vinst: kredit eget kapital
            $lineItem->setCreditAmount($netIncome);
        } else {
            // Förlust: debet eget kapital
            $lineItem->setDebitAmount(abs($netIncome));
        }

        $closingEntry->addJournalLineItem($lineItem);
    }
}
