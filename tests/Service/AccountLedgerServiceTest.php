<?php

namespace App\Tests\Service;

use App\Entity\Account;
use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use App\Service\AccountLedgerService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountLedgerServiceTest extends TestCase
{
    private AccountLedgerService $service;

    protected function setUp(): void
    {
        $this->service = new AccountLedgerService();
    }

    public function testCompleteAccountLedger(): void
    {
        $journal = $this->createJournalWithEntries();

        $result = $this->service->getCompleteAccountLedger($journal);

        // Should have 3 accounts (1910, 3000, 6000)
        $this->assertCount(4, $result);

        // Should be sorted by account number
        $this->assertEquals('1910', $result[0]['accountNumber']);
        $this->assertEquals('3000', $result[1]['accountNumber']);
        $this->assertEquals('6000', $result[2]['accountNumber']);

        // Cash account (1910) - 3 entries
        $cashAccount = $result[0];
        $this->assertEquals('Kassa', $cashAccount['accountName']);
        $this->assertEquals('Tillgångar', $cashAccount['typeName']);
        $this->assertCount(4, $cashAccount['entries']);
        $this->assertEquals(500, $cashAccount['summary']['totalDebit']);
        $this->assertEquals(1250, $cashAccount['summary']['totalCredit']);
        $this->assertEquals(-750, $cashAccount['summary']['finalBalance']);

        // Verify date sorting and running balance for cash account
        $cashEntries = $cashAccount['entries'];

        // First: Jan 10, ID 2
        $this->assertEquals('2024-01-10', $cashEntries[0]['date']->format('Y-m-d'));
        $this->assertEquals(2, $cashEntries[0]['verificationNumber']);
        $this->assertEquals('Kontorsmaterial', $cashEntries[0]['description']);
        $this->assertEquals(0, $cashEntries[0]['debit']);
        $this->assertEquals(200, $cashEntries[0]['credit']);
        $this->assertEquals(-200, $cashEntries[0]['balance']);

        // Second: Jan 10, ID 3 (same date, higher ID)
        $this->assertEquals('2024-01-10', $cashEntries[1]['date']->format('Y-m-d'));
        $this->assertEquals(3, $cashEntries[1]['verificationNumber']);
        $this->assertEquals('Fika', $cashEntries[1]['description']);
        $this->assertEquals(-250, $cashEntries[1]['balance']);
        $this->assertEquals(50, $cashEntries[1]['credit']);

        // Third: Jan 15, ID 1
        $this->assertEquals('2024-01-15', $cashEntries[2]['date']->format('Y-m-d'));
        $this->assertEquals(1, $cashEntries[2]['verificationNumber']);
        $this->assertEquals(250, $cashEntries[2]['balance']);

        $this->assertEquals('2024-01-20', $cashEntries[3]['date']->format('Y-m-d'));
        $this->assertEquals(4, $cashEntries[3]['verificationNumber']);
        $this->assertEquals(-750, $cashEntries[3]['balance']);

        // Revenue account (3000) - 1 entry
        $revenueAccount = $result[1];
        $this->assertEquals('Försäljning', $revenueAccount['accountName']);
        $this->assertCount(1, $revenueAccount['entries']);
        $this->assertEquals(0, $revenueAccount['summary']['totalDebit']);
        $this->assertEquals(500, $revenueAccount['summary']['totalCredit']);
        $this->assertEquals(-500, $revenueAccount['summary']['finalBalance']);

        // Expense account (6000) - 2 entries on same date
        $expenseAccount = $result[2];
        $this->assertEquals('Övriga kostnader', $expenseAccount['accountName']);
        $this->assertCount(2, $expenseAccount['entries']);
        $this->assertEquals(250, $expenseAccount['summary']['totalDebit']);
        $this->assertEquals(0, $expenseAccount['summary']['totalCredit']);
        $this->assertEquals(250, $expenseAccount['summary']['finalBalance']);

        // Verify ID tiebreaker sorting for expense account
        $expenseEntries = $expenseAccount['entries'];
        $this->assertEquals(2, $expenseEntries[0]['verificationNumber']); // ID 2 first
        $this->assertEquals(3, $expenseEntries[1]['verificationNumber']); // ID 3 second
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
