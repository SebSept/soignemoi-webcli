<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class UserHomeTest extends WebTestCase
{
    use HasBrowser;

    public function testNotLoggedViewHospitalStaysRedirectedToLogin(): void
    {
        $this->browser()
            ->interceptRedirects()
            ->visit('/patient/sejours')
            ->assertRedirectedTo('/login');
    }
}
