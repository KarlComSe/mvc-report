<?php

namespace App\Tests\Controller\Kassabok;

use App\Entity\Journal;
use Symfony\Component\DomCrawler\Crawler;

trait YearEndClosureUiHelper
{
    protected function performYearEndClosureThroughUI(Journal $journal): void
    {
        $crawler = $this->client->request(
            'GET',
            sprintf('/proj/journals/%d/add-year-end-closure', $journal->getId())
        );

        $form = $crawler
            ->selectButton('Nollställ resultatkonton')
            ->form();

        $this->client->submit($form);
    }
}
