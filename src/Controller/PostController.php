<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Post;
use App\Form\PostType;

class PostController extends AbstractController
{
    public function showAll()
    {
        $posts = $this->getDoctrine()->getRepository(Post::class)->findAll();
        
        return $this->json([
            'data' => $posts,
            'count' => count($posts)
        ]);
    }

    public function showOne($id)
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);
        
        if (!$post) {
            return $this->json(['message' => 'Post o podanym id nie istnieje'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $post
        ]);
    }

    public function add(Request $request)
    {
        $post = new Post();
        $post->setCreationDate(new \DateTime());

        $form = $this->createForm(PostType::class, $post);
        $form->submit(json_decode($request->getContent(), true));

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush($post);

            return $this->json([
                'data' => $post,
            ], Response::HTTP_CREATED);
        } else {
            return $this->json([
                'message' => 'Blad walidacji'
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
                'message' => 'Pomyslnie zaktualizowano'
            ]);
        }

        return $this->json([
            'message' => 'Blad walidacji'
        ], Response::HTTP_BAD_REQUEST);
    }

    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            return $this->json([
                'message' => 'Post o podanym id nie istnieje'
            ]);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->json([
            'message' => 'Post usuniety'
        ]);
    }
};
