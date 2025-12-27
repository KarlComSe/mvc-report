<?php

namespace App\Tests\Controller\Kassabok;

class JournalAccountLedgerControllerTest extends KassabokWebTestCase
{
    public function testRequiresAuthentication(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);
        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/grundbok',
            $org->getId(),
            $journal->getId()
        ));
        $this->assertResponseRedirects();
    }

    public function testAccountLedgerPageLoads(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->loginUser($user);

        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/grundbok',
            $org->getId(),
            $journal->getId()
        ));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2');
    }

    public function testAccountLedgerDisplaysData(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->loginUser($user);

        $crawler = $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/grundbok',
            $org->getId(),
            $journal->getId()
        ));

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('body')->count());
    }

    public function testWrongOrganizationId(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->loginUser($user);

        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/grundbok',
            999999,
            $journal->getId()
        ));

        $this->assertResponseStatusCodeSame(404);
    }
}
