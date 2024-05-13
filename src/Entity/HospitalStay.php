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

class HospitalStay
{
    /**
     * @param Prescription[]   $prescriptions
     * @param MedicalOpinion[] $medicalOpinions
     */
    public function __construct(
        public ?int $id = null,
        public ?DateTimeInterface $startDate = null,
        public ?DateTimeInterface $endDate = null,
        public ?DateTimeInterface $checkin = null,
        public ?DateTimeInterface $checkout = null,
        public string $reason = '',
        public string $medicalSpeciality = '',
        public ?Patient $patient = null,
        public ?Doctor $doctor = null,
        public ?Prescription $todayPrescription = null,
        public ?MedicalOpinion $todayMedicalOpinion = null,
        public array $prescriptions = [],
        public array $medicalOpinions = [],
    ) {
    }
}
