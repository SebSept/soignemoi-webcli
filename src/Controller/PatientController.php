<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use App\Entity\HospitalStay;
use App\Form\Type\HospitalStayType;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PatientController extends AbstractController
{
    #[Route('/patient/sejours', name: 'app_patient_home')]
    public function index(): Response
    {
        return $this->render('patient/home.html.twig', [
            'sejours' => $this->apiService->getPatientHospitalStays(),
        ]);
    }

    // @todo on peut se passer de ce paramètre et prend le user en cours
    #[Route(
        path: '/patient/hospital_stay_form/{patientId}',
        name: 'app_patient_hospital_stay_edit',
        methods: ['GET'], )]
    public function hospitalStayFormEdit(int $patientId): Response
    {
        $form = $this->createForm(
            type: HospitalStayType::class,
            options: ['patientId' => $patientId, 'doctors' => $this->apiService->getDoctors()],
        );

        return $this->render('patient/hospital_stay_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: '/patient/hospital_stay_form',
        name: 'app_patient_hospital_stay_edit_submit',
        methods: ['POST'],
    )]
    public function hospitalStayFormSubmit(Request $request): Response
    {
        try {
            $form = $this->createForm(
                type: HospitalStayType::class,
                options: ['doctors' => $this->apiService->getDoctors()],
            );
            $form->handleRequest($request);
            /** @var HospitalStay $hospitalStay */
            $hospitalStay = $form->getData();

            $this->apiService->postHospitalStay($hospitalStay);

            $this->addFlash(
                'success',
                'Demande de séjour enregistrée.'
            );

            return $this->redirectToRoute('app_patient_home');
        } catch (Exception $exception) {
            $this->addFlash(
                'danger',
                "une erreur c'est produite. ".$exception->getMessage()
            );

            return $this->redirectToRoute('app_patient_home');
        }
    }
}
