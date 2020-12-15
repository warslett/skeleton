<?php

declare(strict_types=1);

namespace App\Action\User;

use App\Action\ActionInterface;
use App\Responder\WebResponder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DashboardAction implements ActionInterface
{
    private WebResponder $responder;

    public function __construct(WebResponder $responder)
    {
        $this->responder = $responder;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Twig\Error\Error
     */
    public function __invoke(Request $request): Response
    {
        return $this->responder->createTemplateResponse('user/dashboard.html.twig');
    }
}
