<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use App\Entity\Doctor;
use App\Entity\MedicalOpinion;
use App\Entity\Patient;
use App\Form\Type\MedicalOptionType;
use App\Service\SoigneMoiApiService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DoctorPatientsTodayController extends AbstractController
{
    public function __construct(
        private readonly SoigneMoiApiService $apiService)
    {
    }

    #[Route('/doctor/patients/today', name: 'app_doctor_patients_today')]
    public function index(): Response
    {
        return $this->render('doctor/patients/today.html.twig', [
            'stays' => $this->apiService->getTodayHospitalStaysForDoctor(),
        ]);
    }

    #[Route(
        path: '/doctor/patients/today/medical_opinion/{patientId}/{medicalOpinionId?}',
        name: 'app_doctor_patients_today_medical_opinion',
        methods: ['GET']
    )]
    public function medicalOpinionFormEdit(int $patientId, ?int $medicalOpinionId = null): Response
    {
        if (!is_null($medicalOpinionId)) {
            $medicalOpinion = $this->apiService->getMedicalOpinion($medicalOpinionId);
        // @todo verif de cohérence avec l'utilisateur courant ? On a pas un test qui vérifie que le medecin peut récupérér les ids des ses propres opinions ?
        } else {
            $medicalOpinion = new MedicalOpinion(null, '', '', new Doctor(null), new Patient($patientId)); // @todo faire plutot une option dans MedicalType
        }

        $form = $this->createForm(MedicalOptionType::class, $medicalOpinion);

        return $this->render('doctor/patients/medical_opinion.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(
        path: '/doctor/patients/today/medical_opinion',
        name: 'app_doctor_patients_today_medical_opinion_submit',
        methods: ['POST']
    )]
    public function medicalOpinionFormSubmit(Request $request): Response
    {
        try {
            $form = $this->createForm(MedicalOptionType::class);
            $form->handleRequest($request);
            /** @var MedicalOpinion $medicalOpinion */
            $medicalOpinion = $form->getData();

            $this->apiService->postMedicalOpinion($medicalOpinion);

            $this->addFlash(
                'success',
                'Modifications enregistrées.'
            );

            return $this->redirectToRoute('app_doctor_patients_today');
        } catch (Exception $exception) {
            $this->addFlash(
                'danger',
                "une erreur c'est produite. ".$exception->getMessage()
            );

            return $this->redirectToRoute('app_doctor_patients_today');
        }
    }
}
