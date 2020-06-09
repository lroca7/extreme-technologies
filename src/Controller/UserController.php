<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\ORM\EntityManagerInterface;


use App\Entity\User;

class UserController extends AbstractController
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
    * @Route("/api/users", name="api_create_user", methods="POST")
    */
    public function new(EntityManagerInterface $em, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
            
        if (isset($data['password'])) {
            $password = $this->encoder->encodePassword($user, $data['password']);
            $user->setPassword($password);
        }

        $em->persist($user);
        $em->flush();

        $user = array(
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getUsername()
        );

        return new JsonResponse($user);
    }

    /**
    * @Route("/api/users/{id}", name="api_update_user", methods="PUT")
    */
    public function update(EntityManagerInterface $em, Request $request, $id)
    {
        $data = json_decode($request->getContent(), true);

        $repository = $em->getRepository(User::class);;
        
        $user = $repository->find($id);

        if($user){

            if (isset($data['username'])) {
                $user->setUsername($data['username']);
            }
    
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
                
            if (isset($data['password'])) {
                
                $password_data = trim($data['password']);

                if(strlen($password_data) > 0){
                    $password = $this->encoder->encodePassword($user, $data['password']);
                    $user->setPassword($password);
                }
               
            }
    
            $em->persist($user);
            $em->flush();
    
            $user = array(
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getUsername()
            );
    
            return new JsonResponse($user);
        
        }else{
            return $this->json([
                'error' => 'User not found'
            ], 400);
        }
        
    }
}
