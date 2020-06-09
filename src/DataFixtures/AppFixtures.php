<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;


class AppFixtures extends Fixture
{
    private $encoder;


    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $this->loadUser($manager);
    }


    public function loadUser($manager)
    {
        $user1 = new User();
        $user1->setEmail('lizeth@extrem.com');
        $user1->setUsername('lrodriguez');
        $password = $this->encoder->encodePassword($user1, 'admin2020');
        $user1->setPassword($password);

        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('luis@gmail.com');
        $user2->setUsername('ldiaz');
        $password2 = $this->encoder->encodePassword($user2, 'client2020');
        $user2->setPassword($password2);

        $manager->persist($user2);


        $manager->flush();
    }
}
