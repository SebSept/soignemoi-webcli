<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Objet non trouvé.
 * Exception qui hérite de l'exception native pour capture par le framework
 * et affichage d'une erreur 404.
 */
class NotFoundFailure extends NotFoundHttpException
{
}
