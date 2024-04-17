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

    public function testLoginWithValidCredentials(): void
    {
        $this->markAsRisky();
        // On ne peut pas mocker le serveur, il faut s\'assurer d\'avoir les bonnes données dans l\'api
        // Ce qu'on peut faire, ça serait d'implémenter un authentificateur pour le mode test
        // Cet auth considèrerait un certain mail/mot de passe comme valide et un autre comme invalide
        $this->browser()->visit('/login')
            ->fillField('inputEmail', self::$validCredentials->email)
            ->fillField('inputPassword', self::$validCredentials->password)
            ->clickAndIntercept('submit')
            ->assertRedirectedTo('/sejours');
    }

    public function testLoginWithInValidCredentials(): void
    {
        $this->browser()->visit('/login')
            ->fillField('inputEmail', self::$invalidCredentials->email)
            ->fillField('inputPassword', self::$invalidCredentials->password)
            ->clickAndIntercept('submit')
            ->assertRedirectedTo('/login')
            ->assertSee('Identification ratée');
    }

}