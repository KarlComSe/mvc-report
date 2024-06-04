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

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();


        // // Filter organizations based on user membership (if not admin)
        // if (!$this->security->isGranted('ROLE_ADMIN')) {
        //     $currentUser = $this->security->getUser();
        //     $organizations = array_filter($organizations, function (Organization $organization) use ($currentUser) {
        //         return $organization->getUsers()->contains($currentUser);
        //     });
        // }
    }

    //    /**
    //     * @return Organization[] Returns an array of Organization objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Organization
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
