<?php

namespace App\Repository;

use App\Entity\ChartOfAccounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChartOfAccounts>
 */
class ChartOfAccountsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChartOfAccounts::class);
    }

    // AI-assisted: Custom query method to find standard BAS charts
    /**
     * @return array<int, ChartOfAccounts>
     */
    public function findStandardCharts(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isStandard = true')
            ->orderBy('c.basVersion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // AI-assisted: Find chart by version
    /**
     * @return array<int, ChartOfAccounts>
     */
    public function findByVersion(string $version): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.basVersion = :version')
            ->andWhere('c.isStandard = true')
            ->setParameter('version', $version)
            ->getQuery()
            ->getResult();
    }
}
