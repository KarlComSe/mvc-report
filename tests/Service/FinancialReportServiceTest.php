<?php

namespace App\Tests\Service;

use App\Service\FinancialReportService;
use App\Service\YearEndService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
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
        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine */
        $doctrine = self::getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        assert($em instanceof EntityManagerInterface);
        $this->em = $em;

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

    public function testBalanceSheetUnbalanced(): void
    {
        $journal = $this->fixtureLoader->loadScenario('E');
        $result = $this->service->getBalanceSheet($journal);

        $this->assertArrayHasKey('assets', $result);
        $this->assertArrayHasKey('liabilities', $result);
        $this->assertArrayHasKey('equity', $result);
        $this->assertArrayHasKey('totalAssets', $result);
        $this->assertArrayHasKey('totalLiabilities', $result);
        $this->assertArrayHasKey('totalEquity', $result);
        $this->assertArrayHasKey('balanced', $result);
        $this->assertEquals(73437, $result['totalAssets']);
        $this->assertEquals(50000, $result['totalEquity']);
        $this->assertFalse($result['balanced']);
    }

    public function testBalanceSheetWithAndWithoutClosingEntries(): void
    {
        $journal = $this->fixtureLoader->loadScenario('E');
        $yearEndService = new YearEndService($this->em, $this->service);
        $journalEntry = $yearEndService->closeIncomeStatement($journal, new DateTime('2025-12-31'));

        $journal->addJournalEntry($journalEntry);

        // PART 1: Without closing entries - should NOT balance
        $result = $this->service->getBalanceSheetWithoutClosingEntries($journal);

        $this->assertArrayHasKey('balanced', $result);
        $this->assertFalse($result['balanced']);

        // PART 2: With closing entries - SHOULD balance
        $result = $this->service->getBalanceSheet($journal);

        $this->assertArrayHasKey('balanced', $result);
        $this->assertEquals(
            $result['totalAssets'],
            $result['totalLiabilities'] + $result['totalEquity']
        );
        $this->assertTrue($result['balanced']);
    }
}
