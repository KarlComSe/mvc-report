<?php

namespace App\Tests\Fixtures;

use App\Entity\{Organization, ChartOfAccounts, Account, Journal, JournalEntry, JournalLineItem, User};
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use DateTime;
use RuntimeException;

class AccountingFixtureLoader
{
    private EntityManagerInterface $em;
    /** @var array<string, mixed> */
    private array $fixtures;
    /** @var array<object> */
    private array $cleanup = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $fixturesPath = __DIR__ . '/../../accounting_fixtures.json';
        $fixturesContent = file_get_contents($fixturesPath);
        if ($fixturesContent === false) {
            throw new RuntimeException("Failed to read fixtures file");
        }
        $this->fixtures = json_decode($fixturesContent, true);
    }

    /**
     * Load a complete accounting scenario with transactions
     *
     * @param string $scenarioId 'A', 'B', 'C', 'D', 'E', 'F'
     * @return Journal A journal with all transactions loaded
     */
    public function loadScenario(string $scenarioId): Journal
    {
        $scenarioData = $this->findScenario($scenarioId);

        // Create organization and user
        $userFactory = new UserFactory($this->em);
        $user = $userFactory->create();
        $this->cleanup[] = $user;

        $org = new Organization();
        $org->setName("Org Scenario {$scenarioId}");
        $org->addUser($user);
        $this->em->persist($org);
        $this->cleanup[] = $org;

        // Create chart of accounts
        $chart = new ChartOfAccounts();
        $chart->setName('BAS 2025');
        $chart->setIsStandard(true);
        $chart->setBasVersion('2025');
        $chart->setOrganization($org);
        $this->em->persist($chart);
        $this->cleanup[] = $chart;

        // Create accounts from fixture account_descriptions
        $accounts = $this->createAccounts($chart);

        // Create journal
        $journal = new Journal();
        $journal->setFirstDay(new DateTime('2025-01-01'));
        $journal->setLastDay(new DateTime('2025-12-31'));
        $journal->setChartOfAccounts($chart);
        $journal->setOrganization($org);
        $this->em->persist($journal);
        $this->cleanup[] = $journal;

        // Create transactions
        foreach ($scenarioData['transactions'] as $transactionData) {
            $entry = $this->createJournalEntry($journal, $transactionData, $accounts);
            $journal->addJournalEntry($entry);
            $this->em->persist($entry);
            $this->cleanup[] = $entry;
        }

        $this->em->flush();

        return $journal;
    }

    /**
     * @return array<string, mixed>
     */
    private function findScenario(string $id): array
    {
        foreach ($this->fixtures['scenarios'] as $scenario) {
            if ($scenario['scenario']['id'] === $id) {
                return $scenario;
            }
        }
        throw new InvalidArgumentException("Scenario '$id' not found");
    }

    /**
     * @return array<string, Account>
     */
    private function createAccounts(ChartOfAccounts $chart): array
    {
        $accounts = [];
        $descriptions = $this->fixtures['account_descriptions'];

        $typeMap = [
            '1' => Account::TYPE_ASSET,
            '2' => Account::TYPE_LIABILITY,
            '3' => Account::TYPE_REVENUE,
            '4' => Account::TYPE_EXPENSE,
            '5' => Account::TYPE_EXPENSE,
            '6' => Account::TYPE_EXPENSE,
            '7' => Account::TYPE_EXPENSE,
            '8' => Account::TYPE_EXPENSE,
        ];

        foreach ($descriptions as $number => $name) {
            $account = new Account();
            $account->setAccountNumber((string)$number);
            $account->setName($name);

            $firstDigit = substr((string)$number, 0, 1);
            $type = $typeMap[$firstDigit] ?? Account::TYPE_EXPENSE;

            if (in_array($number, ['2081', '2099'])) {
                $type = Account::TYPE_EQUITY;
            }

            $account->setType($type);
            $account->setIsStandard(true);

            $chart->addAccount($account);

            $this->em->persist($account);
            $accounts[(string)$number] = $account;
            $this->cleanup[] = $account;
        }

        return $accounts;
    }

    public function deleteAccount(ChartOfAccounts $chart, string $accountNumber): void
    {
        // AI assisted: Force initialization of lazy-loaded accounts collection
        $chart->getAccounts()->count();

        $account = $chart->getAccount($accountNumber);

        if (!$account) {
            throw new RuntimeException("Account {$accountNumber} not found in chart");
        }

        $chart->removeAccount($account);
        // AI assisted: Remove from DB.
        $this->em->remove($account);
        $this->em->flush();
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, Account> $accounts
     */
    private function createJournalEntry(Journal $journal, array $data, array $accounts): JournalEntry
    {
        $entry = new JournalEntry();
        $entry->setTitle($data['description']);

        $dateString = $data['date'] ?? '2025-01-15';
        $entry->setDate(new DateTime($dateString));

        $entry->setJournal($journal);

        foreach ($data['lines'] as $lineData) {
            $accountNumber = (string)$lineData['account'];

            if (!isset($accounts[$accountNumber])) {
                throw new RuntimeException("Account $accountNumber not found in fixture");
            }

            $lineItem = new JournalLineItem();
            $lineItem->setAccount($accounts[$accountNumber]);
            $lineItem->setDebitAmount($lineData['debit']);
            $lineItem->setCreditAmount($lineData['credit']);
            $lineItem->setJournalEntry($entry);

            $entry->addJournalLineItem($lineItem);
        }

        return $entry;
    }

    public function cleanup(): void
    {
        foreach (array_reverse($this->cleanup) as $entity) {
            if ($this->em->contains($entity)) {
                $this->em->remove($entity);
            }
        }
        $this->em->flush();
        $this->cleanup = [];
    }
}
