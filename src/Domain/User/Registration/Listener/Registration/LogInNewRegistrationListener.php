<?php

declare(strict_types=1);

namespace App\Domain\User\Registration\Listener\Registration;

use App\Domain\User\Registration\Event\RegistrationEvent;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LogInNewRegistrationListener
{
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;

    public function __construct(TokenStorageInterface $tokenStorage, RequestStack $requestStack)
    {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public function __invoke(RegistrationEvent $event)
    {
        $token = new UsernamePasswordToken($event->getUser(), null, 'main', $event->getUser()->getRoles());
        $this->tokenStorage->setToken($token);
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            throw new RuntimeException("No master request");
        }
        $session = $request->getSession();
        $session->set('_security_main', $token);
    }
}
