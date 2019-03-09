<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Post;
use App\Entity\Subscriber;
use App\Form\PostType;

class PostController extends AbstractController
{
    public function showAll(Request $request)
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        $criteria = $isAdmin ? [] : ['active' => true];
        
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $totalPostsAmount = $repository->countBy($criteria);
        
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', $totalPostsAmount);
        
        $posts = $repository->findBy($criteria, [], $limit, $offset);

        return $this->json([
            'data' => $posts,
            'count' => $totalPostsAmount,
        ], Response::HTTP_OK);
    }

    public function showOne($id)
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);
        
        if (!$post) {
            return $this->json([
                'message' => 'Post o podanym id nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }

        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$post->getActive() && !$isAdmin) {
            return $this->json([
                'message' => 'Tylko administrator ma dostęp do tego zasobu',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'data' => $post,
        ], Response::HTTP_OK);
    }

    public function add(Request $request)
    {
        $post = new Post();
        $post->setCreationDate(new \DateTime());

        $form = $this->createForm(PostType::class, $post);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush($post);

            return $this->json([
                'data' => $post,
            ], Response::HTTP_CREATED);
        } else {
            return $this->json([
                'message' => 'Błąd walidacji'
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    
    public function update(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);
        
        if (!$post) {
            return $this->json([
                'message' => 'Post o podanym id nie istnieje'
            ], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PostType::class, $post);
        $form = $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->json([
                'data' => $post,
                'message' => 'Pomyślnie zaktualizowano',
            ], Response::HTTP_OK);
        }

        return $this->json([
            'message' => 'Błąd walidacji',
        ], Response::HTTP_BAD_REQUEST);
    }

    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json([
                'message' => 'Post o podanym id nie istnieje',
            ], Result::HTTP_NOT_FOUND);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post usunięty',
        ], Result::HTTP_OK);
    }

    public function sendNotifications($id, \Swift_Mailer $mailer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json([
                'message' => 'Post o podanym id nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }

        $subscribers = $entityManager->getRepository(Subscriber::class)->findBy(['confirmed' => true]); // Send emails only to confirmed subscribers

        $message = (new \Swift_Message($post->getTopic()))
            ->setFrom($this->getParameter('sender_email'))
            ->setBody(
                $this->renderView(
                    'email/notification.html.twig',
                    [
                        'topic' => $post->getTopic(),
                        'content' => $post->getContent(),
                        'creation_date' => $post->getCreationDate(),
                    ]
                ),
                'text/html'
            );

        foreach ($subscribers as $subscriber) {
            $message->setTo($subscriber->getEmail());

            $mailer->send($message);
        }

        return $this->json([
            'message' => 'Powiadomienie dodane do wysyłki',
            'adresaci' => $subscribers,
        ], Response::HTTP_OK);
    }
};
