<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use App\Entity\MedicalOpinion;
use App\Form\Type\MedicalOptionType;
use App\Security\User;
use App\Service\SoigneMoiApiService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DoctorPatientsTodayController extends AbstractController
{
    #[Route('/doctor/patients/today', name: 'app_doctor_patients_today')]
    public function index(#[CurrentUser] User $user, SoigneMoiApiService $apiService): Response
    {
        // @todo injecter RequestStack|User dans le service pour l'initialiser automatiquement à chaque fois ?
        $apiService->setToken($user->getToken());
        if (is_null($user->getId())) {
            throw new Exception('Id user null, doit être défini pour le patient lui même.');
        }

        $hospitalStays = $apiService->getTodayPatientsForDoctor($user->getId());

        return $this->render('doctor/patients/today.html.twig', [
            'stays' => $hospitalStays,
        ]);
    }

    #[Route(
        path: '/doctor/patients/today/medical_opinion/{medicalOpinionId?}',
        name: 'app_doctor_patients_today_medical_opinion',
        methods: ['GET']
    )]
    public function medicalOpinionFormEdit(?int $medicalOpinionId = null): Response
    {
        // @todo recuperer l'objet via l'api
        if (!is_null($medicalOpinionId)) {
            throw new Exception('implement me : medicalOpinion non null');
            // @todo verif de cohérence avec l'utilisateur courant ?
            // @todo patientId en param ?
        }

        $medicalOpinion = new MedicalOpinion(7, 'titi', 'dec', 4, 5);

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
        $form = $this->createForm(MedicalOptionType::class);
        $form->handleRequest($request);

        // pas de validation, on laisse l'api faire le travail
        // moins de dev, pas de soucis de cohérence, par contre c'est moins réactif
        // on pourra ajouter une validation minimal dont on est sur qu'elle restera valable dans le temps.
        dump($form->getData());
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
