<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Subscriber;
use App\Form\SubscriberType;
use App\Security\TokenGenerator;

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
        
        $tokenGenerator = new TokenGenerator();
        $subscriber->setUnsubscribeToken($tokenGenerator->generateToken(32));
        $subscriber->setConfirmToken($tokenGenerator->generateToken(32));

        $form = $this->createForm(SubscriberType::class, $subscriber);
        $form->submit(json_decode($request->getContent(), true));
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subscriber);
            $entityManager->flush();

            //TODO: Send Email

            return $this->json([
                'data' => $subscriber
            ], Response::HTTP_CREATED); 
        }

        return $this->json([
            'message' => 'Błąd walidacji' 
        ], Response::HTTP_BAD_REQUEST);
    }

    public function deleteUponId(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Subscriber::class)->find($id);

        if (!$user) {
            return $this->json([
                'message' => 'Subskrybent o podanym id nie istnieje'
            ], Response::HTTP_NOT_FOUND);
        }
        
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Subskrybent usunięty',
        ], Response::HTTP_OK);
    }

    public function deleteUponToken(Request $request, $token)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(Subscriber::class)->findOneBy(['unsubscribe_token' => $token]);

        if (!$user) {
            return $this->json([
                'message' => 'Subskrybent o podanym tokenie nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }
        
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Subskrybent usunięty',
        ], Response::HTTP_OK);
    }

    public function confirm(Request $request, $token)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $subscriber = $entityManager->getRepository(Subscriber::class)->findOneBy(['confirm_token' => $token]);

        if (!$subscriber) {
            return $this->json([
                'message' => 'Subskrybent o podanym tokenie nie istnieje',
            ], Response::HTTP_NOT_FOUND);
        }
        
        $subscriber->setConfirmed(true);
        $entityManager->persist($subscriber);
        $entityManager->flush();

        return $this->json([
            'message' => 'Aktywowano subskrypcję',
        ], Response::HTTP_OK);
    }
}
