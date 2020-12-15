<?php

declare(strict_types=1);

namespace App\Action\User\PasswordReset;

use App\Action\ActionInterface;
use App\Domain\Repository\PasswordResetTokenRepository;
use App\Domain\User\PasswordReset\Exception\PasswordResetTokenExpiredException;
use App\Domain\User\PasswordReset\PasswordResetProcessor;
use App\Domain\User\PasswordReset\Resolver\UserResolver;
use App\Form\User\PasswordResetType;
use App\Responder\WebResponder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig;

final class PasswordResetAction implements ActionInterface
{
    private PasswordResetTokenRepository $tokenRepository;
    private UserResolver $userResolver;
    private FormFactoryInterface $formFactory;
    private PasswordResetProcessor $passwordResetProcessor;
    private WebResponder $responder;

    public function __construct(
        PasswordResetTokenRepository $tokenRepository,
        UserResolver $userResolver,
        FormFactoryInterface $formFactory,
        PasswordResetProcessor $passwordResetProcessor,
        WebResponder $responder
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->userResolver = $userResolver;
        $this->formFactory = $formFactory;
        $this->passwordResetProcessor = $passwordResetProcessor;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Twig\Error\Error
     */
    public function __invoke(Request $request): Response
    {
        /** @var string $tokenString */
        $tokenString = $request->get('token');
        $token = $this->tokenRepository->findOneByToken($tokenString);
        if (null === $token) {
            throw new NotFoundHttpException("Password reset link not recognised");
        }

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        try {
            $user = $this->userResolver->resolveFromToken($token);
        } catch (PasswordResetTokenExpiredException $e) {
            $flashBag->add('danger', $e->getMessage());
            return $this->responder->createRedirectResponse('user_forgotten_password');
        }

        $form = $this->formFactory->create(PasswordResetType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->passwordResetProcessor->processPasswordReset($user, $token);

                /** @var string $email */
                $email = $user->getEmail();
                $flashBag->add('success', sprintf("Successfully changed password for %s", $email));
                return $this->responder->createRedirectResponse('root');
            }
            $flashBag->add('danger', "There were some problems with the information you provided");
        }

        return $this->responder->createTemplateResponse('form_page.html.twig', [
            'title' => "Reset your password",
            'form' => $form->createView()
        ]);
    }
}
