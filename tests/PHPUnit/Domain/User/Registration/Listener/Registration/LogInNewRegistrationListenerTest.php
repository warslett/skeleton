<?php

declare(strict_types=1);

namespace App\Tests\PHPUnit\Domain\User\Registration\Listener\Registration;

use App\Domain\Entity\RegistrationToken;
use App\Domain\Entity\User;
use App\Domain\User\Registration\Event\RegistrationEvent;
use App\Domain\User\Registration\Listener\Registration\LogInNewRegistrationListener;
use App\Tests\PHPUnit\TestCase;
use Mockery as m;
use Mockery\Mock;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LogInNewRegistrationListenerTest extends TestCase
{

    public function testInvokeSetsUsernameAndPasswordTokenOnTokenStorage(): void
    {
        $tokenStorage = $this->mockTokenStorage();
        $request = $this->mockRequest($this->mockSession());
        $listener = new LogInNewRegistrationListener($tokenStorage, $this->mockRequestStack($request));
        $userRoles = ['ROLE_USER'];
        $user = $this->mockUser($userRoles);

        $listener(new RegistrationEvent($user, $this->mockRegistrationToken()));

        $tokenStorage->shouldHaveReceived('setToken')
            ->once()
            ->with(m::on(function (TokenInterface $token) use ($user, $userRoles): bool {
                $this->assertInstanceOf(UsernamePasswordToken::class, $token);
                /** @var UsernamePasswordToken $token */
                $this->assertSame($user, $token->getUser());
                $this->assertNull($token->getCredentials());
                $this->assertSame('main', $token->getFirewallName());
                $this->assertSame($userRoles, $token->getRoleNames());
                return true;
            }))
        ;
    }

    public function testInvokeNoMasterRequestThrowsException(): void
    {
        $listener = new LogInNewRegistrationListener($this->mockTokenStorage(), $this->mockRequestStack(null));
        $userRoles = ['ROLE_USER'];
        $user = $this->mockUser($userRoles);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No master request');

        $listener(new RegistrationEvent($user, $this->mockRegistrationToken()));
    }

    public function testInvokeSetsSerializedTokenOnSession(): void
    {
        $session = $this->mockSession();
        $request = $this->mockRequest($session);
        $listener = new LogInNewRegistrationListener($this->mockTokenStorage(), $this->mockRequestStack($request));
        $userRoles = ['ROLE_USER'];
        $user = $this->mockUser($userRoles);

        $listener(new RegistrationEvent($user, $this->mockRegistrationToken()));

        $session->shouldHaveReceived('set')
            ->once()
            ->with('_security_main', m::on(function (TokenInterface $token) use ($user, $userRoles): bool {
                $this->assertInstanceOf(UsernamePasswordToken::class, $token);
                /** @var UsernamePasswordToken $token */
                $this->assertSame($user, $token->getUser());
                $this->assertNull($token->getCredentials());
                $this->assertSame('main', $token->getFirewallName());
                $this->assertSame($userRoles, $token->getRoleNames());
                return true;
            }))
        ;
    }

    /**
     * @return TokenStorageInterface&Mock
     */
    private function mockTokenStorage(): TokenStorageInterface
    {
        $tokenStorage =  m::mock(TokenStorageInterface::class);
        $tokenStorage->shouldReceive('setToken');
        return $tokenStorage;
    }

    /**
     * @return SessionInterface&Mock
     */
    private function mockSession(): SessionInterface
    {
        $session = m::mock(SessionInterface::class);
        $session->shouldReceive('set');
        return $session;
    }

    private function mockRequest(SessionInterface $session): Request
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getSession')->andReturn($session);
        return $request;
    }

    /**
     * @param Request|null $request
     * @return RequestStack&Mock
     */
    private function mockRequestStack(?Request $request): RequestStack
    {
        $requestStack = m::mock(RequestStack::class);
        $requestStack->shouldReceive('getMasterRequest')->andReturn($request);
        return $requestStack;
    }

    /**
     * @param array $userRoles
     * @return User&Mock
     */
    private function mockUser(array $userRoles): User
    {
        $user = m::mock(User::class);
        $user->shouldReceive('getRoles')->andReturn($userRoles);
        return $user;
    }

    /**
     * @return RegistrationToken&Mock
     */
    private function mockRegistrationToken(): RegistrationToken
    {
        return m::mock(RegistrationToken::class);
    }
}
