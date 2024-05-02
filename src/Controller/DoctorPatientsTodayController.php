<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DoctorPatientsTodayController extends AbstractController
{
    #[Route('/doctor/patients/today', name: 'app_doctor_patients_today')]
    public function index(): Response
    {
        return $this->render('doctor/patients/today.html.twig', [
        ]);
    }
}
