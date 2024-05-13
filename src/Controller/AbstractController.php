<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use App\Service\SoigneMoiApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as NativeAbstractControllerAlias;

class AbstractController extends NativeAbstractControllerAlias
{
    public function __construct(
        protected readonly SoigneMoiApiService $apiService)
    {
    }
}
