<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see \App\Tests\Service\SoigneMoiApiServiceTest
 */
readonly class SoigneMoiApiService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiUrl
    ) {
    }

    public function authenticate(string $email, string $password): ApiResponse
    {
        $response = $this->httpClient->request(
            'POST',
            $this->apiUrl.'/token',
            [
                'json' => [
                    'email' => $email,
                    'password' => $password,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

        if (200 !== $response->getStatusCode()) {
            return new ApiResponse('', false);
        }

        dump($response->getStatusCode(), $response->getContent());

        try {
            $token = json_decode($response->getContent(), flags: JSON_THROW_ON_ERROR)->accessToken ?? null;
            if (is_null($token)) {
                throw new RuntimeException('Pas de champs accessToken dans la réponse ');
            }
        } catch (Exception $exception) {
            throw new RuntimeException('Erreur lors de la récupération du token : '.$exception->getMessage().' - '.$response->getContent(), $exception->getCode(), $exception);
        }

        return new ApiResponse($token, true);
    }
}
