<?php

namespace App\Tests\Entity;

use App\Entity\Organization;
use App\Entity\User;
use App\Entity\Journal;
use App\Entity\ChartOfAccounts;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    public function testConstructorInitializesCollections(): void
    {
        $organization = new Organization();

        $this->assertCount(0, $organization->getUsers());
        $this->assertCount(0, $organization->getJournals());
        $this->assertCount(0, $organization->getChartOfAccounts());
    }

    public function testBasicGettersAndSetters(): void
    {
        $organization = new Organization();
        $name = "abra kadabra";

        $this->assertNull($organization->getId());
        $organization->setName($name);

        $this->assertEquals($name, $organization->getName());
    }

    public function testAddChartOfAccount(): void
    {
        $organization = new Organization();
        $chartOfAccounts = $this->createMock(ChartOfAccounts::class);

        $chartOfAccounts->expects($this->once())
            ->method('setOrganization')
            ->with($organization);

        $organization->addChartOfAccount($chartOfAccounts);

        $this->assertCount(1, $organization->getChartOfAccounts());
        $this->assertContains($chartOfAccounts, $organization->getChartOfAccounts());
    }

    public function testRemoveChartOfAccount(): void
    {
        $organization = new Organization();
        $chartOfAccounts = $this->createMock(ChartOfAccounts::class);

        $chartOfAccounts->method('setOrganization')
            ->willReturnSelf();

        $chartOfAccounts->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $organization->addChartOfAccount($chartOfAccounts);
        $this->assertCount(1, $organization->getChartOfAccounts());

        $organization->removeChartOfAccount($chartOfAccounts);

        $this->assertCount(0, $organization->getChartOfAccounts());
        $this->assertNotContains($chartOfAccounts, $organization->getChartOfAccounts());
    }

    public function testAddUserFunctions(): void
    {
        $organization = new Organization();
        $user = $this->createMock(User::class);

        $organization->addUser($user);
        $this->assertTrue($organization->isUserInOrganization($user));
    }

    public function testRemoveUserFunctions(): void
    {
        $organization = new Organization();
        $user = $this->createMock(User::class);

        $organization->addUser($user);
        $this->assertTrue($organization->isUserInOrganization($user));

        $organization->removeUser($user);
        $this->assertFalse($organization->isUserInOrganization($user));
    }

    public function testAddJournal(): void
    {
        $organization = new Organization();
        $journal = $this->createMock(Journal::class);

        $organization->addJournal($journal);
        $this->assertContains($journal, $organization->getJournals());
    }

    public function testRemoveJournal(): void
    {
        $organization = new Organization();
        $journal = $this->createMock(Journal::class);

        $organization->addJournal($journal);
        $this->assertContains($journal, $organization->getJournals());
        $organization->removeJournal($journal);
        $this->assertNotContains($journal, $organization->getJournals());
    }
}
