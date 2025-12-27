<?php

namespace App\Tests\Entity;

use App\Entity\Journal;
use App\Entity\JournalEntry;
use App\Entity\JournalLineItem;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;

class JournalEntryTest extends TestCase
{
    public function testConstructorInitializesCollections(): void
    {
        $journalEntry = new JournalEntry();

        $this->assertCount(0, $journalEntry->getJournalLineItems());
        $this->assertNull($journalEntry->getId());
    }

    public function testBasicGettersAndSetters(): void
    {
        $journalEntry = new journalEntry();

        $date = new DateTimeImmutable("now");
        $amount = 10;
        $title = "Hej";

        $journalEntry->setTitle($title);
        $journalEntry->setAmount($amount);
        $journalEntry->setDate($date);

        $this->assertEquals($date, $journalEntry->getDate());
        $this->assertEquals($title, $journalEntry->getTitle());
        $this->assertEquals($amount, $journalEntry->getAmount());
    }

    public function testSetJournal(): void
    {
        $journalEntry = new journalEntry();
        $journal = $this->createMock(Journal::class);


        $journalEntry->setJournal($journal);
        $this->assertEquals($journal, $journalEntry->getJournal());
    }

    public function testAddJournalLineItem(): void
    {
        $journalEntry = new journalEntry();
        $journalLineItem = $this->createMock(JournalLineItem::class);
        $journalLineItem->expects($this->any())
            ->method('getJournalEntry')
            ->willReturn($journalEntry);

        $journalEntry->addJournalLineItem($journalLineItem);
        $this->assertContains($journalLineItem, $journalEntry->getJournalLineItems());
    }

    public function testAddJournalLineItems(): void
    {
        $journalEntry = new journalEntry();
        $journalLineItem1 = $this->createMock(JournalLineItem::class);
        $journalLineItem1->expects($this->any())
            ->method('getJournalEntry')
            ->willReturn($journalEntry);
        $journalLineItem2 = $this->createMock(JournalLineItem::class);
        $journalLineItems = new ArrayCollection();

        $journalLineItems->add($journalLineItem1);
        $journalLineItems->add($journalLineItem2);

        //twice to cover the case to remove existing items first...
        $journalEntry->setJournalLineItems($journalLineItems);
        $journalEntry->setJournalLineItems($journalLineItems);

        $this->assertCount(2, $journalEntry->getJournalLineItems());
        $this->assertContains($journalLineItem1, $journalEntry->getJournalLineItems());
        $this->assertContains($journalLineItem2, $journalEntry->getJournalLineItems());

        $journalEntry->removeJournalLineItem($journalLineItem1);
        $this->assertCount(1, $journalEntry->getJournalLineItems());
        $this->assertNotContains($journalLineItem1, $journalEntry->getJournalLineItems());
    }
}
