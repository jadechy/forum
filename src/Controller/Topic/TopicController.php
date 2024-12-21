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
use App\Enum\ResponseStatusEnum;
use App\Enum\TopicStatusEnum;
use App\Form\TopicType;

class TopicController extends AbstractController
{
    #[Route('/topic/{id}', name: 'app_topic')]
    public function topic(string $id, Request $request, Topic $topic, EntityManagerInterface $entityManager): HttpResponse
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

            $response->setStatus(ResponseStatusEnum::WAITING);

            $entityManager->persist($response);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic', ['id' => $id], HttpResponse::HTTP_SEE_OTHER);
        }

        return $this->render('topic/index.html.twig', [
            'topic' => $topic,
            'form' => $form
        ]);
    }

    #[Route('/new/topic', name: 'app_topic_new',methods: ['GET', 'POST'])]
    public function newTopic(Request $request, EntityManagerInterface $entityManager): HttpResponse
    {
        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un topic.');
            }
            $topic->setAuthor($user);

            $topic->setStatus(TopicStatusEnum::WAITING);

            $topic->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($topic);
            $entityManager->flush();

            return $this->redirectToRoute('app_topic_user', [], HttpResponse::HTTP_SEE_OTHER);
        }

        return $this->render('topic/new.html.twig', [
            'topic' => $topic,
            'form' => $form,
        ]);
    }

    #[Route('/edit/topic/{id}', name: 'app_topic_edit',methods: ['GET', 'POST'])]
    public function editTopic(Request $request, Topic $topic, EntityManagerInterface $entityManager): HttpResponse
    {
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_topic_user', [], HttpResponse::HTTP_SEE_OTHER);
        }

        return $this->render('topic/edit.html.twig', [
            'topic' => $topic,
            'form' => $form,
        ]);
    }

    #[Route('/delete/topic/{id}', name: 'app_topic_delete', methods: ['POST'])]
    public function delete(Request $request, Topic $topic, EntityManagerInterface $entityManager): HttpResponse
    {
        if ($this->isCsrfTokenValid('delete'.$topic->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($topic);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_topic_user', [], HttpResponse::HTTP_SEE_OTHER);
    }

    #[Route('/topics', name: 'app_topic_user')]
    public function userTopic(TopicRepository $topicRepository): HttpResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('No user is logged in.');
        }

        $topics = $topicRepository->findBy(['author' => $user]);

        return $this->render('topic/author.html.twig', [
            'topics' => $topics,
        ]);
    }

    #[Route('/topic/{id}/status', name: 'topic_change_status', methods: ['POST'])]
    public function changeStatus(Topic $topic, Request $request, EntityManagerInterface $entityManager): HttpResponse
    {
        $status = $request->request->get('status');

        if (!in_array($status, array_map(fn($enum) => $enum->value, TopicStatusEnum::cases()), true)) {
            throw $this->createNotFoundException('Statut non valide');
        }       

        $topic->setStatus(TopicStatusEnum::from($status));

        $entityManager->flush();

        return $this->redirectToRoute('admin_topic', [], HttpResponse::HTTP_SEE_OTHER);

    }


}
