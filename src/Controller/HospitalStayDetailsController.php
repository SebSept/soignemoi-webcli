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

class HospitalStayDetailsController extends AbstractController
{
    public function __construct(
        private readonly SoigneMoiApiService $api)
    {
    }

    #[Route('/hospital_stay/details/{hospitalStayId}', name: 'app_hospital_stay_details')]
    public function index(int $hospitalStayId): Response
    {
        return $this->render('hospital_stay_details.html.twig', [
            'hospitalStay' => $this->api->getHospitalStayDetails($hospitalStayId),
        ]);
    }
}
