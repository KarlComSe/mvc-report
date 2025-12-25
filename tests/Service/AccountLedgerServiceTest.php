<?php

namespace App\Tests\Service;

use App\Service\AccountLedgerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use App\Tests\Fixtures\AccountingFixtureLoader;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountLedgerServiceTest extends KernelTestCase
{
    private AccountLedgerService $service;
    private EntityManagerInterface $em;
    private AccountingFixtureLoader $fixtureLoader;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get('doctrine')->getManager();

        $this->fixtureLoader = new AccountingFixtureLoader($this->em);
        $this->service = new AccountLedgerService();
    }

    public function testLedgerReturnsAccountsWithEntriesAndSummary(): void
    {
        $journal = $this->fixtureLoader->loadScenario('D');
        $result = $this->service->getCompleteAccountLedger($journal);

        $this->assertIsArray($result);

        foreach ($result as $ledgerDataEntry) {
            $this->assertIsArray($ledgerDataEntry);
            $this->assertArrayHasKey('account', $ledgerDataEntry);
            $this->assertArrayHasKey('accountNumber', $ledgerDataEntry);
            $this->assertArrayHasKey('accountName', $ledgerDataEntry);
            $this->assertArrayHasKey('typeName', $ledgerDataEntry);
            $this->assertArrayHasKey('summary', $ledgerDataEntry);
            $this->assertIsArray($ledgerDataEntry['summary']);
            $this->assertArrayHasKey('totalDebit', $ledgerDataEntry['summary']);
            $this->assertArrayHasKey('totalCredit', $ledgerDataEntry['summary']);
            $this->assertArrayHasKey('finalBalance', $ledgerDataEntry['summary']);
            $this->assertArrayHasKey('entryCount', $ledgerDataEntry['summary']);
        }
    }

    public function testLedgerGroupsLineItemsByAccount(): void
    {
        // Expected accounts: ['1930', '2081', '2641', '2650', '6100', '6230', '6420'],
        $journal = $this->fixtureLoader->loadScenario('D');
        $result = $this->service->getCompleteAccountLedger($journal);

        $accountNumbers = array_column($result, 'accountNumber');

        $uniqueAccounts = array_unique($accountNumbers);
        $duplicates = array_diff_assoc($accountNumbers, $uniqueAccounts);

        $this->assertEmpty(
            $duplicates,
            'Account duplicates found in ledger.'
        );
    }

    public function testLedgerIsSortedByAccount(): void
    {
        $journal = $this->fixtureLoader->loadScenario('B');
        $result = $this->service->getCompleteAccountLedger($journal);

        $accountNumbers = array_column($result, 'accountNumber');
        $sortedAccountNumbers = $accountNumbers;
        sort($sortedAccountNumbers);

        $this->assertSame($accountNumbers, $sortedAccountNumbers);
    }

    public function testLedgerGroupLineItemsIsSortedByDate(): void
    {
        $journal = $this->fixtureLoader->loadScenario('F');
        $result = $this->service->getCompleteAccountLedger($journal);

        foreach ($result as $ledgerDataEntry) {
            $entries = $ledgerDataEntry['entries'];
            for ($i = 0; $i < count($entries) - 1; $i++) {
                $currentDate = $entries[$i]['date'];
                $nextDate = $entries[$i + 1]['date'];
                $this->assertLessThanOrEqual($nextDate, $currentDate);
                // could check on  next stort critera in case of equal dates
            }
        }
    }
}
