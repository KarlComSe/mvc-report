<?php

namespace App\Tests\Service;

use App\Entity\{JournalLineItem, JournalEntry, Account, Journal};
use App\Service\{YearEndService, FinancialReportService};
use App\Tests\Fixtures\AccountingFixtureLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use LogicException;
use InvalidArgumentException;

class YearEndServiceTest extends KernelTestCase
{
    private YearEndService $service;
    private EntityManagerInterface $em;
    private AccountingFixtureLoader $fixtureLoader;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();

        $reportService = new FinancialReportService();
        $this->service = new YearEndService($this->em, $reportService);
        $this->fixtureLoader = new AccountingFixtureLoader($this->em);
    }

    /**
     * Tests year-end closure with Scenario A (Net LOSS)
     *
     * Financial Summary:
     * - Revenue: 10,000 SEK (account 3041)
     * - Expenses: 11,614 SEK (accounts 6570, 6540, 7010, 7510)
     * - Net Result: -1,614 SEK (LOSS)
     *
     * Expected Closing Entry:
     * - Debit 3041 (Revenue): 10,000 SEK (zero out revenue)
     * - Credit 6570 (Bankavgifter): 100 SEK (zero out expense)
     * - Credit 6540 (Konsulttjänster): 1,000 SEK (zero out expense)
     * - Credit 7010 (Löner): 8,000 SEK (zero out expense)
     * - Credit 7510 (Arbetsgivaravgifter): 2,514 SEK (zero out expense)
     * - Debit 2099 (Årets resultat): 1,614 SEK (loss reduces equity)
     *
     * Total Debits: 11,614 SEK, Total Credits: 11,614 SEK
     */
    public function testClosingEntryWithLossScenarioA(): void
    {
        $journal = $this->fixtureLoader->loadScenario('A');

        $closingEntry = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31'),
            'Årsbokslut 2025'
        );

        $this->assertInstanceOf(JournalEntry::class, $closingEntry);
        $this->assertTrue($closingEntry->isClosingEntry());
        $this->assertEquals('Årsbokslut 2025', $closingEntry->getTitle());
        // foreach ($journal->getJournalEntries() as $jorunalEntry) {
        //     dump($jorunalEntry);
        // }
        $revenueLineItem = $this->findLineItemForAccount($closingEntry, '3041');
        $this->assertNotNull($revenueLineItem);
        $this->assertEquals(10000, $revenueLineItem->getDebitAmount());

        $expenseAccounts = ['6570', '6540', '7010', '7510'];
        foreach ($expenseAccounts as $accountNumber) {
            $lineItem = $this->findLineItemForAccount($closingEntry, $accountNumber);
            $this->assertNotNull($lineItem, "Account $accountNumber should be in closing entry");
            $this->assertGreaterThan(0, $lineItem->getCreditAmount());
        }

        $retainedEarnings = $this->findLineItemForAccount($closingEntry, '2099');
        $this->assertNotNull($retainedEarnings);
        $this->assertEquals(1614, $retainedEarnings->getDebitAmount());
    }

    /**
     * Tests year-end closure with Scenario B (Larger Net LOSS)
     *
     * Financial Summary:
     * - Revenue: 5,000 SEK (account 3041)
     * - Expenses: 14,742 SEK (accounts 6570, 6540, 7010, 7510)
     * - Net Result: -9,742 SEK (LOSS)
     *
     * Expected Closing Entry:
     * - Debit 3041 (Revenue): 5,000 SEK
     * - Credit expenses (total): 14,742 SEK
     * - Debit 2099 (Årets resultat): 9,742 SEK (larger loss)
     */
    public function testClosingEntryWithLargerLossScenarioB(): void
    {
        $journal = $this->fixtureLoader->loadScenario('B');

        $closingEntry = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31')
        );

        $retainedEarnings = $this->findLineItemForAccount($closingEntry, '2099');
        $this->assertNotNull($retainedEarnings, "Retained earnings is null");
        $this->assertEquals(9742, $retainedEarnings->getDebitAmount());
        $this->assertEquals(0, $retainedEarnings->getCreditAmount());
    }

    /**
     * Tests year-end closure with Scenario E (Profit)
     *
     * Financial Summary:
     * - Revenue: 50,000 SEK (account 3041)
     * - Expenses: 30,313 SEK (accounts 5010, 6100, 6540, 6570, 7010, 7510)
     * - Net Result: +19,687 SEK (PROFIT)
     *
     * Expected Closing Entry:
     * - Debit 3041 (Revenue): 50,000 SEK (zero out revenue)
     * - Credit 5010 (Lokalhyra): 8,000 SEK (zero out expense)
     * - Credit 6100 (Kontorsmaterial): 500 SEK (zero out expense)
     * - Credit 6540 (Konsulttjänster): 2,000 SEK (zero out expense)
     * - Credit 6570 (Bankavgifter): 100 SEK (zero out expense)
     * - Credit 7010 (Löner): 15,000 SEK (zero out expense)
     * - Credit 7510 (Arbetsgivaravgifter): 4,713 SEK (zero out expense)
     * - Credit 2099 (Årets resultat): 19,687 SEK (profit increases equity)
     *
     * Total Debits: 50,000 SEK, Total Credits: 50,000 SEK
     *
     */
    public function testClosingEntryWithProfitScenarioE(): void
    {
        $journal = $this->fixtureLoader->loadScenario('E');

        $closingEntry = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31'),
            'Årsbokslut 2025 - Positivt resultat'
        );

        $this->assertInstanceOf(JournalEntry::class, $closingEntry);
        $this->assertTrue($closingEntry->isClosingEntry());
        $this->assertEquals('Årsbokslut 2025 - Positivt resultat', $closingEntry->getTitle());

        // Verify revenue account is debited to zero it out
        $revenueLineItem = $this->findLineItemForAccount($closingEntry, '3041');
        $this->assertNotNull($revenueLineItem);
        $this->assertEquals(50000, $revenueLineItem->getDebitAmount());
        $this->assertEquals(0, $revenueLineItem->getCreditAmount());

        // Verify expense accounts are credited to zero them out
        $expenseAccounts = [
            '5010' => 8000,
            '6100' => 500,
            '6540' => 2000,
            '6570' => 100,
            '7010' => 15000,
            '7510' => 4713
        ];
        foreach ($expenseAccounts as $accountNumber => $expectedAmount) {
            $lineItem = $this->findLineItemForAccount($closingEntry, $accountNumber);
            $this->assertNotNull($lineItem, "Account $accountNumber should be in closing entry");
            $this->assertEquals(0, $lineItem->getDebitAmount());
            $this->assertEquals($expectedAmount, $lineItem->getCreditAmount());
        }

        $retainedEarnings = $this->findLineItemForAccount($closingEntry, '2099');
        $this->assertNotNull($retainedEarnings, 'Retained earnings should be in closing entry');
        $this->assertEquals(0, $retainedEarnings->getDebitAmount(), 'Profit should not be debited');
        $this->assertEquals(19687, $retainedEarnings->getCreditAmount(), 'Profit should be credited to equity');
    }

    /**
     * Tests year-end closure with Scenario C (Expenses Only - No Revenue)
     *
     * Financial Summary:
     * - Revenue: 0 SEK (no revenue accounts)
     * - Expenses: 6,300 SEK (accounts 5010, 5060, 6100)
     * - Net Result: -6,300 SEK (LOSS)
     *
     * Expected Closing Entry:
     * - No revenue accounts to close
     * - Credit 5010 (Lokalhyra): 5,000 SEK
     * - Credit 5060 (El): 800 SEK
     * - Credit 6100 (Kontorsmaterial): 500 SEK
     * - Debit 2099 (Årets resultat): 6,300 SEK
     *
     * Special Case: Tests scenario where company has NO revenue (startup phase).
     */
    public function testClosingEntryWithOnlyExpenses(): void
    {
        $journal = $this->fixtureLoader->loadScenario('C');

        $closingEntry = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31')
        );

        $this->assertNull($this->findLineItemForAccount($closingEntry, '3041'));

        $expenseTotal = 0;
        foreach ($closingEntry->getJournalLineItems() as $item) {
            if ($item->getAccount()->getType() === Account::TYPE_EXPENSE) {
                $expenseTotal += $item->getCreditAmount();
            }
        }
        $this->assertEquals(6300, $expenseTotal);
    }

    /**
     * Tests that closing date cannot be null
     *
     * YearEndService requires a valid closing date for the entry.
     */
    public function testClosingDateCannotBeNull(): void
    {
        $journal = $this->fixtureLoader->loadScenario('A');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Closing date cannot be null');

        $this->service->closeIncomeStatement($journal, null);
    }

    /**
     * Tests that system throws exception when account 2099 (Årets resultat) is missing
     *
     * The YearEndService is hard-coded to use account 2099 for retained earnings.
     * If this account doesn't exist in the chart of accounts, it should throw LogicException.
     *
     * This test verifies proper scoping: account 2099 must belong to THIS journal's
     * chart of accounts, not just any 2099 in the database.
     */
    public function testWithoutRetainedEarningsAccount(): void
    {
        $journal = $this->fixtureLoader->loadScenario('A');
        $this->fixtureLoader->deleteAccount($journal->getChartOfAccounts(), '2099');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('finns inte i din kontoplan');

        $this->service->closeIncomeStatement($journal, new \DateTime('2025-12-31'));
    }

    /**
     * Tests that multiple year-end closures don't double-count amounts
     *
     * Scenario:
     * 1. First closure zeros out revenue/expense accounts and transfers to 2099
     * 2. Second closure should see accounts already at zero (includes first closing entry)
     * 3. Second closure should NOT create duplicate line items for already-closed accounts
     *
     * This tests that getIncomeStatementWithClosingEntries() properly includes
     * previous closing entries in its calculations.
     */
    public function testMultipleClosuresDoNotDoubleCount(): void
    {
        $journal = $this->fixtureLoader->loadScenario('A');

        $closure1 = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31')
        );
        $this->em->persist($closure1);
        $this->em->flush();

        $journalId = $journal->getId();
        $this->em->clear();
        $journal = $this->em->getRepository(Journal::class)->find($journalId);

        $closure2 = $this->service->closeIncomeStatement(
            $journal,
            new \DateTime('2025-12-31'),
            'Rättning årsbokslut'
        );

        $revenue = $this->findLineItemForAccount($closure2, '3041');
        $this->assertNull($revenue, 'Revenue already closed, should not appear again');
    }

    private function findLineItemForAccount(JournalEntry $entry, string $accountNumber): ?JournalLineItem
    {
        foreach ($entry->getJournalLineItems() as $item) {
            if ($item->getAccount()->getAccountNumber() === $accountNumber) {
                return $item;
            }
        }
        return null;
    }
}
