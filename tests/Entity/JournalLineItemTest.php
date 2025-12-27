<?php

namespace App\Tests\Entity;

use App\Entity\Account;
use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use PHPUnit\Framework\TestCase;

class JournalLineItemTest extends TestCase
{
    public function testgetJournalEntry(): void
    {
        $journalLineItem = new JournalLineItem();
        $journalEntry = $this->createMock(JournalEntry::class);

        $journalLineItem->setJournalEntry($journalEntry);
        $this->assertEquals($journalEntry, $journalLineItem->getJournalEntry());
    }

    public function testBasicGettersAndSetters(): void
    {
        $journalLineItem = new JournalLineItem();

        $debitAmount = 10;
        $creditAmount = 10;
        $account = $this->createMock(Account::class);

        $journalLineItem->setCreditAmount($creditAmount);
        $journalLineItem->setDebitAmount($debitAmount);
        $journalLineItem->setAccount($account);

        $this->assertEquals($account, $journalLineItem->getAccount());
        $this->assertEquals($creditAmount, $journalLineItem->getCreditAmount());
        $this->assertEquals($debitAmount, $journalLineItem->getDebitAmount());
        $this->assertNull($journalLineItem->getId());
    }
}
