<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserFactory
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Create a test user with generated email
     *
     * @return User
     */
    public function create(): User
    {
        $user = new User();
        $user->setEmail('test_' . uniqid() . '@test.com');
        $user->setPassword('hashed_password');

        $this->em->persist($user);

        return $user;
    }
}
