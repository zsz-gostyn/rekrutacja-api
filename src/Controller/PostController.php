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
    public function showAll(Request $request)
    {
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 2);
        
        $repository = $this->getDoctrine()->getRepository(Post::class);

        $criteria = $isAdmin ? [] : ['active' => true];
        $posts = $repository->findBy($criteria, [], $limit, $offset);
        $totalPostsAmount = $repository->countBy($criteria);

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
};
