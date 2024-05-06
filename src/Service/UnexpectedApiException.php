<?php

declare(strict_types=1);


namespace App\Service;


use App\Service\ApiException;

/**
 * Class UnexpectedApiException
 * Erreur d'api inattendue.
 *
 * Il doit y avoir un dysfonctionnement.
 * N'est pas une exception liée à la validation des données.
 */
class UnexpectedApiException extends ApiException
{

}