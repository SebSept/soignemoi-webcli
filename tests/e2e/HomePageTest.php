<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class HomePageTest extends WebTestCase
{
    use HasBrowser;

    public function testViewHomePage(): void
    {
        $this->browser()
            ->visit('/')
            ->assertSuccessful();
    }

    public function testRedirectedToLoginPageIfNotAuthenticated(): void
    {
        $this->browser()->interceptRedirects()
            ->visit('/sejours')
            ->assertNotAuthenticated()
            ->assertRedirectedTo('/login');
    }

    public function testViewLoginFormIfNotLoggedIn(): void
    {
        $this->browser()->visit('/login')
            ->assertSuccessful()
            ->assertSee('Connexion')
            ->assertSeeElement('form');
    }

}
