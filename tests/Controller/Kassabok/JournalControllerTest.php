<?php

namespace App\Tests\Controller\Kassabok;

class JournalControllerTest extends KassabokWebTestCase
{
    use YearEndClosureUiHelper;

    /**
     * @dataProvider protectedPagesProvider
     */
    public function testProtectedPagesRequireAuth(string $urlPattern): void
    {
        ['journal' => $journal, 'org' => $org] = $this->createAuthenticatedScenario();
        $url = str_replace(
            ['{org_id}', '{journal_id}'],
            [$org->getId(), $journal->getId()],
            $urlPattern
        );
        $this->assertPageRequiresAuth($url);
    }

    /**
     * @dataProvider authenticatedPagesProvider
     */
    public function testAuthenticatedPagesShowContent(
        string $urlPattern,
        string $expectedContent
    ): void {
        ['user' => $user, 'journal' => $journal] = $this->createAuthenticatedScenario();
        $url = str_replace('{journal_id}', $journal->getId(), $urlPattern);
        $this->assertPageAccessibleWhenAuthenticated($url, $user, $expectedContent);
    }

    /**
     * @return array<string, array<string>>
     */
    public function protectedPagesProvider(): array
    {
        return [
            'add-entry' => ['/proj/journals/{journal_id}/add-entry'],
            'year-end-closure' => ['/proj/journals/{journal_id}/add-year-end-closure'],
            'results-page' => ['/proj/journals/{org_id}/{journal_id}/resultatrakning'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function authenticatedPagesProvider(): array
    {
        return [
            'add-entry form' => [
                '/proj/journals/{journal_id}/add-entry',
                'Spara verifikat'
            ],
            'year-end heading' => [
                '/proj/journals/{journal_id}/add-year-end-closure',
                'Nollställ resultatkonton'
            ],
        ];
    }

    public function testUserCanPerformYearEndClosure(): void
    {
        $scenario = $this->createAuthenticatedScenario();
        $this->loginUser($scenario['user']);

        $this->performYearEndClosureThroughUI($scenario['journal']);

        $this->assertResponseRedirects();
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-success');
    }

    public function testJournalEntryFormSubmissionRedirects(): void
    {
        ['user' => $user, 'journal' => $journal, 'accounts' => $accounts] =
            $this->createAuthenticatedScenario();
        $this->loginUser($user);

        $crawler = $this->client->request('GET', '/proj/journals/' . $journal->getId() . '/add-entry');
        $form = $crawler->selectButton('Spara verifikat')->form();
        $tokenField = $form['journal_entry_form[_token]'];
        assert($tokenField instanceof \Symfony\Component\DomCrawler\Field\FormField);
        $csrfToken = $tokenField->getValue();

        $this->client->request('POST', '/proj/journals/' . $journal->getId() . '/add-entry', [
            'journal_entry_form' => [
                '_token' => $csrfToken,
                'title' => 'Test entry',
                'date' => '2025-01-01',
                'journalLineItems' => [
                    0 => [
                        'account' => $accounts['1930']->getId(),
                        'debitAmount' => '',
                        'creditAmount' => '100',
                    ],
                    1 => [
                        'account' => $accounts['6570']->getId(),
                        'debitAmount' => '100',
                        'creditAmount' => '',
                    ],
                ],
            ],
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testUserCannotAccessOtherUsersJournal(): void
    {
        // ['user' => $user1, 'journal' => $journal] = $this->createAuthenticatedScenario();
        ['journal' => $journal] = $this->createAuthenticatedScenario();
        $user2 = $this->createUser();

        $this->loginUser($user2);
        $this->client->request('GET', '/journals/' . $journal->getId());

        $this->assertResponseStatusCodeSame(404);
    }
}
