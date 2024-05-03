<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Entity;

class Patient
{
    public function __construct(
        public ?int $id = null,
        public string $fullName = '',
    ) {
    }
}
