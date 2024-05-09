<?php

declare(strict_types=1);

/*
 * SoigneMoi Webcli - Projet ECF
 *
 * @author Sébastien Monterisi <sebastienmonterisi@gmail.com>
 * 2024
 */

namespace App\Security;

use App\Service\SoigneMoiApiService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see \App\Tests\Security\SoigneMoiApiAuthenticatorTest
 */
class SoigneMoiApiAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SoigneMoiApiService $api,
        private Security $security,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');
        $password = $request->getPayload()->getString('password');
        $_csrf_token = $request->getPayload()->getString('_csrf_token');

        if ('' === $email || '0' === $email || ('' === $password || '0' === $password)) {
            throw new AuthenticationException('Données manquantes');
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        /*
         * Le passport n'est pas un document qui permet seulment de s'authentifier,
         * il CONTIENT aussi le user.
         *
         * - il permet aussi de construire le User
         *  - soit en interne au travers de 2 fonctions :
         *      - le callback passé second paramètre de UserBadge (le param est l'identifiant de l'utilisateur))
         *      - puis le callback passé premier paramètre de PasswordCredentials (le param est le mot de passe de l'utilisateur)
         */
        return new Passport(
            // le user peut être invalide à ce moment, le passeport vérifie la validité avec le second paramètre ($credentials)
            new UserBadge($email, static fn ($email): User => new User($email)),
            new CustomCredentials(
                function ($password, User $user): bool {
                    $response = $this->api->authenticateUser($user->getEmail(), $password);

                    // données a persister dans la session pour les appels api à venir.
                    $user->setToken($response->token);
                    $user->setRoles([$response->role]);
                    $user->setId($response->id);

                    return $response->ok;
                },
                $password),
            [
                // 'authenticate' est la chaine définie dans le form twig
                // la validation du token est faite automatiquement en interne
                new CsrfTokenBadge('authenticate', $_csrf_token),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName): ?Response
    {
        /** @var UserInterface $user */
        $user = $token->getUser();
        // on suppose un role unique
        $redirectedTo = match ($user->getRoles()[0]) {
            'ROLE_DOCTOR' => 'app_doctor_patients_today',
            'ROLE_SECRETARY' => 'app_secretary_home',
            'ROLE_ADMIN' => 'app_admin_home',
            'ROLE_PATIENT' => 'app_patient_home',
            default => '/',
        };

        return new RedirectResponse($this->urlGenerator->generate($redirectedTo));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    /**
     * Lancement login.
     *
     * Méthode de AuthenticationEntryPointInterface
     * utilisée quand on a emis une exception AuthenticationException
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if ($this->security->getUser() instanceof UserInterface) {
            $this->security->logout(false);
        }
        $url = $this->getLoginUrl($request);

        return new RedirectResponse($url);
    }
}
