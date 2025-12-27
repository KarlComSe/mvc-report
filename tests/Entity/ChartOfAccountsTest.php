<?php

namespace App\Tests\Entity;

use App\Entity\Account;
use App\Entity\ChartOfAccounts;
use App\Entity\Journal;
use App\Entity\Organization;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ChartOfAccountsTest extends TestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $chart = new ChartOfAccounts();

        $chart->setName('BAS 2024');
        $chart->setBasVersion('2024');
        $chart->setIsStandard(true);

        $this->assertEquals('BAS 2024', $chart->getName());
        $this->assertEquals('2024', $chart->getBasVersion());
        $this->assertTrue($chart->isStandard());
    }

    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $chart = new ChartOfAccounts();
        $this->assertNull($chart->getId());
    }

    public function testConstructorInitializesCollectionsAndCreatedAt(): void
    {
        $chart = new ChartOfAccounts();

        $this->assertCount(0, $chart->getAccounts());
        $this->assertCount(0, $chart->getJournals());
        $this->assertCount(0, $chart->getCustomVersions());
        $this->assertInstanceOf(\DateTimeImmutable::class, $chart->getCreatedAt());
    }

    public function testOrganizationRelationship(): void
    {
        $chart = new ChartOfAccounts();
        $organization = $this->createMock(Organization::class);

        $chart->setOrganization($organization);

        $this->assertSame($organization, $chart->getOrganization());
    }

    public function testBasedOnRelationship(): void
    {
        $standardChart = new ChartOfAccounts();
        $customChart = new ChartOfAccounts();

        $customChart->setBasedOn($standardChart);

        $this->assertSame($standardChart, $customChart->getBasedOn());
    }

    public function testAddAccount(): void
    {
        $chart = new ChartOfAccounts();
        $account = new Account();

        $chart->addAccount($account);

        $this->assertCount(1, $chart->getAccounts());
        $this->assertTrue($chart->getAccounts()->contains($account));
        $this->assertSame($chart, $account->getChartOfAccounts());
    }

    public function testRemoveAccount(): void
    {
        $chart = new ChartOfAccounts();
        $account = new Account();

        $chart->addAccount($account);
        $this->assertCount(1, $chart->getAccounts());

        $chart->removeAccount($account);

        $this->assertCount(0, $chart->getAccounts());
        $this->assertNull($account->getChartOfAccounts());
    }

    public function testAddJournal(): void
    {
        $chart = new ChartOfAccounts();
        $journal = $this->createMock(Journal::class);

        $journal->expects($this->once())
            ->method('setChartOfAccounts')
            ->with($chart);

        $chart->addJournal($journal);

        $this->assertCount(1, $chart->getJournals());
        $this->assertTrue($chart->getJournals()->contains($journal));
    }

    public function testAddJournalDoesNotDuplicateExistingJournal(): void
    {
        $chart = new ChartOfAccounts();
        $journal = $this->createMock(Journal::class);

        $journal->expects($this->once())
            ->method('setChartOfAccounts')
            ->with($chart);

        $chart->addJournal($journal);
        $chart->addJournal($journal);

        $this->assertCount(1, $chart->getJournals());
    }

    public function testRemoveJournal(): void
    {
        $chart = new ChartOfAccounts();
        $journal = $this->createMock(Journal::class);

        $journal->method('setChartOfAccounts');
        $journal->method('getChartOfAccounts')->willReturn($chart);

        $chart->addJournal($journal);
        $this->assertCount(1, $chart->getJournals());

        $chart->removeJournal($journal);

        $this->assertCount(0, $chart->getJournals());
    }

    public function testToStringForStandardChart(): void
    {
        $chart = new ChartOfAccounts();
        $chart->setName('BAS 2024');
        $chart->setIsStandard(true);

        $this->assertEquals('BAS 2024 (Standard)', (string)$chart);
    }

    public function testGetDisplayNameWithoutOrganization(): void
    {
        $chart = new ChartOfAccounts();
        $chart->setName('BAS 2024');

        $this->assertEquals('BAS 2024', $chart->getDisplayName());
    }

    public function testGetDisplayNameWithOrganization(): void
    {
        $chart = new ChartOfAccounts();
        $chart->setName('Anpassad kontoplan');

        $organization = $this->createMock(Organization::class);
        $organization->expects($this->once())
            ->method('getName')
            ->willReturn('Mitt AB');

        $chart->setOrganization($organization);

        $this->assertEquals('Anpassad kontoplan - Mitt AB', $chart->getDisplayName());
    }
}
