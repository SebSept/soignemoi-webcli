<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Entity;

class PrescriptionItem
{
    public function __construct(
        public ?int $id = null,
        public string $drug = '',
        public string $dosage = '',
    ) {
    }
}
