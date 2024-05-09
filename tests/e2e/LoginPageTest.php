<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class LoginPageTest extends WebTestCase
{
    use HasBrowser;
    use Credentials;

    public function testLoginAsPatientIsRedirectedToPatientHomePage(): void
    {
        $this->testLogin(...self::PATIENT_CREDENTIALS);
    }

    public function testLoginAsDoctorIsRedirectedToDoctorHomePage(): void
    {
        $this->testLogin(...self::DOCTOR_CREDENTIALS);
    }

    public function testLoginAsSecretaryIsRedirectedToSecretaryHomePage(): void
    {
        $this->testLogin(...self::SECRETARY_CREDENTIALS);
    }

    public function testLoginAsAdminIsRedirectedToAdminHomePage(): void
    {
        $this->testLogin(...self::ADMIN_CREDENTIALS);
    }


    private function testLogin(string $userName, string $password, string $expectedUrl): void
    {
//        $this->markTestSkipped('tests pas fiables');
        $browser = $this->browser()->visit('/login');

        // Act
        // view login form
        $browser->assertSuccessful()
            ->assertSee('Connexion')
            ->assertSeeElement('form');

        // fill & submit form
        $browser
            ->fillField('inputEmail', $userName)
            ->fillField('inputPassword', $password)
            ->clickAndIntercept('submit');

//        $this->debugBrowser($browser);

        // Assert
        $browser->assertRedirectedTo($expectedUrl)
            ->assertSuccessful();
    }

    private function debugBrowser(\Zenstruck\Browser|\Zenstruck\Browser\KernelBrowser $browser)
    {
        dump($browser->profile()->getCollector('http_client')->getClients()['http_client']['traces'][0]['info']);
    }

}
