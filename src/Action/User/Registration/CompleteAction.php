<?php

declare(strict_types=1);

namespace App\Action\User\Registration;

use App\Action\ActionInterface;
use App\Domain\Repository\RegistrationTokenRepository;
use App\Domain\User\Registration\Exception\DuplicateEmailException;
use App\Domain\User\Registration\Exception\RegistrationTokenExpiredException;
use App\Domain\User\Registration\Factory\UserFactory;
use App\Domain\User\Registration\RegistrationProcessor;
use App\Form\User\Registration\CompleteType;
use App\Responder\WebResponder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig;

final class CompleteAction implements ActionInterface
{
    private RegistrationTokenRepository $tokenRepository;
    private UserFactory $userFactory;
    private FormFactoryInterface $formFactory;
    private RegistrationProcessor $registrationProcessor;
    private WebResponder $responder;

    public function __construct(
        RegistrationTokenRepository $tokenRepository,
        UserFactory $userFactory,
        FormFactoryInterface $formFactory,
        RegistrationProcessor $registrationProcessor,
        WebResponder $responder
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->userFactory = $userFactory;
        $this->formFactory = $formFactory;
        $this->registrationProcessor = $registrationProcessor;
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Twig\Error\Error
     * @throws NotFoundHttpException
     */
    public function __invoke(Request $request): Response
    {
        /** @var string $tokenString */
        $tokenString = $request->get('token');
        $registrationToken = $this->tokenRepository->findOneByToken($tokenString);
        if (null === $registrationToken) {
            throw new NotFoundHttpException(sprintf(
                "No registration token found for token string %s",
                $tokenString
            ));
        }

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        try {
            $user = $this->userFactory->createFromToken($registrationToken);
        } catch (DuplicateEmailException $e) {
            $flashBag->add('danger', $e->getMessage());
            return $this->responder->createRedirectResponse('user_login');
        } catch (RegistrationTokenExpiredException $e) {
            $flashBag->add('danger', $e->getMessage());
            return $this->responder->createRedirectResponse('user_registration_initiate');
        }

        $form = $this->formFactory->create(CompleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->registrationProcessor->processRegistration($user, $registrationToken);

                /** @var string $email */
                $email = $user->getEmail();
                $flashBag->add('success', sprintf("Successfully registered %s", $email));
                return $this->responder->createRedirectResponse('root');
            }
            $flashBag->add('danger', "There were some problems with the information you provided");
        }

        return $this->responder->createTemplateResponse('form_page.html.twig', [
            'title' => "Complete Registration",
            'form' => $form->createView()
        ]);
    }
}
