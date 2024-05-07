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

class UserHomeController extends AbstractController
{
    #[Route('/patient/sejours', name: 'app_patient_home')]
    public function index(SoigneMoiApiService $apiService): Response
    {
        return $this->render('user_home/index.html.twig', [
            'sejours' => $apiService->getPatientHospitalStays(),
        ]);
    }
}
