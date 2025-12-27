<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Organization;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testBasicGettersAndSetters(): void
    {
        $user = new User();
        $email = 'test@example.com';
        $role = 'superAdmin';
        $password = 'password';
        $user->setEmail($email);
        $user->setRoles([$role]);

        $user->setPassword($password);

        $this->assertContains($role, $user->getRoles());
        $this->assertNull($user->getId());

        $this->assertSame($email, $user->getEmail());
        $this->assertSame($email, $user->getUserIdentifier());

        $this->assertSame($password, $user->getPassword());
        $user->eraseCredentials();
    }

    public function testOrganization(): void
    {
        $user = new User();
        $organization = $this->createMock(Organization::class);
        $user->addOrganization($organization);

        $this->assertContains($organization, $user->getOrganizations());

        $user->removeOrganization($organization);

        $this->assertNotContains($organization, $user->getOrganizations());
    }
}
