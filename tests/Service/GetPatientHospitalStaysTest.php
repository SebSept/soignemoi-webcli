<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use App\Tests\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GetPatientHospitalStaysTest extends KernelTestCase
{

    /**
     * @test On obtient des Entités HospitalStay si on a une réponse conforme
     */
    public function testGetPatientHospitalStaysReturnsArrayOfHospitalStay(): void
    {
        $this->prepareHttpResponse('response_hospital_stays.json');
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        $hospitalStays = $api->getPatientHospitalStays();

        $this->assertIsArray($hospitalStays);
        $this->assertContainsOnlyInstancesOf(HospitalStay::class, $hospitalStays);
    }

}
