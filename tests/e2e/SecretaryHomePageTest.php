<?php

namespace App\Tests\e2e;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class SecretaryHomePageTest extends WebTestCase
{
    use HasBrowser;
    use Credentials;
    
    public function testGetEntriesToday(): void
    {
        
        $this->secretaryBrowser()
            ->assertSee('Entrées et sorties du jour');
    }

}
