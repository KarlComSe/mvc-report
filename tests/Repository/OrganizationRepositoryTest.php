<?php

namespace App\Tests\Repository;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Tests\Controller\Kassabok\KassabokWebTestCase;

class OrganizationRepositoryTest extends KassabokWebTestCase
{
    private OrganizationRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var OrganizationRepository $repository */
        $repository = $this->entityManager->getRepository(Organization::class);
        $this->repository = $repository;
    }

    public function testFindByUserReturnsUserOrganizations(): void
    {
        $user = $this->createUser();
        $org1 = $this->createOrganization('Org 1');
        $org2 = $this->createOrganization('Org 2');
        $otherUserOrg = $this->createOrganization('Other Org');

        $org1->addUser($user);
        $org2->addUser($user);
        $this->entityManager->flush();

        $result = $this->repository->findByUser($user);

        $this->assertCount(2, $result);
        $this->assertContains($org1, $result);
        $this->assertContains($org2, $result);
        $this->assertNotContains($otherUserOrg, $result);
    }

    public function testFindByUserReturnsEmptyArrayWhenNoOrganizations(): void
    {
        $user = $this->createUser();

        $result = $this->repository->findByUser($user);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindReturnsNullForNonExistentOrganization(): void
    {
        $result = $this->repository->find(99999);

        $this->assertNull($result);
    }
}
