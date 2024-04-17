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
        $this->markTestSkipped('fonctionne bien mais on doit changer le token à chaque fois');
        $patient = new User('test@test.com');
        $patient->setToken('64d08f6808632411b7c18d9d635547a1692269011c88f5c0e6c0fe1c0f21276f'); // vrai token
        $patient->setId(672);
        $patient->setRoles(['ROLE_PATIENT']);

        // mocking pas effectif. besoin d'une interface ?
//        $apiService = $this->createMock(SoigneMoiApiService::class);
//        $apiService->method('getHospitalStays')
//            ->willReturn([
//                new HospitalStay(1, new \DateTime(), new \DateTime(), null, null, "bla", "blaie"),
//                    ]
//            )
//        ;
//
//        $this->getContainer()->set(SoigneMoiApiService::class, $apiService);

        $this->browser()
            ->interceptRedirects()
            ->actingAs($patient)
            ->visit('/sejours')
            ->assertSuccessful();
    }

    public function testNotLoggedViewHospitalStaysRedirectedToLogin(): void
    {
        $this->browser()
            ->interceptRedirects()
            ->visit('/sejours')
            ->assertRedirectedTo('/login');
    }
}
