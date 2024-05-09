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

readonly class HospitalStay
{
    /**
     * @param Prescription[] $prescriptions
     */
    public function __construct(
        public int $id,
        public DateTimeInterface $startDate,
        public DateTimeInterface $endDate,
        public ?DateTimeInterface $checkin,
        public ?DateTimeInterface $checkout,
        public string $reason,
        public string $medicalSpeciality,
        public Patient $patient,
        public ?Prescription $todayPrescription,
        public ?MedicalOpinion $todayMedicalOpinion,
        public array $prescriptions = [],
    ) {
    }
}
