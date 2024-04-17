<?php

namespace App\Tests\Service;

use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceTest extends TestCase
{
    
    public function testAuthenticationFailsIfUnAuthorized(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => Response::HTTP_UNAUTHORIZED]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        $response = $api->authenticatePatient('email@email.com', 'password');
        $this->assertFalse($response->ok);
        // @todo tester les champs attendus
    }

    public function testAuthenticationFailsIfNoJsonResponse(): void
    {
        // Arrange
        $mockResponse = new MockResponse('gloup gloup not json Contents', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Assert
//        $this->expectException(ApiException::class);
//        $this->expectExceptionMessageMatches('/.*Syntax error.*/');

        // Act
        $response = $api->authenticatePatient('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsIfNoTokenReceived(): void
    {
        // Arrange
        $mockResponse = new MockResponse('{"bla": "bla"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Assert
//        $this->expectException(ApiException::class);
//        $this->expectExceptionMessageMatches('/.*no accessToken field*/');

        // Act
        $response = $api->authenticatePatient('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsIfNoRoleReceived(): void 
    {
        // Arrange
        $mockResponse = new MockResponse('{"accessToken": "123"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Assert
//        $this->expectException(ApiException::class);
//        $this->expectExceptionMessageMatches('/.*no Role field*/');

        // Act
        $response = $api->authenticatePatient('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }

    public function testAuthenticationFailsRoleIsNotPatient(): void
    {
        // Arrange
        $mockResponse = new MockResponse('{"accessToken": "123", "role": "doctor"}', ['http_code' => Response::HTTP_OK]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        // Assert
//        $this->expectException(InvalidRoleException::class);
//        $this->expectExceptionMessageMatches('/.*doctor*/');

        // Act
        $response = $api->authenticatePatient('email@email.com', 'password');
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
        $response = $api->authenticatePatient('patient@patient.com', 'hello');

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

}
