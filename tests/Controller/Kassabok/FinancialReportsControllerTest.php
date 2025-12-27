<?php

namespace App\Tests\Controller\Kassabok;

class FinancialReportsControllerTest extends KassabokWebTestCase
{
    public function testIncomeStatementRequiresAuth(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);
        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/resultatrakning',
            $org->getId(),
            $journal->getId()
        ));
        $this->assertResponseRedirects();
    }

    public function testBalanceSheetRequiresAuth(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/balansrakning',
            $org->getId(),
            $journal->getId()
        ));

        $this->assertResponseRedirects();
    }

    public function testIncomeStatementLoads(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->loginUser($user);

        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/resultatrakning',
            $org->getId(),
            $journal->getId()
        ));

        $this->assertResponseIsSuccessful();
    }

    public function testBalanceSheetLoads(): void
    {
        $user = $this->createUser();
        $org = $this->createOrganization(owner: $user);
        $journal = $this->createJournal($org);

        $this->loginUser($user);

        $this->client->request('GET', sprintf(
            '/proj/journals/%d/%d/balansrakning',
            $org->getId(),
            $journal->getId()
        ));

        $this->assertResponseIsSuccessful();
    }

    /**
     * @dataProvider reportRoutes
     */
    public function testReportRoutesWithInvalidIds(string $route): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $this->client->request('GET', sprintf($route, 999999, 999999));

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * @return array<string, array<string>>
     */
    public function reportRoutes(): array
    {
        return [
            'income statement' => ['/proj/journals/%d/%d/resultatrakning'],
            'balance sheet' => ['/proj/journals/%d/%d/balansrakning'],
        ];
    }
}
