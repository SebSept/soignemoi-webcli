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
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserHomeController extends AbstractController
{
    #[Route('/sejours', name: 'app_patient_home')]
    #[IsGranted('ROLE_PATIENT')]
    public function index(#[CurrentUser] User $user, SoigneMoiApiService $apiService): Response
    {
        $apiService->setToken($user->getToken());
        if (is_null($user->getId())) {
            throw new Exception('Id user null, doit être défini pour le patient lui même.');
        }

        return $this->render('user_home/index.html.twig', [
            'sejours' => $apiService->getHospitalStays($user->getId()),
        ]);
    }
}
