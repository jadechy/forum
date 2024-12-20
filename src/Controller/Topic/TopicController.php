<?php

namespace App\Controller\Topic;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\TopicRepository;
use App\Entity\Topic;
use App\Entity\Response;
use App\Form\ResponseType;

class TopicController extends AbstractController
{
    #[Route('/topic/{id}', name: 'app_topic')]
    public function topic(string $id, Request $request, TopicRepository $topicRepository, Topic $topic, EntityManagerInterface $entityManager): HttpResponse
    {
        $response = new Response();
        $form = $this->createForm(ResponseType::class, $response);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour répondre.');
            }
            $response->setAuthor($user);

            $response->setTopic($topic);

            $response->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($response);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic', ['id' => $id], HttpResponse::HTTP_SEE_OTHER);
        }

        return $this->render('topic/index.html.twig', [
            'topic' => $topic,
            'form' => $form
        ]);
    }
}
