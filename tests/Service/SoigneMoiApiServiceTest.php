<?php

namespace App\Tests\Service;

use App\Security\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceTest extends KernelTestCase
{
    public function testGetPatientHospitalStays(): void
    {
        $httpClient = new MockHttpClient();
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__.'/response_hospital_stays.json'),
            ['http_code' => Response::HTTP_OK]
        );
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);

        $user = new User('nop@nop.com');
        $user->setToken('123');
        $user->setId(7);

        $mockedSecurity = $this->createMock(Security::class);
        $mockedSecurity->method('getUser')->willReturn($user);

        static::getContainer()->set(HttpClientInterface::class, $httpClient);
        static::getContainer()->set(Security::class, $mockedSecurity);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        $hospitalStays = $api->getPatientHospitalStays();

        $this->assertContainsOnlyInstancesOf(HospitalStay::class, $hospitalStays);
    }
}
