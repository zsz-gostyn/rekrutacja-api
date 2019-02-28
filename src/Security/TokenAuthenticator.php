<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Token;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supports(Request $request)
    {
        return $request->headers->has(Token::HTTP_HEADER_FIELD_NAME);
    }

    public function getCredentials(Request $request)
    {
        return ['token' => $request->headers->get(Token::HTTP_HEADER_FIELD_NAME)];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $tokenValue = $credentials['token'];
        if (!$tokenValue) {
            return;
        }

        $token = $this->entityManager->getRepository(Token::class)->findOneBy(['value' => $tokenValue]);
        if (!$token) {
            return;
        }

        if (!$token->isValid()) {
            $this->entityManager->remove($token);
            $this->entityManager->flush();
            return;
        }

        $token->renew();
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token->getUser();
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse([
            'message' => 'Błąd uwierzytelnienia'
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'message' => 'Wymagane uwierzytelnienie'
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
