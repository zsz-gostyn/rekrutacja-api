<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\TokenGenerator;

class SecurityController extends AbstractController
{
    public function login(Request $request, TokenGenerator $tokenGenerator, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'message' => 'Niepełny format przysłanych danych',
            ], Response::HTTP_BAD_REQUEST);
        }

        $token = $tokenGenerator->generateToken();
        $user->setApiToken($token);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Pomyślnie zalogowano',
            'token' => $user->getApiToken(),
        ], Response::HTTP_OK);
    }

    public function logout(EntityManagerInterface $entityManager)
    {
    }
}
