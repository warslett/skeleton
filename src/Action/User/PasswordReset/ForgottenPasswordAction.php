<?php

declare(strict_types=1);

namespace App\Action\User\PasswordReset;

use App\Action\ActionInterface;
use App\Domain\User\PasswordReset\PasswordResetInitiator;
use App\Form\User\ForgottenPasswordType;
use App\Responder\WebResponder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig;

final class ForgottenPasswordAction implements ActionInterface
{
    private FormFactoryInterface $formFactory;
    private PasswordResetInitiator $passwordResetInitiator;
    private WebResponder $responder;

    public function __construct(
        FormFactoryInterface $formFactory,
        PasswordResetInitiator $passwordResetInitiator,
        WebResponder $responder
    ) {
        $this->formFactory = $formFactory;
        $this->passwordResetInitiator = $passwordResetInitiator;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Twig\Error\Error
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(ForgottenPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getFlashBag();

            if ($form->isValid()) {
                /** @var string $email */
                $email = $form->get('email')->getData();
                $this->passwordResetInitiator->initiatePasswordReset($email);

                $flashBag->add('success', "Please check your emails to reset your password");
                return $this->responder->createRedirectResponse('root');
            }
            $flashBag->add('danger', "There were some problems with the information you provided");
        }

        return $this->responder->createTemplateResponse('form_page.html.twig', [
            'title' => "Forgotten Password",
            'form' => $form->createView()
        ]);
    }
}
