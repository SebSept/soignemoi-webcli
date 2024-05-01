<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

use App\Entity\HospitalStay;
use Exception;
use RuntimeException;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see \App\Tests\Service\SoigneMoiApiServiceTest
 */
class SoigneMoiApiService
{
    public const ALLOWED_ROLES = ['ROLE_PATIENT', 'ROLE_DOCTOR', 'ROLE_SECRETARY', 'ROLE_ADMIN'];

    private string $token;

    private readonly Serializer $serializer;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        /** chaine sans 'api/' car on fait la demande de token au dessous.
         */
        private readonly string $apiUrl
    ) {
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(propertyTypeExtractor: new ReflectionExtractor()),
                new DateTimeNormalizer(),
                new ArrayDenormalizer(),
            ],
            [new JsonEncoder()]);
    }

    public function authenticateUser(string $email, string $password): ApiResponse
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
            return new ApiResponse(ok: false);
        }

        try {
            /** @var object{accessToken: string, role: string, id: int} $json */
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

            $id = $json->id ?? null;
            // json bien décodé mais ne contient pas de champs accessToken (ou est exactement null)
            if (is_null($id)) {
                throw new RuntimeException('no id field');
            }

            if (!in_array($role, self::ALLOWED_ROLES)) {
                throw new InvalidRoleException('Expected ROLE_PATIENT but got '.$role);
            }
        } catch (Exception) {
            return new ApiResponse(ok: false); // @todo message et/ou log
        }

        return new ApiResponse(true, $token, $role, $id);
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return HospitalStay[]
     */
    public function getHospitalStays(int $patientId): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl.'/api/patients/'.$patientId.'/hospital_stays', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                throw new RuntimeException('Code réponse inatendu :'.$response->getStatusCode());
            }

            return $this->serializer->deserialize($response->getContent(), 'App\Entity\HospitalStay[]', 'json'); /* @phpstan-ignore-line */
        } catch (Exception $exception) {
            throw new ApiException('Erreur récupération des séjours : '.$exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
