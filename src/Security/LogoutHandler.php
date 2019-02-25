<?php
namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Token;


class LogoutHandler implements LogoutHandlerInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $tokenInterface)
    {
        $tokenValue = $request->headers->get(Token::HTTP_HEADER_FIELD_NAME);
        $token = $this->entityManager->getRepository(Token::class)->findOneBy(['value' => $tokenValue]);

        $user = $tokenInterface->getUser();
        $user->removeToken($token);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
