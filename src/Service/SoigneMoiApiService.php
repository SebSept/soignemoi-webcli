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

    public function authenticatePatient(string $email, string $password): ApiResponse
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

        try {
            /** @var object{accessToken: string, role: string} $json */
            $json = json_decode($response->getContent(), flags: JSON_THROW_ON_ERROR);
            $token = $json->accessToken ?? null;
            // json bien décodé mais ne contient pas de champs accessToken (ou est exactement null)
            if (is_null($token)) {
                throw new RuntimeException('no accessToken field');
            }

            $role = $json->role ?? null;
            // json bien décodé mais ne contient pas de champs accessToken (ou est exactement null)
            if (is_null($role)) {
                throw new RuntimeException('no Role field');
            }

            if ('ROLE_PATIENT' !== $role) {
                throw new InvalidRoleException('Expected ROLE_PATIENT but got '.$role);
            }
        } catch (Exception $exception) {
            // pas de recapture pour les exceptions déjà de notre type
            if ($exception instanceof ApiException) {
                throw $exception;
            }

            throw new ApiException('Erreur authentification : '.$exception->getMessage().' - '.$response->getContent(), $exception->getCode(), $exception);
        }

        return new ApiResponse($token, true);
    }
}
