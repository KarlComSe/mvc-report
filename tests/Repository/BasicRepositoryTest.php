<?php

namespace App\Tests\Repository;

use App\Entity\ChartOfAccounts;
use App\Entity\Currency;
use App\Entity\JournalLineItem;
use App\Entity\User;
use App\Repository\ChartOfAccountsRepository;
use App\Repository\CurrencyRepository;
use App\Repository\JournalLineItemRepository;
use App\Repository\UserRepository;
use App\Tests\Controller\Kassabok\KassabokWebTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/** @SuppressWarnings(PHPMD.LongVariable) */
class BasicRepositoryTest extends KassabokWebTestCase
{
    private CurrencyRepository $currencyRepository;
    private JournalLineItemRepository $journalLineItemRepository;
    private ChartOfAccountsRepository $chartOfAccountsRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CurrencyRepository $currencyRepository */
        $currencyRepository = $this->entityManager->getRepository(Currency::class);
        $this->currencyRepository = $currencyRepository;
        /** @var JournalLineItemRepository $journalLineItemRepository */
        $journalLineItemRepository = $this->entityManager->getRepository(JournalLineItem::class);
        $this->journalLineItemRepository = $journalLineItemRepository;
        /** @var ChartOfAccountsRepository $chartOfAccountsRepository */
        $chartOfAccountsRepository = $this->entityManager->getRepository(ChartOfAccounts::class);
        $this->chartOfAccountsRepository = $chartOfAccountsRepository;
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        $this->userRepository = $userRepository;
    }

    public function testRepositoryReturnsCorrectEntity(): void
    {
        $this->assertSame(Currency::class, $this->currencyRepository->getClassName());
        $this->assertSame(JournalLineItem::class, $this->journalLineItemRepository->getClassName());
        $this->assertSame(ChartOfAccounts::class, $this->chartOfAccountsRepository->getClassName());
    }

    public function testFindStandardCharts(): void
    {
        $result = $this->chartOfAccountsRepository->findStandardCharts();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testFindByVersion(): void
    {
        $result = $this->chartOfAccountsRepository->findByVersion('2025');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testUpgradePasswordUpdatesUserPassword(): void
    {
        $user = $this->createUser();
        $this->userRepository->upgradePassword($user, 'new_hashed_password');
        $this->entityManager->clear();
        $updatedUser = $this->userRepository->find($user->getId());
        $this->assertSame('new_hashed_password', $updatedUser->getPassword());
    }
    public function testUpgradePasswordThrowsExceptionForNonUserInstance(): void
    {
        $nonUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        
        $this->expectException(UnsupportedUserException::class);
        
        $this->userRepository->upgradePassword($nonUser, 'some_password');
    }
}
