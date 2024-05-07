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
use App\Entity\Prescription;
use App\Form\Type\MedicalOpinionType;
use App\Form\Type\PrescriptionType;
use App\Service\ApiValidationException;
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
        $medicalOpinion = null;
        if (!is_null($medicalOpinionId)) {
            $medicalOpinion = $this->apiService->getMedicalOpinion($medicalOpinionId);
        }

        $form = $this->createForm(MedicalOpinionType::class, $medicalOpinion, ['patientId' => $patientId]);

        return $this->render('doctor/patients/medical_opinion.html.twig', [
            'form' => $form->createView(),
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
            $form = $this->createForm(MedicalOpinionType::class);
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

    #[Route(
        path: '/doctor/patients/today/prescription/{patientId}/{prescriptionId?}',
        name: 'app_doctor_patients_today_prescription',
        methods: ['GET']
    )]
    public function prescriptionFormEdit(int $patientId, ?int $prescriptionId = null): Response
    {
        if (!is_null($prescriptionId)) {
            $prescription = $this->apiService->getPrescription($prescriptionId);
        } else {
            $prescription = new Prescription(null, new Doctor(), new Patient($patientId), []);
        }

        $form = $this->createForm(PrescriptionType::class, $prescription);

        return $this->render('doctor/patients/prescription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/doctor/patients/today/prescription',
        name: 'app_doctor_patients_today_prescription_submit',
        methods: ['POST']
    )]
    public function prescriptionFormSubmit(Request $request): Response
    {
        try {
            $form = $this->createForm(PrescriptionType::class);
            $form->handleRequest($request);
            /** @var Prescription $prescription */
            $prescription = $form->getData();

            $this->apiService->postPrescription($prescription);

            $this->addFlash(
                'success',
                'Modifications enregistrées.'
            );
        } catch (ApiValidationException $validationException) {
            $this->addFlash(
                'danger',
                $validationException->getMessage()
            );
        } catch (Exception $exception) {
            $this->addFlash(
                'danger',
                'Erreur interne. '.$exception->getMessage() // // @todo ne pas afficher en prod
            );
        } finally {
            return $this->redirectToRoute('app_doctor_patients_today');
        }
    }
}
