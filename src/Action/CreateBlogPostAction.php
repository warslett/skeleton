<?php

declare(strict_types=1);

namespace App\Action;

use App\Domain\Entity\BlogPost;
use App\Form\BlogPost\CreateType;
use App\Responder\WebResponder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

final class CreateBlogPostAction implements ActionInterface
{
    private FormFactoryInterface $formFactory;
    private WebResponder $responder;
    private EntityManagerInterface $entityManager;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        WebResponder $responder
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->responder = $responder;
    }

    public function __invoke(Request $request): Response
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getFlashBag();

        $blogPost = new BlogPost();
        $form = $this->formFactory->create(CreateType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->entityManager->persist($blogPost);
                $this->entityManager->flush();

                /** @var string $blogTitle */
                $blogTitle = $blogPost->getTitle();
                $flashBag->add('success', sprintf("Successfully created \"%s\"", $blogTitle));
                return $this->responder->createRedirectResponse('root');
            }
            $flashBag->add('danger', "There were some problems with the information you provided");
        }

        return $this->responder->createTemplateResponse('form_page.html.twig', [
            'title' => "Create Blog Post",
            'form' => $form->createView()
        ]);
    }
}
