<?php

namespace App\Tests\Service;

use DateTime;
use App\Entity\Doctor;
use App\Security\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceLoggerTest extends KernelTestCase
{

    public function testCriticalLogIsAddedOnHttpForbiddenResponse(): void
    {
        // Arrange
        self::bootKernel();

        // Mock Http response
        $mockResponse = new MockResponse(
            json_encode(['lacharge' => 'recue']),
            ['http_code' => Response::HTTP_FORBIDDEN]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        $security = $this->createMock(Security::class);
        $fakeUser = new User('nop@nop.com');
        $fakeUser->setToken('123');
        $fakeUser->setId('77');
        $security->method('getUser')->willReturn($fakeUser);
        static::getContainer()->set(Security::class, $security);

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $this->expectException(AccessDeniedException::class);
//        $response = $api->getDoctors();

        $api->postHospitalStay(new HospitalStay(
            startDate: new DateTime(),
            endDate: new DateTime('+1 day'),
            medicalSpeciality: 'la spe',
            doctor: new Doctor(1, 'doc')
        ));

        // pas de test pour le logger, a regarder à la main
        $this->markTestIncomplete('A regarder à la main dans le log des erreurs d\'api.');
    }

    public function testDoctorCanLogin(): void
    {
        // Arrange
        self::bootKernel();

        $mockResponse = new MockResponse(
            json_encode(['role' => 'ROLE_DOCTOR', 'accessToken' => '123', 'id' => 1]),
            ['http_code' => 200]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $response = $api->authenticateUser('nomatter@nomatter.com', 'nomatter');

        // Assert
        $this->assertTrue($response->ok);
        $this->assertSame('ROLE_DOCTOR', $response->role);
    }

}
