<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Action\ActionInterface;
use App\Responder\WebResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Error\Error as TwigError;

final class LoginAction implements ActionInterface
{
    private AuthenticationUtils $authenticationUtils;
    private WebResponder $responder;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        WebResponder $responder
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws TwigError
     */
    public function __invoke(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        if (!is_null($error)) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getFlashBag();
            $flashBag->add('danger', $error->getMessageKey());
        }
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->responder->createTemplateResponse('user/login.html.twig', [
            'title' => 'Log in',
            'last_username' => $lastUsername,
        ]);
    }
}
