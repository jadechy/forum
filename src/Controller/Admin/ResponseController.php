<?php

namespace App\Controller\Admin;

use App\Entity\Response;
use App\Form\Response1Type;
use App\Repository\ResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/response')]
final class ResponseController extends AbstractController
{

    #[Route('/{id}', name: 'app_response_show', methods: ['GET'])]
    public function show(Response $response): HttpResponse
    {
        return $this->render('response/show.html.twig', [
            'response' => $response,
        ]);
    }

    #[Route('/{id}', name: 'app_response_delete', methods: ['POST'])]
    public function delete(Request $request, Response $response, EntityManagerInterface $entityManager): HttpResponse
    {
        if ($this->isCsrfTokenValid('delete'.$response->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($response);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_response', [], HttpResponse::HTTP_SEE_OTHER);
    }
}
