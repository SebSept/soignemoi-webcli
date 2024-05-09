<?php

namespace App\Tests\e2e;

use App\Security\User;
use Zenstruck\Browser\KernelBrowser;

trait Credentials
{

    public const PATIENT_CREDENTIALS = [
        'userName' => 'patient@patient.com',
        'password' => 'hello',
        'expectedUrl' => '/patient/sejours',
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

    protected function secretaryBrowser(): KernelBrowser
    {
        return $this->browser()
            ->visit('/login')
            ->fillField('inputEmail', self::SECRETARY_CREDENTIALS['userName'])
            ->fillField('inputPassword', self::SECRETARY_CREDENTIALS['password'])
            ->clickAndIntercept('submit')
            ->assertRedirected()
            ->followRedirects();
    }

    private function secretary(): User
    {
        $u = new User(self::SECRETARY_CREDENTIALS['userName']);
        $u->setRoles(['ROLE_SECRETARY']);

        return $u;
    }
}