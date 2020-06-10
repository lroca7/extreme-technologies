<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Entity\ComplaintType;

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
        $this->loadComplaintType($manager);
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

    public function loadComplaintType($manager)
    {
        $complaintType1 = new ComplaintType();
        $complaintType1->setName('Petición');

        $manager->persist($complaintType1);

        $complaintType2 = new ComplaintType();
        $complaintType2->setName('Reclamo');

        $manager->persist($complaintType2);

        $complaintType3 = new ComplaintType();
        $complaintType3->setName('Cambio');

        $manager->persist($complaintType3);


        $manager->flush();
    }
}
