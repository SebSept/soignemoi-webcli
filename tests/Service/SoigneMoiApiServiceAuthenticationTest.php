<?php

namespace App\Tests\Service;

use Generator;
use App\Tests\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\SoigneMoiApiService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceAuthenticationTest extends KernelTestCase
{
    public function testAuthTokenIsSent(): void
    {
        // les tests sont réalisés au moment de la création des requetes,
        // dans les callbacks définis ici
        $testExpectedApiCalls = [
            function ($method, $url, array $options): JsonMockResponse {
                $headers = $options['normalized_headers'];
                // Assert
                $this->assertSame('GET', $method);
                $this->assertContains('Authorization: Bearer 123', $headers['authorization'], 'debogage : contenus : '.var_export($headers, true));

                return new JsonMockResponse(
                    ['rien' => 'sans aucune importance, non testé'],
                    ['http_code' => Response::HTTP_OK] // important sinon, exception est levée
                );
            }
        ];

        $httpClient = new MockHttpClient($testExpectedApiCalls);

        static::getContainer()->set(HttpClientInterface::class, $httpClient);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $api->getDoctors();
    }

    public function testAuthenticationFailsIfUnAuthorized(): void
    {
        // Arrange
        self::bootKernel();

        $mockResponse = new JsonMockResponse(
            ['role' => 'ROLE_ADMIN', 'accessToken' => '123', 'id' => 1], // données pour être sur que c'est le code http qui détermine le succès
            ['http_code' => Response::HTTP_UNAUTHORIZED]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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
        $httpClient = new MockHttpClient();
        $mockResponse = new JsonMockResponse(['bla' => 'bla'], ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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
        $mockResponse = new JsonMockResponse(['accessToken' => "123"], ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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
        $mockResponse = new JsonMockResponse(['accessToken' => "123", "role" => "ROLE_NOT_A_ROLE"], ['http_code' => Response::HTTP_OK]);
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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
        $mockResponse = new JsonMockResponse(
            [
                'accessToken' => $token,
                'role' => 'ROLE_PATIENT',
                'id' => $id
            ],
            ['http_code' => Response::HTTP_OK]
        );
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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

    public function testPatientCanLogin(): void
    {
        // Arrange
        self::bootKernel();

        $token = '123';
        $mockResponse = new JsonMockResponse(
            ['role' => 'ROLE_PATIENT', 'accessToken' => $token, 'id' => 1],
            ['http_code' => 200]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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

        $mockResponse = new JsonMockResponse(
            ['role' => 'ROLE_DOCTOR', 'accessToken' => '123', 'id' => 1],
            ['http_code' => 200]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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

        $mockResponse = new JsonMockResponse(
            ['role' => 'ROLE_SECRETARY', 'accessToken' => '123', 'id' => 1],
            ['http_code' => 200]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
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

        $mockResponse = new JsonMockResponse(
            ['role' => 'ROLE_ADMIN', 'accessToken' => '123', 'id' => 1],
            ['http_code' => 200]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): JsonMockResponse => $mockResponse);
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $response = $api->authenticateUser('nomatter@nomatter.com', 'nomatter');

        // Assert
        $this->assertTrue($response->ok);
        $this->assertSame('ROLE_ADMIN', $response->role);
    }

    public function testTransportExceptionIsThrowOnNetworkFailure(): void
    {
        // Arrange
        $httpClient = new MockHttpClient(
            new MockResponse((static function (): Generator {
                yield new TransportException('Error at transport level');
            })())
        );
        static::getContainer()->set(HttpClientInterface::class, $httpClient);

        // Assert
        $this->expectException(TransportException::class);

        // Act
        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $api->authenticateUser('nomatter@nomatter.com', 'nomatter');
    }

}
