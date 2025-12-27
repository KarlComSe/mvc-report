<?php

namespace App\Tests\Controller\Kassabok;

class StartControllerTest extends KassabokWebTestCase
{
    public function testStartPageLoads(): void
    {
        $this->client->request('GET', '/proj/');

        $this->assertResponseIsSuccessful();
    }

    public function testJournalsRequireLogin(): void
    {
        $this->client->request('GET', '/proj/journals/1');

        $this->assertResponseRedirects();
    }
}
