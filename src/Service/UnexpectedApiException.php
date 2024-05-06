<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

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
