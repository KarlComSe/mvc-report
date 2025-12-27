<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }
    /**
     * @return array<int, Organization>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
