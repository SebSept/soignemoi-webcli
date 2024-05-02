<?php

declare(strict_types=1);

namespace App\Entity;

readonly class Patient
{
    public function __construct(
        public string $fullName
    ) { }

}