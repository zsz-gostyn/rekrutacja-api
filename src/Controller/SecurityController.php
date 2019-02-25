<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\TokenGenerator;
use App\Entity\Token;

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

        $token = new Token();
        $token->setValue($tokenGenerator->generateToken());
        $token->renew();
        $entityManager->persist($token);

        $user->addToken($token);
        $entityManager->persist($user);

        $entityManager->flush();

        return $this->json([
            'message' => 'Pomyślnie zalogowano',
            'token' => $token->getValue(),
        ], Response::HTTP_OK);
    }

    public function logout(EntityManagerInterface $entityManager)
    {
        // Logout handler is located in src/Security/LogoutHandler.php
    }
}
