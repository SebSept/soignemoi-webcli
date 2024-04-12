<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

readonly class ApiResponse
{
    public function __construct(
        public string $token,
        public bool $ok
    ) {
    }
}
