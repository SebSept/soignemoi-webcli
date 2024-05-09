<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Entity;

class Prescription
{
    /**
     * @param PrescriptionItem[] $items
     */
    public function __construct(
        public ?int $id = null,
        public ?Doctor $doctor = null,
        public ?Patient $patient = null,
        public array $items = [],
        public ?\DateTime $dateTime = null,
    ) {
    }
}
