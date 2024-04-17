<?php

namespace App\Tests\e2e;

use App\Security\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class UserHomeTest extends WebTestCase
{
    use HasBrowser;
    
    public function testPatientViewItsHospitalStays(): void
    {
        $patient = new User('test@test.com');
        $patient->setToken('valid-token');

        $this->browser()
            ->interceptRedirects()
            ->actingAs($patient)
            ->visit('/sejours')
            ->assertSuccessful();
    }
}
