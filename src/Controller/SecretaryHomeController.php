<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecretaryHomeController extends AbstractController
{
    #[Route('/secretary/', name: 'app_secretary_home')]
    public function index(): Response
    {
        return $this->render('secretary/home.html.twig', [
            'entries' => $this->apiService->getEntriesToday(),
            'exits' => $this->apiService->getExitsToday(),
        ]);
    }

    #[Route('/secretary/checkin/{hospitalStayId}', name: 'app_hospital_stay_checkin')]
    public function checkinEntry(int $hospitalStayId): RedirectResponse
    {
        $this->apiService->checkinEntry($hospitalStayId);
        $this->addFlash('success', 'Entrée enregistrée.');

        return $this->redirectToRoute('app_secretary_home');
    }

    #[Route('/secretary/checkout/{hospitalStayId}', name: 'app_hospital_stay_checkout')]
    public function checkoutEntry(int $hospitalStayId): RedirectResponse
    {
        $this->apiService->checkoutEntry($hospitalStayId);
        $this->addFlash('success', 'Sortie enregistrée.');

        return $this->redirectToRoute('app_secretary_home');
    }
}
