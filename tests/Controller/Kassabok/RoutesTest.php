<?php

namespace App\Tests\Controller\Kassabok;

class RoutesTest extends KassabokWebTestCase
{
    /**
     * @dataProvider publicRoutes
     */
    public function testPublicPages(string $url, int $status): void
    {
        $this->client->request('GET', $url);
        $this->assertResponseStatusCodeSame($status);
    }

    /**
     * @return array<int, array<int|string>>
     */
    public function publicRoutes(): array
    {
        return [
            ['/proj/', 200],
            ['/proj/login', 200],
            ['/proj/register', 200],
            ['/proj/about', 200],
            ['/proj/about/database', 200],
        ];
    }

    /**
     * @dataProvider protectedRoutes
     */
    public function testProtectedPagesRedirect(string $url): void
    {
        $this->client->request('GET', $url);
        $this->assertResponseRedirects();
    }

    /**
     * @return array<int, array<string>>
     */
    public function protectedRoutes(): array
    {
        return [
            ['/proj/organization'],
            ['/proj/journals/1']
        ];
    }

    public function testOrganizationPageWorksWhenLoggedIn(): void
    {
        $user = $this->createUser();
        $this->loginUser($user);

        $this->client->request('GET', '/proj/organization');

        $this->assertResponseIsSuccessful();
    }
}
