<?php

namespace App\Tests\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceTest extends KernelTestCase
{
    
    public function testAuthenticationFailsIfUnAuthorized(): void
    {
        // Arrange
        self::bootKernel();

        $mockResponse = new MockResponse(
            json_encode(['role' => 'ROLE_ADMIN', 'accessToken' => '123', 'id' => 1]), // données pour être sur que c'est le code http qui détermine le succès
            ['http_code' => Response::HTTP_UNAUTHORIZED]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $response = $api->authenticateUser('nomatter@nomatter.com', 'nomatter');

        // Assert
        $this->assertFalse($response->ok);
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
        $mockResponse = new MockResponse('{"bla": "bla"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        // @todo reéécrire ces tests pour utiliser le container
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Assert
//        $this->expectException(ApiException::class);
//        $this->expectExceptionMessageMatches('/.*no accessToken field*/');

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsIfNoRoleReceived(): void 
    {
        // Arrange
        $mockResponse = new MockResponse('{"accessToken": "123"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');

        // Assert
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsRoleIsNotAllowed(): void
    {
        // Arrange
        $mockResponse = new MockResponse('{"accessToken": "123", "role": "ROLE_NOT_A_ROLE"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Act
        $response = $api->authenticateUser('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }
    
    public function testAuthenticationSuccessful(): void
    {
        // Arrange
        $token = 'valid-token';
        $apiUrl = 'https://mock.me:666';
        $id = 44;
        $mockResponse = new MockResponse(
            json_encode([
                'accessToken' => $token,
                'role' => 'ROLE_PATIENT',
                'id' => $id
            ]),
            ['http_code' => Response::HTTP_OK]
        );
        $client = new MockHttpClient($mockResponse);

        // test avec client non mocké
//         $apiUrl = 'http://192.168.96.2:80';
//         $apiUrl = 'http://192.168.176.1:32772';
//         $client = HttpClient::create();

        // Act
        $api = new SoigneMoiApiService($client, $apiUrl);
        $response = $api->authenticateUser('patient@patient.com', 'hello');

        // Assert
        $this->assertTrue($response->ok);
        $this->assertNotEmpty($response->token);
        $this->assertSame('ROLE_PATIENT', $response->role);
        $this->assertSame($token, $response->token);
        $this->assertSame($id, $response->id);
    }

    public function testGetHospitalStays(): void
    {
        $apiUrl = 'https://mock.me:666';
        $mockResponse = new MockResponse(
            file_get_contents(__DIR__.'/response_hospital_stays.json'),
            ['http_code' => Response::HTTP_OK]
        );
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, $apiUrl);
        $api->setToken('valid-token');

        $hospitalStays = $api->getHospitalStays(44);

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
