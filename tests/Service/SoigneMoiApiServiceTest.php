<?php

namespace App\Tests\Service;

use App\Service\SoigneMoiApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SoigneMoiApiServiceTest extends TestCase
{
    public function testAuthenticationSuccess(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        // todo: mock the response of the http client
        $api = new SoigneMoiApiService($httpClient, 'http://mock.me:666');

        $response = $api->authenticate('email@email.com', 'password');
        $this->assertFalse($response->ok);
    }
}
