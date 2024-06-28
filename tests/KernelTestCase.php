<?php

declare(strict_types=1);

namespace App\Tests;

use Exception;
use App\Security\User;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyKernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KernelTestCase extends SymfonyKernelTestCase
{

    /**
     * @return (object&MockObject)|MockObject|Security|(Security&object&MockObject)|(Security&MockObject)
     */
    protected function getMockedSecurity()
    {
        $user = new User('nop@nop.com');
        $user->setToken('123');
        $user->setId(7);

        $mockedSecurity = $this->createMock(Security::class);
        $mockedSecurity->method('getUser')->willReturn($user);

        return $mockedSecurity;
    }

    protected function prepareHttpResponse(
        string $jsonFilePath,
        int    $httpCode = Response::HTTP_OK
    ): void
    {
        $httpClient = new MockHttpClient();
        $filePath = __DIR__ . '/Service/stubs/' . $jsonFilePath;
        $body = file_get_contents($filePath);
        if(!(is_string($body))) {
            throw new Exception('Echec ouverture du fichier de stub ' . $filePath);
        }

        $mockResponse = new MockResponse(
            $body,
            ['http_code' => $httpCode]
        );
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);

        KernelTestCase::getContainer()->set(HttpClientInterface::class, $httpClient);
    }
}