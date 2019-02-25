<?php

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;


class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $userCredentials = ['admin', 'admin2', 'dyrektor']; // Username and passwords are the same
        
        foreach ($userCredentials as $element) {
            $user = new User();

            $user->setUsername($element);
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $element);
            $user->setPassword($encodedPassword);
            $user->setRoles(['ROLE_ADMIN']);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
