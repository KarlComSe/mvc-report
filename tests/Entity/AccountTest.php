<?php

namespace App\Tests\Entity;

use App\Entity\Account;
use PHPUnit\Framework\TestCase;
use App\Entity\ChartOfAccounts;

class AccountTest extends TestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $account = new Account();

        $account->setAccountNumber('1920');
        $account->setName('Bank');
        $account->setDescription('Main bank account');
        $account->setIsStandard(false);
        $account->setIsDetailAccount(true);
        $account->setParentAccountNumber('192');
        $account->setType('asset');
        $account->setHierarchyLevel(4);

        $this->assertEquals('1920', $account->getAccountNumber());
        $this->assertEquals('Bank', $account->getName());
        $this->assertEquals('Main bank account', $account->getDescription());
        $this->assertFalse($account->isStandard());
        $this->assertTrue($account->isDetailAccount());
        $this->assertTrue($account->canHaveTransactions());
        $this->assertEquals('192', $account->getParentAccountNumber());
        $this->assertEquals(4, $account->getHierarchyLevel());
        $this->assertEquals(str_repeat('  ', 3) . "1920 - Bank", $account->getIndentedName());
        $this->assertEquals('asset', $account->getType());
        $this->assertEquals('Tillgångar', $account->getTypeName());
        $this->assertEquals('1920 - Bank', $account);
        $this->expectException(\InvalidArgumentException::class);
        $account->setType('Tillgångar');
        $this->assertEquals('Tillgångar', $account->getTypeName());
    }


    public function testGetIdReturnsNullBeforePersistence(): void
    {
        $account = new Account();
        $this->assertNull($account->getId());
    }

    public function testChartOfAccountsRelationship(): void
    {
        $account = new Account();

        $chartOfAccounts = $this->createMock(ChartOfAccounts::class);

        $account->setChartOfAccounts($chartOfAccounts);

        $this->assertSame($chartOfAccounts, $account->getChartOfAccounts());
    }

    public function testSetTypeWithInvalidTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $account = new Account();
        $account->setType('invalid_type');
    }
}
