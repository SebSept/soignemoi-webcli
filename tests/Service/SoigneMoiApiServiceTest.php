<?php

namespace App\Tests\Service;

use App\Entity\Doctor;
use App\Security\User;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
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

        static::getContainer()->set(HttpClientInterface::class, $httpClient);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        $hospitalStays = $api->getPatientHospitalStays();

        $this->assertContainsOnlyInstancesOf(HospitalStay::class, $hospitalStays);
    }

    public function testPostPatientHospitalStay(): void
    {
        // Arrange
        $hospitalStay = new HospitalStay(
            id:null,
            startDate: new DateTime('2025-12-07'),
            endDate: new DateTime('2025-12-14'),
            medicalSpeciality: 'la specialite',
            reason: 'une raison valable',
            doctor: new Doctor(4)
        );

        // les tests sont réalisés au moment de la création des requetes,
        // dans les callbacks définis ici
        $testExpectedApiCalls = [
            function ($method, $url, array $options): MockResponse {
                $body = $options['body'];
                 // Assert
                // tests basiques
                $this->assertSame('POST', $method);
                $this->assertJson($body);
                // tests sur les contenus
                $this->assertStringContainsString('"startDate":"2025-12-07', $body);
                $this->assertStringContainsString('"endDate":"2025-12-14', $body);
                $this->assertStringContainsString('"doctor":"\/api\/doctors\/4"', $body);
                $this->assertStringContainsString('"patient":"\/api\/patients\/7"', $body);
                $this->assertStringContainsString('"medicalSpeciality":"la specialite"', $body);
                $this->assertStringContainsString('"reason":"une raison valable"', $body);

                return new MockResponse(
                    'sans aucune importance, non testé',
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
        $api->postHospitalStay($hospitalStay);
    }

    public function testAuthTokenIsSent(): void
    {
        // les tests sont réalisés au moment de la création des requetes,
        // dans les callbacks définis ici
        $testExpectedApiCalls = [
            function ($method, $url, array $options): MockResponse {
                $headers = $options['normalized_headers'];
                 // Assert
                $this->assertSame('GET', $method);
                $this->assertContains('Authorization: Bearer 123', $headers['authorization'], 'debogage : contenus : '.var_export($headers, true));

                return new MockResponse(
                    json_encode(['rien' => 'sans aucune importance, non testé']),
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

    /**
     * @return (object&MockObject)|MockObject|Security|(Security&object&MockObject)|(Security&MockObject)
     */
    private function getMockedSecurity()
    {
        $user = new User('nop@nop.com');
        $user->setToken('123');
        $user->setId(7);

        $mockedSecurity = $this->createMock(Security::class);
        $mockedSecurity->method('getUser')->willReturn($user);

        return $mockedSecurity;
    }
}
