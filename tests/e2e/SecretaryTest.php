<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class SecretaryTest extends WebTestCase
{
    use HasBrowser;
    use Credentials;
    
    public function testGetEntriesToday(): void
    {
        
        $this->secretaryBrowser()
            ->assertSee('Entrées et sorties du jour');
    }
    
    public function testViewPatientFile(): void
    {
        $this->secretaryBrowser()
            ->visit('/hospital_stay/details/1295')
            ->assertSuccessful()
            ->assertSee('Dossier du séjour')
        ;
    }

}
