<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Service;

use Exception;

/**
 * @see \App\Tests\Service\SoigneMoiApiServiceTest
 */
readonly class SoigneMoiApiService
{
    public function __construct(
        private string $apiUrl
    ) {
    }

    public function authenticate(string $email, string $password): ApiResponse
    {
        throw new Exception('implement me - '.$this->apiUrl);
    }
}
