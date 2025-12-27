<?php

namespace App\Tests\Controller\Kassabok;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Organization;
use App\Entity\Journal;
use App\Entity\ChartOfAccounts;
use App\Entity\Account;
use DateTimeImmutable;

abstract class KassabokWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;
    /** @var array<object> */
    protected array $entitiesToCleanup = [];

    protected const STANDARD_ACCOUNTS = [
        '1510' => ['name' => 'Kundfordringar', 'type' => Account::TYPE_ASSET],
        '1930' => ['name' => 'Företagskonto/checkkonto', 'type' => Account::TYPE_ASSET],
        '2081' => ['name' => 'Aktiekapital', 'type' => Account::TYPE_EQUITY],
        '2099' => ['name' => 'Årets resultat', 'type' => Account::TYPE_EQUITY],
        '2611' => ['name' => 'Utgående moms, 25%', 'type' => Account::TYPE_LIABILITY],
        '2641' => ['name' => 'Ingående moms', 'type' => Account::TYPE_ASSET],
        '2650' => ['name' => 'Redovisningskonto för moms', 'type' => Account::TYPE_LIABILITY],
        '2710' => ['name' => 'Personalens källskatt', 'type' => Account::TYPE_LIABILITY],
        '2731' => ['name' => 'Avräkning arbetsgivaravgifter', 'type' => Account::TYPE_LIABILITY],
        '3041' => ['name' => 'Försäljning tjänster, 25% moms', 'type' => Account::TYPE_REVENUE],
        '5010' => ['name' => 'Lokalhyra', 'type' => Account::TYPE_EXPENSE],
        '5060' => ['name' => 'El för belysning', 'type' => Account::TYPE_EXPENSE],
        '6100' => ['name' => 'Kontorsmaterial och blanketter', 'type' => Account::TYPE_EXPENSE],
        '6230' => ['name' => 'Telefon', 'type' => Account::TYPE_EXPENSE],
        '6420' => ['name' => 'Representation, avdragsgill', 'type' => Account::TYPE_EXPENSE],
        '6540' => ['name' => 'Konsulttjänster och företagsledning', 'type' => Account::TYPE_EXPENSE],
        '6570' => ['name' => 'Bankavgifter', 'type' => Account::TYPE_EXPENSE],
        '7010' => ['name' => 'Löner till tjänstemän', 'type' => Account::TYPE_EXPENSE],
        '7510' => ['name' => 'Arbetsgivaravgifter', 'type' => Account::TYPE_EXPENSE],
        '8999' => ['name' => 'Årets resultat', 'type' => Account::TYPE_EXPENSE],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        /** @var \Doctrine\Bundle\DoctrineBundle\Registry $doctrine */
        $doctrine = static::getContainer()->get('doctrine');
        $em = $doctrine->getManager();
        assert($em instanceof EntityManagerInterface);
        $this->entityManager = $em;
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->entitiesToCleanup) as $entity) {
            if ($this->entityManager->contains($entity)) {
                $this->entityManager->remove($entity);
            }
        }

        if (!empty($this->entitiesToCleanup)) {
            $this->entityManager->flush();
        }

        $this->entityManager->close();
        parent::tearDown();
    }

    /**
     * @return array<string, mixed>
     */
    protected function createAuthenticatedScenario(): array
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $chart = $this->createChartOfAccounts();
        $accounts = $this->createStandardAccounts($chart);
        $journal = $this->createJournal($org, $chart);

        return compact('user', 'org', 'journal', 'chart', 'accounts');
    }


    protected function assertPageRequiresAuth(string $url): void
    {
        $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        $this->assertTrue(
            $response->isRedirect(),
            "Page $url should redirect (require authentication)"
        );

        $this->assertStringContainsString(
            '/proj/login',
            $response->headers->get('Location'),
            "Page $url should redirect to login"
        );
    }
    protected function assertPageAccessibleWithoutAuth(string $url): void
    {
        $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful(
            "Page $url should be accessible without authentication"
        );
    }

    protected function assertPageAccessibleWhenAuthenticated(string $url, User $user, string $expectedContent): void
    {
        $this->loginUser($user);
        $this->client->request('GET', $url);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', $expectedContent);
    }

    protected function createUser(?string $email = null): User
    {
        $user = new User();
        $user->setEmail($email ?? 'test_' . uniqid() . '@test.com');
        $user->setPassword('hashed_password');

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entitiesToCleanup[] = $user;

        return $user;
    }

    protected function createOrganization(?string $name = null, ?User $owner = null): Organization
    {
        $org = new Organization();
        $org->setName($name ?? 'Org_' . uniqid());

        if ($owner) {
            $org->addUser($owner);
        }

        $this->entityManager->persist($org);
        $this->entityManager->flush();
        $this->entitiesToCleanup[] = $org;

        return $org;
    }

    protected function createChartOfAccounts(): ChartOfAccounts
    {
        $chart = new ChartOfAccounts();
        $chart->setName('BAS 2025');
        $chart->setIsStandard(true);
        $chart->setBasVersion('2025');

        $this->entityManager->persist($chart);
        $this->entityManager->flush();
        $this->entitiesToCleanup[] = $chart;

        return $chart;
    }

    protected function createJournalWithDates(
        Organization $org,
        ?DateTimeImmutable $firstDay,
        ?DateTimeImmutable $lastDay,
        ?ChartOfAccounts $chart = null
    ): Journal {
        if (!$chart) {
            $chart = $this->createChartOfAccounts();
        }

        $journal = new Journal();
        $journal->setChartOfAccounts($chart);

        if ($firstDay !== null) {
            $journal->setFirstDay($firstDay);
        }

        if ($lastDay !== null) {
            $journal->setLastDay($lastDay);
        }

        $journal->setOrganization($org);

        $this->entityManager->persist($journal);
        $this->entityManager->flush();
        $this->entitiesToCleanup[] = $journal;

        return $journal;
    }

    protected function createJournal(Organization $org, ?ChartOfAccounts $chart = null): Journal
    {
        return $this->createJournalWithDates(
            $org,
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31'),
            $chart
        );
    }

    protected function loginUser(User $user): void
    {
        $this->client->loginUser($user);
    }

    /**
     * @param array<int|string, array<string, mixed>> $accountsToCreate
     * @return array<string, Account>
     */
    protected function createTestAccounts(ChartOfAccounts $chart, array $accountsToCreate): array
    {
        $accounts = [];

        foreach ($accountsToCreate as $number => $data) {
            $account = new Account();
            $account->setAccountNumber((string)$number);
            $account->setName($data['name']);
            $account->setType($data['type']);
            $account->setChartOfAccounts($chart);

            $this->entityManager->persist($account);
            $accounts[(string)$number] = $account;
            $this->entitiesToCleanup[] = $account;
        }

        $this->entityManager->flush();
        return $accounts;
    }

    /**
     * @return array<string, Account>
     */
    protected function createStandardAccounts(ChartOfAccounts $chart): array
    {
        return $this->createTestAccounts($chart, self::STANDARD_ACCOUNTS);
    }
}
