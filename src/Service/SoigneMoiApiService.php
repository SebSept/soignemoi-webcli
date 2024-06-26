<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

use App\Entity\Doctor;
use App\Entity\HospitalStay;
use App\Entity\MedicalOpinion;
use App\Entity\Patient;
use App\Entity\Prescription;
use App\Security\User;
use DateTime;
use DateTimeInterface;
use Exception;
use JsonException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use stdClass;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see \App\Tests\Service\SoigneMoiApiServiceTest
 */
class SoigneMoiApiService
{
    public const ALLOWED_ROLES = ['ROLE_PATIENT', 'ROLE_DOCTOR', 'ROLE_SECRETARY', 'ROLE_ADMIN'];

    public const ALLOWED_ROLES_WITHOUT_ID = ['ROLE_SECRETARY', 'ROLE_ADMIN'];

    private const API_MEDICAL_OPINIONS_GET_IRI = self::API_MEDICAL_OPINIONS_PATCH_IRI;

    private const string API_MEDICAL_OPINIONS_POST_IRI = '/api/medical_opinions';

    private const string API_MEDICAL_OPINIONS_PATCH_IRI = '/api/medical_opinions/%d';

    private const string API_PRESCRIPTIONS_GET_IRI = self::API_PRESCRIPTIONS_PATCH_IRI;

    private const string API_PRESCRIPTIONS_POST_IRI = '/api/prescriptions';

    private const string API_PRESCRIPTIONS_PATCH_IRI = '/api/prescriptions/%d';

    private const string API_PATIENTS_HOSPITAL_STAYS_GET_IRI = '/api/patients/hospital_stays';

    private const string API_DOCTORS_HOSPITAL_STAYS_GET_IRI = '/api/doctors/%d/hospital_stays/today';

    private const string API_PATIENTS_GET = '/api/patients/%d';

    private const string API_DOCTORS_GET = '/api/doctors/%d';

    private const string API_DOCTORS_GET_LIST_IRI = '/api/doctors';

    private const string API_SECRETARY_HOSPITAL_STAYS_ENTRIES_TODAY = '/api/hospital_stays/today_entries';

    private const string API_SECRETARY_HOSPITAL_STAYS_EXITS_TODAY = '/api/hospital_stays/today_exits';

    private const string API_HOSPITAL_STAY_DETAILS = '/api/hospital_stays/%d';

    private const string API_HOSPITAL_STAYS_PATCH_IRI = self::API_HOSPITAL_STAY_DETAILS;

    private const string API_HOSPITAL_STAYS_POST_IRI = '/api/hospital_stays';

    private string $token;

    private int $userId;

    private readonly Serializer $serializer;

    public function __construct(
        private readonly Security $security,
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl, // chaine sans 'api/' car on fait la demande de token au dessous.
        #[Autowire(service: 'monolog.logger.api_errors')]
        private readonly LoggerInterface $apiErrorsLogger,
    ) {
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(propertyTypeExtractor: new ReflectionExtractor()),
                new DateTimeNormalizer(),
                new ArrayDenormalizer(),
            ],
            [new JsonEncoder()]);

        // token et userId pas initialisés ici car Security n'a pas encore le User au moment de la construction du service.
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
                $this->apiErrorsLogger->critical(
                    'Essait d\'authentification ratée.', [
                        'responseCode' => $response->getStatusCode(),
                        'responseContent' => $response->getContent(),
                    ]);

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

        // $this->token = $token;

        return new ApiResponse(true, $token, $role, $id);
    }

    /**
     * @return HospitalStay[]
     */
    public function getPatientHospitalStays(?int $patientId = null): array
    {
        $patientId ??= $this->getUserId();

        /* @phpstan-ignore-next-line */
        return $this->getRequest(
            self::API_PATIENTS_HOSPITAL_STAYS_GET_IRI,
            $patientId,
            HospitalStay::class.'[]'); /* @phpstan-ignore-line */
    }

    /**
     * @return HospitalStay[]
     */
    public function getTodayHospitalStaysForDoctor(?int $doctorId = null): array
    {
        $doctorId ??= $this->getUserId();

        /* @phpstan-ignore-next-line */
        return $this->getRequest(
            self::API_DOCTORS_HOSPITAL_STAYS_GET_IRI,
            $doctorId,
            HospitalStay::class.'[]'); /* @phpstan-ignore-line */
    }

    public function postMedicalOpinion(MedicalOpinion $medicalOpinion): void
    {
        if (isset($medicalOpinion->id)) {
            $this->patchRequest(
                self::API_MEDICAL_OPINIONS_PATCH_IRI,
                $medicalOpinion->id,
                [
                    'title' => $medicalOpinion->title,
                    'description' => $medicalOpinion->description,
                ]);
        } else {
            $this->postRequest(
                self::API_MEDICAL_OPINIONS_POST_IRI,
                [
                    'patient' => $this->getPatientIri($medicalOpinion->patient),
                    'doctor' => $this->getDoctorIriAsCurrentUser(),
                    'title' => $medicalOpinion->title,
                    'description' => $medicalOpinion->description,
                ]);
        }
    }

    public function getMedicalOpinion(int $medicalOpinionId): MedicalOpinion
    {
        return $this->getRequest(
            self::API_MEDICAL_OPINIONS_GET_IRI,
            $medicalOpinionId,
            MedicalOpinion::class
        );
    }

    public function getPrescription(int $prescriptionId): Prescription
    {
        return $this->getRequest(
            self::API_PRESCRIPTIONS_GET_IRI,
            $prescriptionId,
            Prescription::class
        );
    }

    public function postPrescription(Prescription $prescription): void
    {
        if (isset($prescription->id)) {
            $this->patchRequest(
                self::API_PRESCRIPTIONS_PATCH_IRI,
                $prescription->id,
                [
                    'items' => $prescription->items,
                ]);
        } else {
            $this->postRequest(
                self::API_PRESCRIPTIONS_POST_IRI,
                [
                    'doctor' => $this->getDoctorIriAsCurrentUser(),
                    'patient' => $this->getPatientIri($prescription->patient),
                    'items' => $prescription->items,
                ]);
        }
    }

    /**
     * @return HospitalStay[]
     */
    public function getEntriesToday(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->getRequest(
            url: self::API_SECRETARY_HOSPITAL_STAYS_ENTRIES_TODAY,
            type: HospitalStay::class.'[]', /* @phpstan-ignore-line */
            id: 0);
    }

    /**
     * @return HospitalStay[]
     */
    public function getExitsToday(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->getRequest(
            url: self::API_SECRETARY_HOSPITAL_STAYS_EXITS_TODAY,
            type: HospitalStay::class.'[]', /* @phpstan-ignore-line */
            id: 0);
    }

    public function getHospitalStayDetails(int $hospitalStayId): HospitalStay
    {
        return $this->getRequest(self::API_HOSPITAL_STAY_DETAILS, $hospitalStayId, HospitalStay::class);
    }

    public function checkinEntry(int $hospitalStayId): void
    {
        $this->patchRequest(
            self::API_HOSPITAL_STAYS_PATCH_IRI,
            $hospitalStayId,
            ['checkin' => (new DateTime())->format('c')]
        );
    }

    public function checkoutEntry(int $hospitalStayId): void
    {
        $this->patchRequest(
            self::API_HOSPITAL_STAYS_PATCH_IRI,
            $hospitalStayId,
            ['checkout' => (new DateTime())->format('c')]
        );
    }

    /**
     * @return Doctor[]
     */
    public function getDoctors(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->getRequest(self::API_DOCTORS_GET_LIST_IRI, 0, Doctor::class.'[]');
    }

    public function postHospitalStay(HospitalStay $hospitalStay): void
    {
        // nécessite la désactivation d'une régle rector
        // https://github.com/symplify/phpstan-rules/blob/main/docs/rules_overview.md#checktypehintcallertyperule
        $data = (array) $hospitalStay;

        $data['patient'] = $this->getPatientIriAsCurrentUser();
        $data['doctor'] = $this->getDoctorIri($data['doctor']);
        $data['startDate'] = $this->formatDate($data['startDate']);
        $data['endDate'] = $this->formatDate($data['endDate']);

        $this->postRequest(
            self::API_HOSPITAL_STAYS_POST_IRI,
            $data
        );
    }

    private function getToken(): string
    {
        if (!isset($this->token) || ('' === $this->token || '0' === $this->token)) {
            /** @var ?User $user */
            $user = $this->security->getUser();
            $token = $user?->getToken() ?? '';
            if (empty($token)) {
                throw new AccessDeniedException();
            }

            $this->token = $token;
        }

        return $this->token;
    }

    private function getUserId(): int
    {
        if (!isset($this->userId)) {
            /** @var ?User $user */
            $user = $this->security->getUser();
            $userId = $user?->getId() ?? null;
            if (is_null($userId)) {
                throw new AccessDeniedException();
            }

            $this->userId = $userId;
        }

        return $this->userId;
    }

    /**
     * @template T
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    private function getRequest(string $url, int $id, string $type): mixed
    {
        $response = $this->httpClient->request(
            'GET',
            $this->apiUrl.sprintf($url, $id),
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$this->getToken(),
                ],
            ]);

        $this->handleNonOkResponse($response, 'GET', $this->apiUrl.sprintf($url, $id));

        return $this->serializer->deserialize($response->getContent(), $type, 'json');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function patchRequest(string $url, int $id, array $data): void
    {
        $response = $this->httpClient->request('PATCH', $this->apiUrl.sprintf($url, $id), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->getToken(),
            ],
            'json' => $data,
        ]);

        $this->handleNonOkResponse($response, 'PATCH', [$this->apiUrl.sprintf($url, $id), $data]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function postRequest(string $url, array $data): void
    {
        $response = $this->httpClient->request('POST', $this->apiUrl.$url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->getToken(),
            ],
            'json' => $data,
        ]);

        $this->handleNonOkResponse($response, 'POST', [$url, $data]);
    }

    private function getPatientIri(?Patient $patient): string
    {
        if (is_null($patient)) {
            throw new Exception('Patient non défini');
        }

        return sprintf(self::API_PATIENTS_GET, $patient->id);
    }

    private function getDoctorIri(?Doctor $doctor): string
    {
        if (is_null($doctor)) {
            throw new Exception('Docteur non défini');
        }

        return sprintf(self::API_DOCTORS_GET, $doctor->id);
    }

    private function getDoctorIriAsCurrentUser(): string
    {
        return sprintf(self::API_DOCTORS_GET, $this->getUserId());
    }

    private function getPatientIriAsCurrentUser(): string
    {
        return sprintf(self::API_PATIENTS_GET, $this->getUserId());
    }

    private function formatDate(?DateTimeInterface $dateTime): string
    {
        if (is_null($dateTime)) {
            throw new Exception('Date ne peut être null');
        }

        return $dateTime->format('c');
    }

    /**
     * @param string|array<int, mixed> $payload
     */
    private function handleNonOkResponse(
        ResponseInterface $response,
        string $method,
        string|array $payload): void
    {
        $statusCode = $response->getStatusCode();
        $responseContent = $response->getContent(false); // false pour ne pas lever d'exception.
        try {
            $jsonResponseContent = json_decode($responseContent, flags: JSON_THROW_ON_ERROR);
            if (is_scalar($jsonResponseContent)) {
                $jsonResponseContent = new stdClass();
            }
        } catch (JsonException) {
            $jsonResponseContent = new stdClass();
        }

        // Inspiré de \Symfony\Component\HttpClient\Response\CommonResponseTrait::checkStatusCode
        // les réponses inférieures à 300 sont considérées comme des succès.
        if ($statusCode < 300) {
            return;
        }

        // log la requete fautive
        // @todo par la suite ne pas logger les requetes liées aux autorisations
        $this->apiErrorsLogger->critical('Erreur API {statusCode}. ', [
            'statusCode' => $statusCode,
            'responseContent' => $responseContent,
            'method' => $method,
            'requestPayload' => $payload,
        ]);

        // 400 - erreur de validation avec message
        if (Response::HTTP_BAD_REQUEST === $statusCode) {
            throw new ApiValidationException('Erreur de validation : requete incorrecte'.$jsonResponseContent->detail);
        }

        // erreur du validation Symfony - 422
        if (Response::HTTP_UNPROCESSABLE_ENTITY === $statusCode) {
            //            dd($responseContent);
            // @todo vérifier les contenus
            throw new ApiValidationException('Erreur de validation (2) : '.json_decode($responseContent, flags: JSON_THROW_ON_ERROR)->detail);
        }

        // non loggé - 401
        if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode()) {
            throw new BadCredentialsException();
        }

        // non authorisé (loggé) - 403
        if (Response::HTTP_FORBIDDEN === $response->getStatusCode()) {
            throw new AccessDeniedException('Accès api interdit.');
        }

        throw new RuntimeException('Code réponse inatendu :'.$response->getStatusCode());
    }
}
