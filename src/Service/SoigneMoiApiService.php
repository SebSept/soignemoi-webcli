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

    public const ALLOWED_ROLES_WITHOUT_ID = ['ROLE_SECRETARY', 'ROLE_ADMIN'];

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
        try {
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
                // @todo a logger
                return new ApiResponse(ok: false);
            }

            /** @var object{accessToken: string, role: string, id: int} $json */
            $json = json_decode($response->getContent(), flags: JSON_THROW_ON_ERROR);
            $token = $json->accessToken ?? null;
            if (is_null($token)) {
                throw new RuntimeException('no accessToken field');
            }

            $role = $json->role ?? null;
            if (is_null($role)) {
                throw new RuntimeException('no Role field');
            }

            $id = $json->id ?? null;
            // secrétaire et admin n'ont pas d'id.
            if (!in_array($role, self::ALLOWED_ROLES_WITHOUT_ID) && is_null($id)) {
                throw new RuntimeException('no id field '.var_export(json_encode($json), true));
            }

            if (!in_array($role, self::ALLOWED_ROLES)) {
                throw new InvalidRoleException('Expected role "'.$role.'"');
            }
        } catch (Exception) {
            return new ApiResponse(false);
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

            /* @phpstan-ignore-next-line */
            return $this->serializer->deserialize($response->getContent(), 'App\Entity\HospitalStay[]', 'json');
        } catch (Exception $exception) {
            throw new ApiException('Erreur récupération des séjours : '.$exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @param int $doctorId
     * @return HospitalStay[]
     */
    public function getTodayPatientsForDoctor(int $doctorId): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl.'/api/doctors/'.$doctorId.'/hospital_stays/today', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->token,
                ],
            ]);

            if (200 !== $response->getStatusCode()) {
                throw new RuntimeException('Code réponse inatendu :'.$response->getStatusCode());
            }

            /* @phpstan-ignore-next-line */
            return $this->serializer->deserialize($response->getContent(), 'App\Entity\HospitalStay[]', 'json');
        } catch (Exception $exception) {
            throw new ApiException('Erreur récupération des séjours : '.$exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
