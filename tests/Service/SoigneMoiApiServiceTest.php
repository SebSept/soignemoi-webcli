<?php

namespace App\Tests\Service;

use App\Service\SoigneMoiApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

class SoigneMoiApiServiceTest extends TestCase
{
    public function testAuthenticationFails(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => Response::HTTP_UNAUTHORIZED]);
        $client = new MockHttpClient($mockResponse);
        $api = new SoigneMoiApiService($client, 'https://mock.me:666');

        $response = $api->authenticatePatient('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }    
    
    public function testAuthenticationSuccessful(): void
    {
        // Arrange
        $token = 'valid-token';
        $apiUrl = 'https://mock.me:666';
        $mockResponse = new MockResponse(
            json_encode([
                    'accessToken' => $token,
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
        $this->assertSame($token, $response->token); // pas possible de le prévoir pour une vrai requete.
    }


}
