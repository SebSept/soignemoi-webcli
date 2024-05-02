<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use App\Security\User;
use App\Service\SoigneMoiApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DoctorPatientsTodayController extends AbstractController
{
    #[Route('/doctor/patients/today', name: 'app_doctor_patients_today')]
    public function index(#[CurrentUser] User $user, SoigneMoiApiService $apiService): Response
    {
        $apiService->setToken($user->getToken());
        if (is_null($user->getId())) {
            throw new \Exception('Id user null, doit être défini pour le patient lui même.');
        }

        $hospitalStays = $apiService->getTodayPatientsForDoctor($user->getId());
        return $this->render('doctor/patients/today.html.twig', [
            'stays' => $hospitalStays
        ]);
    }
}
