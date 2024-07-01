<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthorizationFailure extends AccessDeniedException
{
}
