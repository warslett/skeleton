<?php

declare(strict_types=1);

namespace App\Action\User\Registration;

use App\Action\ActionInterface;
use App\Form\User\Registration\InitiateType;
use App\Responder\WebResponder;
use App\Domain\User\Registration\RegistrationInitiator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Twig;

final class InitiateAction implements ActionInterface
{
    private FormFactoryInterface $formFactory;
    private WebResponder $responder;
    private RegistrationInitiator $registrationInitiator;

    public function __construct(
        FormFactoryInterface $formFactory,
        RegistrationInitiator $registrationInitiator,
        WebResponder $responder
    ) {
        $this->formFactory = $formFactory;
        $this->registrationInitiator = $registrationInitiator;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Twig\Error\Error
     */
    public function __invoke(Request $request): Response
    {
        $form = $this->formFactory->create(InitiateType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @var FlashBagInterface $flashBag */
            $flashBag = $request->getSession()->getFlashBag();

            if ($form->isValid()) {
                /** @var string $email */
                $email = $form->get('email')->getData();
                $this->registrationInitiator->initiateRegistration($email);

                $flashBag->add('success', "Please follow the link in your activation email to complete registration");
                return $this->responder->createRedirectResponse('root');
            }
            $flashBag->add('danger', "There were some problems with the information you provided");
        }

        return $this->responder->createTemplateResponse('form_page.html.twig', [
            'title' => "Register",
            'form' => $form->createView()
        ]);
    }
}
