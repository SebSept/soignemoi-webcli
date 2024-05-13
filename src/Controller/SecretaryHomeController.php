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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecretaryHomeController extends AbstractController
{
    public function __construct(
        private readonly SoigneMoiApiService $api)
    {
    }

    #[Route('/secretary/', name: 'app_secretary_home')]
    public function index(): Response
    {
        return $this->render('secretary/home.html.twig', [
            'entries' => $this->api->getEntriesToday(),
            'exits' => $this->api->getExitsToday(),
        ]);
    }

    #[Route('/secretary/checkin/{hospitalStayId}', name: 'app_hospital_stay_checkin')]
    public function checkinEntry(int $hospitalStayId): RedirectResponse
    {
        $this->api->checkinEntry($hospitalStayId);
        $this->addFlash('success', 'Entrée enregistrée.');

        return $this->redirectToRoute('app_secretary_home');
    }
}
