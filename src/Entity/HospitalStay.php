<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Entity;

use DateTimeInterface;

// #[ApiFilter(DateFilter::class, properties: ['startDate'])]
readonly class HospitalStay
{
    public function __construct(
        public int $id,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?DateTimeInterface $checkin,
        public ?DateTimeInterface $checkout,
        public string $reason,
        public string $medicalSpeciality,
        // public Doctor $doctor, // @todo champs rendre serialisé pour avoir le nom/prénom du doc.
    ) {
    }
}
