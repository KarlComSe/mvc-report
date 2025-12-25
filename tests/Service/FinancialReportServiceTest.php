<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use App\Service\FinancialReportService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use App\Tests\Fixtures\AccountingFixtureLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class FinancialReportServiceTest extends KernelTestCase
{
    private FinancialReportService $service;
    private EntityManagerInterface $em;
    private AccountingFixtureLoader $fixtureLoader;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();

        $this->service = new FinancialReportService();
        $this->fixtureLoader = new AccountingFixtureLoader($this->em);
    }

    /**
     * Tests incomestatement with with Scenario A (Net LOSS)
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
    public function testIncomeStatementCalculatesNetIncome(): void
    {
        $journal = $this->fixtureLoader->loadScenario('A');
        $result = $this->service->getIncomeStatement($journal);

        $this->assertArrayHasKey('revenues', $result);
        $this->assertArrayHasKey('expenses', $result);
        $this->assertArrayHasKey('totalRevenue', $result);
        $this->assertArrayHasKey('totalExpenses', $result);
        $this->assertArrayHasKey('netIncome', $result);
        $this->assertEquals(10000, $result['totalRevenue']);
        $this->assertEquals(11614, $result['totalExpenses']);
        $this->assertEquals(-1614, $result['netIncome']);
    }

    public function testBalanceSheet(): void
    {
        $journal = $this->fixtureLoader->loadScenario('E');
        $result = $this->service->getIncomeStatement($journal);

        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('liabilities', $result);
        $this->assertArrayHasKey('equity', $result);
        $this->assertArrayHasKey('totalAssets', $result);
        $this->assertArrayHasKey('totalLiabilities', $result);
        $this->assertArrayHasKey('totalEquity', $result);
        $this->assertArrayHasKey('balanced', $result);
        $this->assertEquals(-750, $result['totalAssets']);
        $this->assertEquals(0, $result['totalEquity']);
        $this->assertFalse($result['balanced']);
    }


    private function createJournalWithEntries(): Journal
    {
        // Accounts
        $cashAccount = $this->createMock(Account::class);
        $cashAccount->method('getId')->willReturn(1);
        $cashAccount->method('getType')->willReturn(Account::TYPE_ASSET);
        $cashAccount->method('getAccountNumber')->willReturn('1910');
        $cashAccount->method('getName')->willReturn('Kassa');
        $cashAccount->method('getTypeName')->willReturn('Tillgångar');

        $revenueAccount = $this->createMock(Account::class);
        $revenueAccount->method('getId')->willReturn(2);
        $revenueAccount->method('getType')->willReturn(Account::TYPE_REVENUE);
        $revenueAccount->method('getAccountNumber')->willReturn('3000');
        $revenueAccount->method('getName')->willReturn('Försäljning');
        $revenueAccount->method('getTypeName')->willReturn('Intäkter');

        $expenseAccount = $this->createMock(Account::class);
        $expenseAccount->method('getId')->willReturn(3);
        $expenseAccount->method('getType')->willReturn(Account::TYPE_EXPENSE);
        $expenseAccount->method('getAccountNumber')->willReturn('6000');
        $expenseAccount->method('getName')->willReturn('Övriga kostnader');
        $expenseAccount->method('getTypeName')->willReturn('Kostnader');

        // Second expense account (higher number, tests sorting within same type)
        $expenseAccount2 = $this->createMock(Account::class);
        $expenseAccount2->method('getId')->willReturn(4);
        $expenseAccount2->method('getType')->willReturn(Account::TYPE_EXPENSE);
        $expenseAccount2->method('getAccountNumber')->willReturn('7000');
        $expenseAccount2->method('getName')->willReturn('Lokalkostnader');
        $expenseAccount2->method('getTypeName')->willReturn('Kostnader');

        // Entry 1: Jan 15 - Sale (cash in, revenue)
        $entry1 = $this->createMock(JournalEntry::class);
        $entry1->method('getDate')->willReturn(new DateTime('2024-01-15'));
        $entry1->method('getId')->willReturn(1);
        $entry1->method('getTitle')->willReturn('Försäljning kontant');

        $lineItem1a = $this->createMock(JournalLineItem::class);
        $lineItem1a->method('getAccount')->willReturn($cashAccount);
        $lineItem1a->method('getDebitAmount')->willReturn(500.0);
        $lineItem1a->method('getCreditAmount')->willReturn(0.0);
        $lineItem1a->method('getJournalEntry')->willReturn($entry1);

        $lineItem1b = $this->createMock(JournalLineItem::class);
        $lineItem1b->method('getAccount')->willReturn($revenueAccount);
        $lineItem1b->method('getDebitAmount')->willReturn(0.0);
        $lineItem1b->method('getCreditAmount')->willReturn(500.0);
        $lineItem1b->method('getJournalEntry')->willReturn($entry1);

        $entry1->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem1a, $lineItem1b]));

        // Entry 2: Jan 10 - Office supplies (tests date sorting)
        $entry2 = $this->createMock(JournalEntry::class);
        $entry2->method('getDate')->willReturn(new DateTime('2024-01-10'));
        $entry2->method('getId')->willReturn(2);
        $entry2->method('getTitle')->willReturn('Kontorsmaterial');

        $lineItem2a = $this->createMock(JournalLineItem::class);
        $lineItem2a->method('getAccount')->willReturn($expenseAccount);
        $lineItem2a->method('getDebitAmount')->willReturn(200.0);
        $lineItem2a->method('getCreditAmount')->willReturn(0.0);
        $lineItem2a->method('getJournalEntry')->willReturn($entry2);

        $lineItem2b = $this->createMock(JournalLineItem::class);
        $lineItem2b->method('getAccount')->willReturn($cashAccount);
        $lineItem2b->method('getDebitAmount')->willReturn(0.0);
        $lineItem2b->method('getCreditAmount')->willReturn(200.0);
        $lineItem2b->method('getJournalEntry')->willReturn($entry2);

        $entry2->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem2a, $lineItem2b]));

        // Entry 3: Jan 10 - Fika (same date, higher ID - tests ID tiebreaker)
        $entry3 = $this->createMock(JournalEntry::class);
        $entry3->method('getDate')->willReturn(new DateTime('2024-01-10'));
        $entry3->method('getId')->willReturn(3);
        $entry3->method('getTitle')->willReturn('Fika');

        $lineItem3a = $this->createMock(JournalLineItem::class);
        $lineItem3a->method('getAccount')->willReturn($expenseAccount);
        $lineItem3a->method('getDebitAmount')->willReturn(50.0);
        $lineItem3a->method('getCreditAmount')->willReturn(0.0);
        $lineItem3a->method('getJournalEntry')->willReturn($entry3);

        $lineItem3b = $this->createMock(JournalLineItem::class);
        $lineItem3b->method('getAccount')->willReturn($cashAccount);
        $lineItem3b->method('getDebitAmount')->willReturn(0.0);
        $lineItem3b->method('getCreditAmount')->willReturn(50.0);
        $lineItem3b->method('getJournalEntry')->willReturn($entry3);

        $entry3->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem3a, $lineItem3b]));

        // Entry 4: Jan 20 - Rent (second expense account)
        $entry4 = $this->createMock(JournalEntry::class);
        $entry4->method('getDate')->willReturn(new DateTime('2024-01-20'));
        $entry4->method('getId')->willReturn(4);
        $entry4->method('getTitle')->willReturn('Hyra januari');

        $lineItem4a = $this->createMock(JournalLineItem::class);
        $lineItem4a->method('getAccount')->willReturn($expenseAccount2);
        $lineItem4a->method('getDebitAmount')->willReturn(1000.0);
        $lineItem4a->method('getCreditAmount')->willReturn(0.0);
        $lineItem4a->method('getJournalEntry')->willReturn($entry4);

        $lineItem4b = $this->createMock(JournalLineItem::class);
        $lineItem4b->method('getAccount')->willReturn($cashAccount);
        $lineItem4b->method('getDebitAmount')->willReturn(0.0);
        $lineItem4b->method('getCreditAmount')->willReturn(1000.0);
        $lineItem4b->method('getJournalEntry')->willReturn($entry4);

        $entry4->method('getJournalLineItems')->willReturn(new ArrayCollection([$lineItem4a, $lineItem4b]));

        // Journal with entries in non-chronological order
        $journal = $this->createMock(Journal::class);
        $journal->method('getJournalEntries')->willReturn(new ArrayCollection([$entry1, $entry2, $entry3, $entry4]));

        return $journal;
    }
}
