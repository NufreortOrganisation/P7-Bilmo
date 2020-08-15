<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $fake = Factory::create("fr_FR");

        for($u = 0; $u < 10; $u++) {
            $user = new Users();

            $passHash = $this->encoder->encodePassword($user, 'password');

            $user->setEmail($fake->email)
                ->setPassword($passHash);

            $manager->persist($user);

    }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
