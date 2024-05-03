<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Entity;

class MedicalOpinion
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public int $doctorId,
        public int $patientId,
    ) {
    }
}
