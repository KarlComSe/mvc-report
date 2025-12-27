<?php

namespace App\Tests\Controller\Kassabok;

use App\Entity\User;

class RegistrationControllerTest extends KassabokWebTestCase
{
    public function testRegistrationPageLoads(): void
    {
        $this->client->request('GET', '/proj/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSuccessfulRegistration(): void
    {
        $crawler = $this->client->request('GET', '/proj/register');

        $email = 'test_' . uniqid() . '@test.com';
        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => 'test123',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/proj/');

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        $this->assertNotNull($user);
        $this->assertSame($email, $user->getEmail());
        $this->entitiesToCleanup[] = $user;
    }
}
