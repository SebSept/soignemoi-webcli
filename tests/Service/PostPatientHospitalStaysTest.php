<?php

declare(strict_types=1);


namespace App\Tests\Service;


use App\Entity\Doctor;
use App\Entity\HospitalStay;
use App\Service\SoigneMoiApiService;
use App\Tests\KernelTestCase;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostPatientHospitalStaysTest extends KernelTestCase
{
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
                    ['http_code' => Response::HTTP_OK]
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

}