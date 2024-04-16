<?php

namespace App\Tests\Security;

use App\Security\SoigneMoiApiAuthenticator;
use App\Service\ApiResponse;
use App\Service\SoigneMoiApiService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class SoigneMoiApiAuthenticatorTest extends TestCase
{
    /**
     * @return void
     * On ne teste  pas l'authentification elle même ici, mais la construction du Passport, que les données soiient valides ou pas,
     * l'authentification n'as pas lieu. (closure de CredentialsInterface pas executée).
     */
    public function testSuccessfulAuthentication(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $api = $this->createMock(SoigneMoiApiService::class);
        $tokenReturnedByApi = 'fake-token';
//        $api->method('authenticate')->willReturn(new ApiResponse($tokenReturnedByApi, true));
        $api->method('authenticatePatient')->willReturn(new ApiResponse($tokenReturnedByApi, false));

        $request = $this->createMock(Request::class);
        $request->method('getPayload')->willReturn(new InputBag(
            ["email" => "sebastienmonterisi@gmail.com",
                "password" => "a",
                "_csrf_token" => 'whatever'
                ]));
        $request->method('getSession')
            ->willReturn($this->createMock(SessionInterface::class));

        $authenticator = new SoigneMoiApiAuthenticator($urlGenerator, $api);
        $passport = $authenticator->authenticate($request);
        $this->assertInstanceOf(Passport::class, $passport);
    }
}
