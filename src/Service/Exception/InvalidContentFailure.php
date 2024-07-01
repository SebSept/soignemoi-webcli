<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service\Exception;

/**
 * Class ApiValidationException.
 * Les données envoyées à l'api sont invalides.
 */
class InvalidContentFailure extends ApiException
{
}
