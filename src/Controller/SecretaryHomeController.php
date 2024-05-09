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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecretaryHomeController extends AbstractController
{
    #[Route('/secretary/', name: 'app_secretary_home')]
    public function index(SoigneMoiApiService $api): Response
    {
        return $this->render('secretary/home.html.twig', [
            'entries' => $api->getEntriesToday(),
            'exits' => [], // $api->getExitsToday(),
        ]);
    }
}
