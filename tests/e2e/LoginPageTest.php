<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class LoginPageTest extends WebTestCase
{
    use HasBrowser;

    public const PATIENT_CREDENTIALS = [
        'userName' => 'patient@patient.com',
        'password' => 'hello',
        'expectedUrl' => '/sejours',
    ];

    public const DOCTOR_CREDENTIALS = [
        'userName' => 'doctor@doctor.com',
        'password' => 'hello',
        'expectedUrl' => '/doctor/patients/today',
    ];

    public const SECRETARY_CREDENTIALS = [
        'userName' => 'secretaire@secretaire.com',
        'password' => 'hello',
        'expectedUrl' => '/secretary/',
    ];

    public const ADMIN_CREDENTIALS = [
        'userName' => 'admin@admin.com',
        'password' => 'hello',
        'expectedUrl' => '/admin/',
    ];

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
        $this->markTestSkipped('tests pas fiables');
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

        // Assert
        $browser->assertRedirectedTo($expectedUrl)
        ->assertSuccessful()
        ;
    }

}
