<?php

declare(strict_types=1);


namespace App\Entity;


readonly class Prescription
{
    public function __construct(
        public int $id
    ) { }

}