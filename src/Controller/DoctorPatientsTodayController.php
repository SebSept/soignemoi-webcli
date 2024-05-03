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
            'stays' => $this->apiService->getTodayPatientsForDoctor(),
        ]);
    }

    #[Route(
        path: '/doctor/patients/today/medical_opinion/{patientId}/{medicalOpinionId?}',
        name: 'app_doctor_patients_today_medical_opinion',
        methods: ['GET']
    )]
    public function medicalOpinionFormEdit(int $patientId, ?int $medicalOpinionId = null): Response
    {
        // @todo recuperer l'objet via l'api
        if (!is_null($medicalOpinionId)) {
            throw new Exception('implement me : medicalOpinion non null');
        // @todo verif de cohérence avec l'utilisateur courant ?
        // @todo patientId en param ?
        } else {
            $medicalOpinion = new MedicalOpinion(null, '', '', new Doctor(null), new Patient($patientId)); // @todo récupérer l'id de l'url
        }

        $form = $this->createForm(MedicalOptionType::class, $medicalOpinion); // // @todo passer une option avec juste l'id patient ?

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
        $form = $this->createForm(MedicalOptionType::class);
        $form->handleRequest($request);
        /** @var MedicalOpinion $medicalOpinion */
        $medicalOpinion = $form->getData();

        $this->apiService->postMedicalOpinion($medicalOpinion);

        // pas de validation, on laisse l'api faire le travail
        // moins de dev, pas de soucis de cohérence, par contre c'est moins réactif
        // on pourra ajouter une validation minimal dont on est sur qu'elle restera valable dans le temps.
        $this->addFlash(
            'success',
            'Your changes were saved!'
        );
        $this->addFlash(
            'danger',
            'oops'
        );

        return $this->redirectToRoute('app_doctor_patients_today_medical_opinion');
    }
}
