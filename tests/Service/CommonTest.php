<?php

namespace App\Tests\Service;

use DateTime;
use App\Entity\Doctor;
use App\Entity\HospitalStay;
use App\Service\Exception\AuthenticationFailure;
use App\Service\Exception\AuthorizationFailure;
use App\Service\Exception\InvalidContentFailure;
use App\Service\Exception\NotFoundFailure;
use App\Service\Exception\UnexpectedApiFailure;
use App\Service\SoigneMoiApiService;
use App\Tests\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

class CommonTest extends KernelTestCase
{

    /**
     * Test que les requetes de type GET échouent proprement si le contenu reçu n'est pas du json.
     * Ce test couvre toutes les requetes get de l'api (getPatientHospitalStay, etc).
     * Toutes les requêtes get présentées par l'api sont couvertes car en interne
     * on utilise la même méthode getRequest() qui s'occupe de cette gestion d'erreur.
     */
    public function testGetRequestThrowsUnexpectedApiFailureNoJsonResponse(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_not_json.json');
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Expect/Assert
        $this->expectException(UnexpectedApiFailure::class);

        // Acte
        $api->getPatientHospitalStays();
    }

    public function testGetRequestReturnsArrayOfEntities(): void
    {
        $this->prepareHttpResponse('response_hospital_stays.json');
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        $hospitalStays = $api->getPatientHospitalStays();

        $this->assertIsArray($hospitalStays);
        $this->assertContainsOnlyInstancesOf(HospitalStay::class, $hospitalStays);
    }

    /**
     * 400 - HTTP_BAD_REQUEST  - InvalidContentFailure
     * Exemple de requete concerné : noter le abc à la place de l'id de page
     * curl -X 'GET' \
     * 'http://localhost:32772/api/doctors?page=abc' \
     * -H 'accept: application/json' \
     * -H 'Authorization: Bearer this-is-a-valid-token-value-patient'
     */
    public function testGetRequestThrowsInvalidContentFailureCode400Response(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_apip_error_400.json', httpCode: Response::HTTP_BAD_REQUEST);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Expect/Assert
        $this->expectException(InvalidContentFailure::class);

        // Act
        $api->getPatientHospitalStays();
    }

    /**
     * 400 - HTTP_BAD_REQUEST  - InvalidContentFailure
     * Exemple de requete concerné : noter le abc à la place de l'id de page
     * curl -X 'GET' \
     * * 'http://localhost:32772/api/hospital_stays/abc' \
     * * -H 'accept: application/json' \
     * * -H 'Authorization: Bearer this-is-a-valid-token-value'
     */
    public function testGetRequestThrowsNotFoundFailureCode404Response(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_apip_error_404.json', httpCode: Response::HTTP_NOT_FOUND);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Expect/Assert
        $this->expectException(NotFoundFailure::class);

        // Act
        $api->getPatientHospitalStays();
    }

    /**
     * 401 - HTTP_UNAUTHORIZED - AuthenticationFailure
     * Exemple de requete concerné
     * curl -X 'GET' \
     * 'http://localhost:32772/api/hospital_stays/1' \
     * -H 'accept: application/json' \
     * -H 'Authorization: Bearer this-is-NOT-a-valid-token-value'
     */
    public function testGetRequestThrowsAuthenticationFailureCode401Response(): void
    {
        // Arrange
        $this->prepareHttpResponse(
            'response_apip_error_401.json',
            httpCode: Response::HTTP_UNAUTHORIZED,
            headers: ['WWW-Authenticate: Bearer error="invalid_token",error_description="Invalid credentials."']
        );
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Expect/Assert
        $this->expectException(AuthenticationFailure::class);

        // Act
        $api->getPatientHospitalStays();
    }

    /**
     * 403 - HTTP_FORBIDDEN - AuthorizationFailure
     * Exemple de requete concerné
     * curl -I \
     * 'http://localhost:32772/api/doctors' \
     * -H 'accept: application/json' \
     * -H 'Authorization: Bearer this-is-a-valid-token-value-secretary'
     */
    public function testGetRequestThrowsAuthorizationFailureCode403Response(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_apip_error_403.json', httpCode: Response::HTTP_FORBIDDEN);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);

        // Expect/Assert
        $this->expectException(AuthorizationFailure::class);

        // Act
        $api->getDoctors();
    }

    /**
     * 422 - HTTP_UNPROCESSABLE_ENTITY - InvalidContentFailure
     * Exemple de requete concerné
     * curl -I \
     * 'http://localhost:32772/api/doctors' \
     * -H 'accept: application/json' \
     * -H 'Authorization: Bearer this-is-a-valid-token-value-secretary'
     */
    public function testGetRequestThrowsInvalidContentFailureCode422Response(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_apip_error_422.json', httpCode: Response::HTTP_UNPROCESSABLE_ENTITY);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $hospitalStay = new HospitalStay(
            id:null,
            startDate: new DateTime('2025-12-07'),
            endDate: new DateTime('2025-12-14'),
//            medicalSpeciality: 'la specialite',
//            reason: 'une raison valable',
            doctor: new Doctor(4)
        );

        // Expect/Assert
        $this->expectException(InvalidContentFailure::class);

        // Act
        $api->postHospitalStay($hospitalStay);
    }

    /**
     * 422 - HTTP_UNPROCESSABLE_ENTITY - InvalidContentFailure
     * Exemple de requete concerné
     * curl -I \
     * 'http://localhost:32772/api/doctors' \
     * -H 'accept: application/json' \
     * -H 'Authorization: Bearer this-is-a-valid-token-value-secretary'
     */
    public function testGetRequestThrowsUnexpectedApiFailure500Response(): void
    {
        // Arrange
        $this->prepareHttpResponse('response_apip_error_500.json', httpCode: Response::HTTP_INTERNAL_SERVER_ERROR);
        static::getContainer()->set(Security::class, $this->getMockedSecurity());

        /** @var SoigneMoiApiService $api */
        $api = static::getContainer()->get(SoigneMoiApiService::class);
        $hospitalStay = new HospitalStay(
            id:null,
            startDate: new DateTime('2025-12-07'),
            endDate: new DateTime('2025-12-14'),
//            medicalSpeciality: 'la specialite',
//            reason: 'une raison valable',
            doctor: new Doctor(4)
        );

        // Expect/Assert
        $this->expectException(UnexpectedApiFailure::class);

        // Act
        $api->postHospitalStay($hospitalStay);
    }

    /**
     * @todo tester les autres cas des requetes post
     */

    /** a tester
     * -> #[ApiResource(
     * collectDenormalizationErrors: true
     * )] - https://api-platform.com/docs/core/validation/#collecting-denormalization-errors
     */


}
