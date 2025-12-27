<?php

namespace App\Tests\Controller\Kassabok;

use App\Entity\Organization;

class OrganizationControllerTest extends KassabokWebTestCase
{
    public function testOrganizationPageLoads(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $this->client->request('GET', '/proj/organization');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSuccessfulOrganizationCreation(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $crawler = $this->client->request('GET', '/proj/organization');

        $organizationName = 'Org_' . uniqid();

        $form = $crawler->selectButton('Lägg till')->form([
            'organization_form[name]' => $organizationName,
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/proj/organization');

        $organization = $this->entityManager->getRepository(Organization::class)
            ->findOneBy(['name' => $organizationName]);

        $this->assertNotNull($organization);
        $this->entitiesToCleanup[] = $organization;
    }
}
