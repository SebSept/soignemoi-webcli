<?php

declare(strict_types=1);

namespace App\Tests;

use JsonException;
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
     * @xreturn (object&MockObject)|MockObject|Security|(Security&object&MockObject)|(Security&MockObject)
     * @return MockObject<Security>
     */
    protected function getMockedSecurity(): MockObject
    {
        $user = new User('nop@nop.com');
        $user->setToken('123');
        $user->setId(7);

        $mockedSecurity = $this->createMock(Security::class);
        $mockedSecurity->method('getUser')->willReturn($user);

        return $mockedSecurity;
    }


    /**
     * @param int|null $httpCode si laissé à null : lecture du status dans le json, sinon Response::HTTP_OK
     * @throws JsonException
     */
    protected function prepareHttpResponse(
        string $jsonFilePath,
        ?int   $httpCode = null,
        array $headers = []
    ): void
    {
        $filePath = __DIR__ . '/Service/stubs/' . $jsonFilePath;
        $body = file_get_contents($filePath);
        if(!(is_string($body))) {
            throw new Exception('Echec ouverture du fichier de stub ' . $filePath);
        }

        // récupération du status dans le payload (pour erreurs)
        if(is_null($httpCode)) {
            try {
                $httpCode = json_decode($body, flags: JSON_THROW_ON_ERROR)->status ?? null;
            }
            catch (JsonException) {
                throw new Exception('Echec lecture du status dans le json '.$filePath.'. As tu oublié de passer explicitement le code de retour attendu ?');
            }
        }

        // si on a toujours pas status, ni du paramètre, ni du json, c'est 200
        $httpCode ??= Response::HTTP_OK;

        $mockResponse = new MockResponse(
            $body,
            [
                'http_code' => $httpCode,
                'response_headers' => $headers
            ]
        );
        $httpClient = new MockHttpClient();
        $httpClient->setResponseFactory(static fn(): MockResponse => $mockResponse);

        KernelTestCase::getContainer()->set(HttpClientInterface::class, $httpClient);
    }
}