<?php

namespace App\Tests\e2e;

use stdClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class LoginPageTest extends WebTestCase
{
    use HasBrowser;

    private static object $validCredentials;

    private static object $invalidCredentials;

    public static function setUpBeforeClass(): void
    {
        $credentials = new stdClass();
        $credentials->email = 'patient@patient.com';
        $credentials->password = 'hello';
        self::$validCredentials = $credentials;

        $credentials = new stdClass();
        $credentials->email = 'invalid@invalid.com';
        $credentials->password = 'invalid-password';
        self::$invalidCredentials = $credentials;

        parent::setUpBeforeClass();
    }


    public function testViewLoginFormIfNotLoggedIn(): void
    {
        $this->browser()->visit('/login')
            ->assertSuccessful()
            ->assertSee('Connexion')
            ->assertSeeElement('form');
    }
}
