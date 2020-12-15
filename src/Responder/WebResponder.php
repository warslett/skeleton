<?php

declare(strict_types=1);

namespace App\Responder;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig;

class WebResponder
{
    private Twig\Environment $twig;
    private RouterInterface $router;

    public function __construct(Twig\Environment $twig, RouterInterface $router)
    {
        $this->twig = $twig;
        $this->router = $router;
    }

    /**
     * @param string $template
     * @param array $context
     * @return Response
     * @throws Twig\Error\Error
     */
    public function createTemplateResponse(string $template, array $context = []): Response
    {
        $response = new Response();
        $response->setContent($this->twig->render($template, $context));
        return $response;
    }

    /**
     * @param string $routeName
     * @param array $routeParameters
     * @return RedirectResponse
     */
    public function createRedirectResponse(string $routeName, array $routeParameters = []): RedirectResponse
    {
        return new RedirectResponse($this->router->generate($routeName, $routeParameters));
    }
}
