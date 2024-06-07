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

    public function testAuthenticationFailsIfNoJsonResponse(): void
    {
        // Arrange
        $httpClient = new MockHttpClient();
        $mockResponse = new MockResponse('gloup gloup not json Contents', ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Act
        $response = $api->authenticateUser('nomatter@nomatter.com', 'nomatter');

        // Assert
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsIfNoTokenReceived(): void
    {
        // Arrange
        $httpClient = new MockHttpClient();
        $mockResponse = new MockResponse('{"bla": "bla"}', ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');

        // Assert
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsIfNoRoleReceived(): void 
    {
        // Arrange
        $httpClient = new MockHttpClient();
        $mockResponse = new MockResponse('{"accessToken": "123"}', ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');

        // Assert
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsRoleIsNotAllowed(): void
    {
        // Arrange
        $httpClient = new MockHttpClient();
        $mockResponse = new MockResponse('{"accessToken": "123", "role": "ROLE_NOT_A_ROLE"}', ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }
    
    public function testAuthenticationSuccessful(): void
    {
        // Arrange
        $httpClient = new MockHttpClient();
        $token = 'valid-token';
        $id = 44;
        $mockResponse = new MockResponse(
            json_encode([
                'accessToken' => $token,
                'role' => 'ROLE_PATIENT',
                'id' => $id
            ]),
            ['http_code' => Response::HTTP_OK]
        );
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Act
        $response = $api->authenticateUser('patient@patient.com', 'hello');

        // Assert
        $this->assertTrue($response->ok);
        $this->assertNotEmpty($response->token);
        $this->assertSame('ROLE_PATIENT', $response->role);
        $this->assertSame($token, $response->token);
        $this->assertSame($id, $response->id);
    }

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

    public function testPatientCanLogin(): void
    {
        // Arrange
        self::bootKernel();

        $token = '123';
        $mockResponse = new MockResponse(
            json_encode(['role' => 'ROLE_PATIENT', 'accessToken' => $token, 'id' => 1]),
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
        $this->assertSame('ROLE_PATIENT', $response->role);
        $this->assertSame($token, $response->token);

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

    public function testSecretaryCanLogin(): void
    {
        // Arrange
        self::bootKernel();

        $mockResponse = new MockResponse(
            json_encode(['role' => 'ROLE_SECRETARY', 'accessToken' => '123', 'id' => 1]),
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
        $this->assertSame('ROLE_SECRETARY', $response->role);
    }

    public function testAdminCanLogin(): void
    {
        // Arrange
        self::bootKernel();

        $mockResponse = new MockResponse(
            json_encode(['role' => 'ROLE_ADMIN', 'accessToken' => '123', 'id' => 1]),
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
        $this->assertSame('ROLE_ADMIN', $response->role);

    }

}
