<?php

/**
 * AI-assisted: This entire command file was created with AI assistance
 * Purpose: Import BAS (Swedish standard chart of accounts) from CSV files
 * AI helped with: CSV parsing, batch processing, progress indicators, error handling
 */

namespace App\Command;

use App\Entity\ChartOfAccounts;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-bas',
    description: 'Import BAS chart of accounts from CSV with hierarchy support'
)]
class SeedBASCommand extends Command
{
    // AI-assisted: Dependency injection for entity manager
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    // AI-assisted: Configure command arguments and options
    protected function configure(): void
    {
        $this
            ->addArgument('version', InputArgument::OPTIONAL, 'BAS version (2024 or 2025)', '2024')
            ->addOption('essential', null, InputOption::VALUE_NONE, 'Import only essential accounts (125 accounts instead of 1769)')
            ->setHelp(
                <<<'HELP'
This command imports the Swedish BAS (Bas Account Schema) chart of accounts from CSV files.

Usage examples:
  php bin/console app:seed-bas 2024          # Import full BAS 2024 (1769 accounts)
  php bin/console app:seed-bas 2025          # Import full BAS 2025 (1770 accounts)
  php bin/console app:seed-bas 2024 --essential  # Import only essential accounts

The CSV files must be located in the misc/ directory.
HELP
            );
    }

    // AI-assisted: Main execution method with full CSV import logic
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // AI-assisted: Use SymfonyStyle for better output formatting
        $io = new SymfonyStyle($input, $output);

        $version = $input->getArgument('version');
        $essential = $input->getOption('essential');

        // AI-assisted: Determine which CSV file to use
        $filename = $essential
            ? "bas_{$version}_essential.csv"
            : "bas_{$version}_accounts.csv";

        $csvFile = $this->getProjectDir() . "/misc/{$filename}";

        // AI-assisted: Validate file exists
        if (!file_exists($csvFile)) {
            $io->error("CSV file not found: {$csvFile}");
            $io->note("Available files should be in misc/ directory:");
            $io->listing([
                'bas_2024_accounts.csv (full, 1769 accounts)',
                'bas_2024_essential.csv (subset, ~125 accounts)',
                'bas_2025_accounts.csv (full, 1770 accounts)',
            ]);
            return Command::FAILURE;
        }

        // AI-assisted: Check if chart already exists to prevent duplicates
        $existing = $this->em->getRepository(ChartOfAccounts::class)
            ->findOneBy(['basVersion' => $version, 'isStandard' => true]);

        if ($existing) {
            $io->warning("BAS {$version} already exists in database!");

            // AI-assisted: Ask for confirmation before proceeding
            if (!$io->confirm('Do you want to delete and re-import?', false)) {
                $io->info('Import cancelled.');
                return Command::SUCCESS;
            }

            // AI-assisted: Delete existing chart and all its accounts (cascade)
            $io->text('Deleting existing BAS ' . $version . '...');
            $this->em->remove($existing);
            $this->em->flush();
        }

        // AI-assisted: Create new Chart of Accounts
        $io->section("Creating BAS {$version} Chart of Accounts");

        $chart = new ChartOfAccounts();
        $chart->setName("BAS {$version}" . ($essential ? ' (Essential)' : ''));
        $chart->setBasVersion($version);
        $chart->setIsStandard(true);
        $this->em->persist($chart);
        $this->em->flush();  // Flush to get chart ID

        $io->success("Chart of Accounts created: " . $chart->getName());

        // AI-assisted: Import accounts from CSV with progress indicator
        $io->section('Importing accounts from CSV');

        $count = $this->importAccountsFromCSV($csvFile, $chart, $io);

        if ($count === 0) {
            $io->error('No accounts were imported!');
            return Command::FAILURE;
        }

        // AI-assisted: Display summary statistics
        $io->success("Successfully imported {$count} accounts for BAS {$version}");

        // AI-assisted: Show hierarchy statistics
        $this->displayStatistics($chart, $io);

        return Command::SUCCESS;
    }

    /**
     * AI-assisted: Import accounts from CSV file with batch processing
     * Returns the number of accounts imported
     */
    private function importAccountsFromCSV(string $csvFile, ChartOfAccounts $chart, SymfonyStyle $io): int
    {
        $file = fopen($csvFile, 'r');

        if ($file === false) {
            $io->error("Could not open CSV file: {$csvFile}");
            return 0;
        }

        // AI-assisted: Read and skip header row
        $header = fgetcsv($file);
        $io->text('CSV columns: ' . implode(', ', $header ?: []));

        $count = 0;
        $batchSize = 100;  // AI-assisted: Process in batches for performance

        // AI-assisted: Create progress bar for visual feedback
        $io->progressStart();

        // AI-assisted: Read and process each CSV row
        while (($data = fgetcsv($file)) !== false) {
            // AI-assisted: Extract data from CSV
            // CSV structure: account_number,name,type,parent_account_number,hierarchy_level,is_detail_account,is_standard,chart_version
            [
                $accountNumber,
                $name,
                $type,
                $parentAccountNumber,
                $hierarchyLevel,
                $isDetailAccount,
                $isStandard,
                /* $csvVersion */
            ] = $data;

            // AI-assisted: Create and populate Account entity
            $account = new Account();
            $account->setAccountNumber($accountNumber);
            $account->setName($name);
            $account->setType($type);
            $account->setParentAccountNumber($parentAccountNumber ?: null);  // AI-assisted: Handle empty string
            $account->setHierarchyLevel((int)$hierarchyLevel);
            $account->setIsDetailAccount((bool)(int)$isDetailAccount);  // AI-assisted: Convert string "1"/"0" to bool
            $account->setIsStandard((bool)(int)$isStandard);
            $account->setChartOfAccounts($chart);

            $this->em->persist($account);
            $count++;

            // AI-assisted: Batch flush every 100 records for performance
            if ($count % $batchSize === 0) {
                $chartId = $chart->getId();  // AI-assisted: Store chart ID before clearing
                $this->em->flush();
                $this->em->clear();  // AI-assisted: Clear memory to prevent issues
                // AI-assisted: Re-fetch chart to keep it managed after clearing accounts
                $chart = $this->em->find(ChartOfAccounts::class, $chartId);
                $io->progressAdvance($batchSize);
            }
        }

        // AI-assisted: Final flush for remaining accounts
        $this->em->flush();
        $this->em->clear();  // AI-assisted: Only clear accounts, keep chart managed

        fclose($file);
        $io->progressFinish();

        return $count;
    }

    /**
     * AI-assisted: Display statistics about imported accounts
     */
    private function displayStatistics(ChartOfAccounts $chart, SymfonyStyle $io): void
    {
        $io->section('Import Statistics');

        // AI-assisted: Query for statistics using DQL
        $stats = $this->em->createQuery(
            'SELECT
                a.hierarchyLevel as level,
                COUNT(a) as count,
                SUM(CASE WHEN a.isDetailAccount = true THEN 1 ELSE 0 END) as detailCount
            FROM App\Entity\Account a
            WHERE a.chartOfAccounts = :chart
            GROUP BY a.hierarchyLevel
            ORDER BY a.hierarchyLevel'
        )
            ->setParameter('chart', $chart)
            ->getResult();

        // AI-assisted: Format statistics as table
        $rows = [];
        $totalAccounts = 0;
        $totalDetail = 0;

        foreach ($stats as $stat) {
            $level = $stat['level'];
            $count = $stat['count'];
            $detailCount = $stat['detailCount'];
            $groupCount = $count - $detailCount;

            $levelName = match ($level) {
                1 => 'Top categories',
                2 => 'Sub-categories',
                3 => 'Group accounts',
                4 => 'Detail accounts',
                default => "Level {$level}",
            };

            $rows[] = [
                $level,
                $levelName,
                $count,
                $groupCount,
                $detailCount,
            ];

            $totalAccounts += $count;
            $totalDetail += $detailCount;
        }

        // AI-assisted: Display formatted table
        $io->table(
            ['Level', 'Type', 'Total', 'Group', 'Detail'],
            $rows
        );

        $io->text([
            "Total accounts imported: {$totalAccounts}",
            "Group accounts (reporting only): " . ($totalAccounts - $totalDetail),
            "Detail accounts (can post transactions): {$totalDetail}",
        ]);

        // AI-assisted: Show some example accounts
        $io->section('Sample Accounts');

        $samples = $this->em->createQuery(
            'SELECT a FROM App\Entity\Account a
            WHERE a.chartOfAccounts = :chart
            AND a.accountNumber IN (:numbers)
            ORDER BY a.accountNumber'
        )
            ->setParameter('chart', $chart)
            ->setParameter('numbers', ['1', '10', '101', '1010', '1910', '1930'])
            ->getResult();

        $sampleRows = [];
        foreach ($samples as $account) {
            $sampleRows[] = [
                $account->getAccountNumber(),
                $account->getName(),
                'Level ' . $account->getHierarchyLevel(),
                $account->getParentAccountNumber() ?: '-',
                $account->isDetailAccount() ? 'Yes' : 'No',
            ];
        }

        $io->table(
            ['Code', 'Name', 'Level', 'Parent', 'Detail?'],
            $sampleRows
        );
    }

    /**
     * AI-assisted: Get project directory path
     */
    private function getProjectDir(): string
    {
        // AI-assisted: Navigate up from src/Command to project root
        return dirname(__DIR__, 2);
    }
}
