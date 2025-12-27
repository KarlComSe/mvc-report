<?php

namespace App\Tests\Entity;

use App\Entity\Organization;
use App\Entity\ChartOfAccounts;
use App\Entity\Journal;
use App\Entity\JournalEntry;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class JournalTest extends TestCase
{
    public function testConstructorInitializesCollections(): void
    {
        $journal = new Journal();

        $this->assertCount(0, $journal->getJournalEntries());
        $this->assertNull($journal->getId());
    }

    public function testBasicGettersAndSetters(): void
    {
        $journal = new Journal();
        $firstDay = new DateTimeImmutable("now");
        $lastDay = $firstDay->modify("+1 day");

        $string = sprintf(
            'Journal %d (%s - %s)',
            null,
            $firstDay->format('Y-m-d'),
            $lastDay->format('Y-m-d')
        );

        $journal->setFirstDay($firstDay);
        $journal->setLastDay($lastDay);

        $this->assertEquals($firstDay, $journal->getFirstDay());
        $this->assertEquals($lastDay, $journal->getLastDay());
        $this->assertEquals($string, $journal->__toString());
    }

    public function testAddChartOfAccount(): void
    {
        $journal = new Journal();
        $chartOfAccounts = $this->createMock(ChartOfAccounts::class);

        $journal->setChartOfAccounts($chartOfAccounts);

        $this->assertEquals($chartOfAccounts, $journal->getChartOfAccounts());
    }

    public function testAddJournalEntry(): void
    {
        $journal = new Journal();
        $journalEntry = $this->createMock(JournalEntry::class);


        $journal->addJournalEntry($journalEntry);
        $this->assertCount(1, $journal->getJournalEntries());
        $this->assertContains($journalEntry, $journal->getJournalEntries());
    }

    public function testRemoveJournalEntry(): void
    {
        $journal = new Journal();
        $journalEntry = $this->createMock(JournalEntry::class);
        $journalEntry->expects($this->once())
            ->method('getJournal')
            ->willReturn($journal);

        $journal->addJournalEntry($journalEntry);
        $this->assertCount(1, $journal->getJournalEntries());
        $this->assertContains($journalEntry, $journal->getJournalEntries());

        $journal->removeJournalEntry($journalEntry);
        $this->assertCount(0, $journal->getJournalEntries());
        $this->assertNotContains($journalEntry, $journal->getJournalEntries());
    }

    public function testSetOrganization(): void
    {
        $journal = new Journal();
        $organization = $this->createMock(Organization::class);

        $journal->setOrganization($organization);
        $this->assertEquals($organization, $journal->getOrganization());
    }
}
