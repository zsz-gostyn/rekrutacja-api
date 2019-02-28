<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Subscriber;
use App\Form\SubscriberType;

class SubscriberController extends AbstractController 
{
    public function showAll(Request $request)
    {
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 2);

        $repository = $this->getDoctrine()->getRepository(Subscriber::class);
        $subscribers = $repository->findBy([], [], $limit, $offset);
        $totalSubscribersAmount = $repository->countBy([]);

        return $this->json([
            'data' => $subscribers,
            'count' => $totalSubscribersAmount,
        ], Response::HTTP_OK);
    }

    public function showOne($id)
    {
        $subscriber = $this->getDoctrine()->getRepository(Subscriber::class)->find($id);

        if (!$subscriber) {
            return $this->json([
                'message' => 'Subskrybent o podanym id nie istnieje'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'data' => $subscriber
        ], Response::HTTP_OK);
    }

    public function add(Request $request)
    {
        $subscriber = new Subscriber();
        $subscriber->setCreationDate(new \DateTime());

        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->submit(json_decode($request->getContent(), true));
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subscriber);
            $entityManager->flush();

            return $this->json([
                'data' => $subscriber
            ], Response::HTTP_CREATED); 
        }

        return $this->json([
            'message' => 'Błąd walidacji' 
        ], Response::HTTP_BAD_REQUEST);
    }
}
