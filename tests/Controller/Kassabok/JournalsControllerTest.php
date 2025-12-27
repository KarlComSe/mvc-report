<?php

namespace App\Tests\Controller\Kassabok;

use App\Entity\Journal;

class JournalsControllerTest extends KassabokWebTestCase
{
    public function testJournalPageLoads(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $this->loginUser($user);

        $this->client->request('GET', '/proj/journals/' . $org->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testSuccessfulJournalCreation(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $chart = $this->createChartOfAccounts();
        $this->loginUser($user);

        $crawler = $this->client->request('GET', '/proj/journals/' . $org->getId());

        $form = $crawler->selectButton('Lägg till')->form([
            'journal_create_form[chartOfAccounts]' => $chart->getId(),
            'journal_create_form[firstDay]' => '2025-01-01',
            'journal_create_form[lastDay]' => '2025-12-31',
        ]);

        $this->client->submit($form);

        $journal = $this->entityManager->getRepository(Journal::class)
            ->findOneBy(['organization' => $org->getId()]);

        $this->assertNotNull($journal, 'Journal should be created');
        $this->assertEquals($chart->getId(), $journal->getChartOfAccounts()->getId());
        $this->assertEquals('2025-01-01', $journal->getFirstDay()->format('Y-m-d'));
        $this->assertEquals('2025-12-31', $journal->getLastDay()->format('Y-m-d'));

        $this->entitiesToCleanup[] = $journal;
    }
}
