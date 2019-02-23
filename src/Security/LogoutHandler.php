<?php
namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LogoutHandler implements LogoutHandlerInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function logout(Request $request, Response $response, TokenInterface $tokenInterface)
    {
        $user = $tokenInterface->getUser();
        $user->setApiToken(null);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
